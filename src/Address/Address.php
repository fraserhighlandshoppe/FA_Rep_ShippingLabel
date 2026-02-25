<?php
/**
 * Address Value Object
 *
 * Immutable value object representing a mailing address.
 * Supports Canadian, US, and international addresses.
 *
 * @package KsFraser\FaShippingLabel\Address
 * @see FR-001, FR-002, FR-003
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Address;

use InvalidArgumentException;

/**
 * Immutable address value object for label generation.
 *
 * Represents a complete mailing address with validation
 * for required fields and postal code format.
 */
class Address
{
    /** @var string Recipient or company name */
    private $name;

    /** @var string Primary street address */
    private $addressLine1;

    /** @var string|null Secondary address (unit, suite, floor) */
    private $addressLine2;

    /** @var string City or municipality */
    private $city;

    /** @var string Province, state, or territory */
    private $province;

    /** @var string Postal or ZIP code */
    private $postalCode;

    /** @var string Country name (empty for domestic) */
    private $country;

    /**
     * Canadian postal code pattern: A1A 1A1
     * Allows optional space between halves.
     */
    private const CANADIAN_POSTAL_PATTERN = '/^[A-Za-z]\d[A-Za-z]\s?\d[A-Za-z]\d$/';

    /**
     * Construct an Address value object.
     *
     * @param string      $name         Recipient or company name
     * @param string      $addressLine1 Primary street address
     * @param string|null $addressLine2 Secondary address line (optional)
     * @param string      $city         City or municipality
     * @param string      $province     Province, state, or territory
     * @param string      $postalCode   Postal or ZIP code
     * @param string      $country      Country name (empty string for domestic)
     *
     * @throws InvalidArgumentException If required fields are missing
     */
    public function __construct(
        string $name,
        string $addressLine1,
        ?string $addressLine2,
        string $city,
        string $province,
        string $postalCode,
        string $country = ''
    ) {
        $this->validateRequired($name, 'name');
        $this->validateRequired($addressLine1, 'addressLine1');
        $this->validateRequired($city, 'city');
        $this->validateRequired($province, 'province');
        $this->validateRequired($postalCode, 'postalCode');

        $this->name = trim($name);
        $this->addressLine1 = trim($addressLine1);
        $this->addressLine2 = $addressLine2 !== null ? trim($addressLine2) : null;
        $this->city = trim($city);
        $this->province = trim($province);
        $this->postalCode = trim($postalCode);
        $this->country = trim($country);
    }

    /**
     * Get the recipient/company name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the primary street address.
     *
     * @return string
     */
    public function getAddressLine1(): string
    {
        return $this->addressLine1;
    }

    /**
     * Get the secondary address line.
     *
     * @return string|null
     */
    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    /**
     * Get the city/municipality.
     *
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Get the province/state/territory.
     *
     * @return string
     */
    public function getProvince(): string
    {
        return $this->province;
    }

    /**
     * Get the postal/ZIP code.
     *
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * Get the country name.
     *
     * @return string Empty string indicates domestic address
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Check if this is a domestic (Canadian) address.
     *
     * An address is domestic when country is empty or 'Canada'.
     *
     * @return bool
     */
    public function isDomestic(): bool
    {
        return $this->country === ''
            || strtoupper(trim($this->country)) === 'CANADA';
    }

    /**
     * Check if the postal code matches Canadian format.
     *
     * @return bool
     */
    public function isCanadianPostalCode(): bool
    {
        return (bool) preg_match(self::CANADIAN_POSTAL_PATTERN, $this->postalCode);
    }

    /**
     * Convert the Address to an associative array.
     *
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'name'         => $this->name,
            'addressLine1' => $this->addressLine1,
            'addressLine2' => $this->addressLine2,
            'city'         => $this->city,
            'province'     => $this->province,
            'postalCode'   => $this->postalCode,
            'country'      => $this->country,
        ];
    }

    /**
     * Create an Address from an associative array.
     *
     * @param array<string, string|null> $data Address data
     *
     * @return self
     * @throws InvalidArgumentException If required keys are missing
     */
    public static function fromArray(array $data): self
    {
        $required = ['name', 'addressLine1', 'city', 'province', 'postalCode'];
        foreach ($required as $key) {
            if (!isset($data[$key]) || $data[$key] === '') {
                throw new InvalidArgumentException(
                    sprintf("Required address field '%s' is missing or empty.", $key)
                );
            }
        }

        return new self(
            $data['name'],
            $data['addressLine1'],
            $data['addressLine2'] ?? null,
            $data['city'],
            $data['province'],
            $data['postalCode'],
            $data['country'] ?? ''
        );
    }

    /**
     * Validate that a required field is not empty.
     *
     * @param string $value     The value to validate
     * @param string $fieldName The field name for error messages
     *
     * @throws InvalidArgumentException If the value is empty
     */
    private function validateRequired(string $value, string $fieldName): void
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException(
                sprintf("Address field '%s' is required and cannot be empty.", $fieldName)
            );
        }
    }
}
