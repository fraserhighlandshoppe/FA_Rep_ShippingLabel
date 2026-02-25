<?php
/**
 * Supplier Contact Type
 *
 * Retrieves supplier addresses from FrontAccounting's suppliers table.
 *
 * @package KsFraser\FaShippingLabel\ContactType
 * @see FR-021
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\ContactType;

use KsFraser\FaShippingLabel\Address\Address;
use InvalidArgumentException;

/**
 * Supplier contact type for FA.
 *
 * Queries the suppliers table for supplier address data.
 */
class SupplierContact implements ContactTypeInterface
{
    /**
     * {@inheritDoc}
     */
    public function getTypeName(): string
    {
        return 'Supplier';
    }

    /**
     * {@inheritDoc}
     *
     * Queries FA suppliers table for active suppliers.
     */
    public function listContacts(): array
    {
        $sql = "SELECT supplier_id, supp_name FROM " . TB_PREF . "suppliers "
             . "WHERE inactive = 0 ORDER BY supp_name";
        $result = db_query($sql, "Could not retrieve supplier list");

        $contacts = [];
        while ($row = db_fetch_assoc($result)) {
            $contacts[(int) $row['supplier_id']] = $row['supp_name'];
        }

        return $contacts;
    }

    /**
     * {@inheritDoc}
     *
     * Suppliers do not have branches in FA. Returns empty array.
     */
    public function listBranches(int $contactId): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     *
     * Retrieves supplier address. Branch ID is ignored for suppliers.
     */
    public function getAddress(int $contactId, ?int $branchId = null): Address
    {
        $sql = "SELECT supp_name, address FROM " . TB_PREF . "suppliers "
             . "WHERE supplier_id = " . db_escape($contactId);
        $result = db_query($sql, "Could not retrieve supplier " . $contactId);
        $row = db_fetch_assoc($result);

        if ($row === false) {
            throw new InvalidArgumentException(
                sprintf("Supplier with ID %d not found.", $contactId)
            );
        }

        return CustomerContact::parseAddressString(
            $row['supp_name'],
            $row['address']
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getCompanyReturnAddress(): Address
    {
        return CompanyAddressHelper::getCompanyAddress();
    }
}
