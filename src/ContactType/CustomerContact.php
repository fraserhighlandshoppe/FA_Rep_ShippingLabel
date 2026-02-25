<?php
/**
 * Customer Contact Type
 *
 * Retrieves customer addresses from FrontAccounting's debtors_master
 * and cust_branch tables.
 *
 * @package KsFraser\FaShippingLabel\ContactType
 * @see FR-020, FR-024, FR-025
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\ContactType;

use KsFraser\FaShippingLabel\Address\Address;
use InvalidArgumentException;

/**
 * Customer contact type for FA.
 *
 * Queries debtors_master for customers and cust_branch for
 * branch-specific shipping addresses.
 */
class CustomerContact implements ContactTypeInterface
{
    /**
     * {@inheritDoc}
     */
    public function getTypeName(): string
    {
        return 'Customer';
    }

    /**
     * {@inheritDoc}
     *
     * Queries FA debtors_master table for active customers.
     */
    public function listContacts(): array
    {
        $sql = "SELECT debtor_no, name FROM " . TB_PREF . "debtors_master "
             . "WHERE inactive = 0 ORDER BY name";
        $result = db_query($sql, "Could not retrieve customer list");

        $contacts = [];
        while ($row = db_fetch_assoc($result)) {
            $contacts[(int) $row['debtor_no']] = $row['name'];
        }

        return $contacts;
    }

    /**
     * {@inheritDoc}
     *
     * Queries FA cust_branch table for customer branches.
     */
    public function listBranches(int $contactId): array
    {
        $sql = "SELECT branch_code, br_name FROM " . TB_PREF . "cust_branch "
             . "WHERE debtor_no = " . db_escape($contactId) . " "
             . "AND inactive = 0 ORDER BY br_name";
        $result = db_query($sql, "Could not retrieve branches for customer " . $contactId);

        $branches = [];
        while ($row = db_fetch_assoc($result)) {
            $branches[(int) $row['branch_code']] = $row['br_name'];
        }

        return $branches;
    }

    /**
     * {@inheritDoc}
     *
     * Retrieves address from cust_branch (shipping) if branchId given,
     * otherwise from debtors_master (billing).
     */
    public function getAddress(int $contactId, ?int $branchId = null): Address
    {
        if ($branchId !== null) {
            return $this->getBranchAddress($contactId, $branchId);
        }

        return $this->getCustomerBillingAddress($contactId);
    }

    /**
     * {@inheritDoc}
     *
     * Returns company address from FA sys_prefs.
     */
    public function getCompanyReturnAddress(): Address
    {
        return CompanyAddressHelper::getCompanyAddress();
    }

    /**
     * Fetch billing address from debtors_master.
     *
     * @param int $contactId Customer ID
     *
     * @return Address
     * @throws InvalidArgumentException If customer not found
     */
    private function getCustomerBillingAddress(int $contactId): Address
    {
        $sql = "SELECT name, address FROM " . TB_PREF . "debtors_master "
             . "WHERE debtor_no = " . db_escape($contactId);
        $result = db_query($sql, "Could not retrieve customer " . $contactId);
        $row = db_fetch_assoc($result);

        if ($row === false) {
            throw new InvalidArgumentException(
                sprintf("Customer with ID %d not found.", $contactId)
            );
        }

        return self::parseAddressString($row['name'], $row['address']);
    }

    /**
     * Fetch shipping address from cust_branch.
     *
     * @param int $contactId Customer ID
     * @param int $branchId  Branch code
     *
     * @return Address
     * @throws InvalidArgumentException If branch not found
     */
    private function getBranchAddress(int $contactId, int $branchId): Address
    {
        $sql = "SELECT br_name, br_address FROM " . TB_PREF . "cust_branch "
             . "WHERE debtor_no = " . db_escape($contactId)
             . " AND branch_code = " . db_escape($branchId);
        $result = db_query($sql, "Could not retrieve branch " . $branchId);
        $row = db_fetch_assoc($result);

        if ($row === false) {
            throw new InvalidArgumentException(
                sprintf(
                    "Branch %d for customer %d not found.",
                    $branchId,
                    $contactId
                )
            );
        }

        return self::parseAddressString($row['br_name'], $row['br_address']);
    }

