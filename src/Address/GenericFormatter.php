<?php
/**
 * Generic Address Formatter
 *
 * Fallback formatter for non-Canada-Post addresses.
 * Provides basic formatting with country name on last line
 * for international addresses.
 *
 * @package KsFraser\FaShippingLabel\Address
 * @see FR-011
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Address;

/**
 * Generic address formatter for international / unspecified carriers.
 *
 * Applies basic formatting rules:
 * - Uppercase output
 * - Country name as last line for international
 * - Standard line ordering
 */
class GenericFormatter implements AddressFormatterInterface
{
    /** @var float Minimum character height in mm */
    private const MIN_CHAR_HEIGHT_MM = 2.0;

    /** @var float Maximum character height in mm */
    private const MAX_CHAR_HEIGHT_MM = 5.0;

    /** @var int Maximum characters per line */
    private const MAX_LINE_LENGTH = 40;

    /** @var float Minimum line spacing in mm */
    private const MIN_LINE_SPACING_MM = 0.5;

    /**
     * {@inheritDoc}
     */
    public function formatForLabel(Address $address): array
    {
        $lines = [];

        $lines[] = strtoupper(trim($address->getName()));

        if ($address->getAddressLine2() !== null && $address->getAddressLine2() !== '') {
            $lines[] = strtoupper(trim($address->getAddressLine2()));
        }

        $lines[] = strtoupper(trim($address->getAddressLine1()));

        // City, Province/State  PostalCode
        $lastLine = strtoupper(trim($address->getCity()));
        $province = strtoupper(trim($address->getProvince()));
        $postal = strtoupper(trim($address->getPostalCode()));

        if ($province !== '') {
            $lastLine .= ' ' . $province;
        }
        if ($postal !== '') {
            $lastLine .= '  ' . $postal;
        }
        $lines[] = $lastLine;

        // International: country on final line
        $country = strtoupper(trim($address->getCountry()));
        if ($country !== '' && $country !== 'CANADA') {
            $lines[] = $country;
        }

        return $lines;
    }

    /**
     * {@inheritDoc}
     */
    public function getMinCharHeightMm(): float
    {
        return self::MIN_CHAR_HEIGHT_MM;
    }

    /**
     * {@inheritDoc}
     */
    public function getMaxCharHeightMm(): float
    {
        return self::MAX_CHAR_HEIGHT_MM;
    }

    /**
     * {@inheritDoc}
     */
    public function getMaxLineLength(): int
    {
        return self::MAX_LINE_LENGTH;
    }

    /**
     * {@inheritDoc}
     */
    public function getMinLineSpacingMm(): float
    {
        return self::MIN_LINE_SPACING_MM;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'Generic';
    }
}