    /**
     * Parse FA's multi-line address string into an Address object.
     *
     * FA stores addresses as newline-delimited strings. This method
     * parses the standard format into structured fields.
     *
     * @param string $name    Contact name
     * @param string $address Multi-line address string
     *
     * @return Address
     */
    public static function parseAddressString(string $name, string $address): Address
    {
        $lines = array_filter(
            array_map('trim', explode("\n", str_replace("\r", "", $address))),
            function ($line) {
                return $line !== '';
            }
        );
        $lines = array_values($lines);
        $count = count($lines);

        // Attempt to parse:
        // Line 0: street address
        // Line 1: optional address line 2 (if > 3 lines)
        // Line N-1: City Province PostalCode (or City, Province PostalCode)
        // Last line could also be country for international

        $addressLine1 = $lines[0] ?? '';
        $addressLine2 = null;
        $city = '';
        $province = '';
        $postalCode = '';
        $country = '';

        if ($count >= 3) {
            // Check if last line looks like a country (no digits, not a city/prov/postal line)
            $lastLine = $lines[$count - 1];
            $cityProvLine = $lines[$count - 1];

            if ($count >= 4 && !preg_match('/\d/', $lastLine)) {
                $country = $lastLine;
                $cityProvLine = $lines[$count - 2];
                $addressLine2 = ($count >= 5) ? $lines[1] : null;
            } else {
                $addressLine2 = ($count >= 4) ? $lines[1] : null;
            }

            // Parse city/province/postal from the cityProvLine
            $parsed = self::parseCityProvPostal($cityProvLine);
            $city = $parsed['city'];
            $province = $parsed['province'];
            $postalCode = $parsed['postalCode'];
        } elseif ($count === 2) {
            $parsed = self::parseCityProvPostal($lines[1]);
            $city = $parsed['city'];
            $province = $parsed['province'];
            $postalCode = $parsed['postalCode'];
        } elseif ($count === 1) {
            $city = 'UNKNOWN';
            $province = 'UNKNOWN';
            $postalCode = '00000';
        }

        return new Address(
            $name,
            $addressLine1,
            $addressLine2,
            $city !== '' ? $city : 'UNKNOWN',
            $province !== '' ? $province : 'UNKNOWN',
            $postalCode !== '' ? $postalCode : '00000',
            $country
        );
    }

    /**
     * Parse a "City Province PostalCode" line.
     *
     * Handles formats:
     * - "OTTAWA ON  K1A 0A6"
     * - "Ottawa, ON K1A 0A6"
     * - "Ottawa, Ontario K1A 0A6"
     *
     * @param string $line The city/province/postal line
     *
     * @return array{city: string, province: string, postalCode: string}
     */
    private static function parseCityProvPostal(string $line): array
    {
        $city = '';
        $province = '';
        $postalCode = '';

        // Try Canadian postal code pattern first
        if (preg_match('/([A-Za-z]\d[A-Za-z]\s?\d[A-Za-z]\d)\s*$/', $line, $matches)) {
            $postalCode = $matches[1];
            $remainder = trim(substr($line, 0, -strlen($matches[0])));
        } elseif (preg_match('/(\d{5}(-\d{4})?)\s*$/', $line, $matches)) {
            // US ZIP code
            $postalCode = $matches[1];
            $remainder = trim(substr($line, 0, -strlen($matches[0])));
        } else {
            $remainder = $line;
        }

        // Remove trailing comma
        $remainder = rtrim($remainder, ', ');

        // Split remainder into city and province
        // Try comma separation first: "City, Province"
        if (strpos($remainder, ',') !== false) {
            $parts = explode(',', $remainder, 2);
            $city = trim($parts[0]);
            $province = trim($parts[1]);
        } else {
            // Space separation: last token is province
            $tokens = preg_split('/\s+/', $remainder);
            if (count($tokens) >= 2) {
                $province = array_pop($tokens);
                $city = implode(' ', $tokens);
            } else {
                $city = $remainder;
            }
        }

        return [
            'city'       => $city,
            'province'   => $province,
            'postalCode' => $postalCode,
        ];
    }
}
