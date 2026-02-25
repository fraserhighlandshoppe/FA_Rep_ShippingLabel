<?php
/**
 * Canada Post Address Formatter
 *
 * Formats addresses according to Canada Post T601 addressing standard.
 * Rules reference: Canada Post Addressing Guidelines.
 *
 * @package KsFraser\FaShippingLabel\Address
 * @see FR-010, FR-011
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Address;

/**
 * Formats addresses per Canada Post T601 standard.
 *
 * Rules applied:
 * - All uppercase
 * - No punctuation (except proper names)
 * - Municipality, province (2-letter), postal code on last line
 * - 1 space between municipality and province
 * - 2 spaces between province and postal code
 * - Character height 2–5mm
 * - Line spacing ≥ 0.5mm
 * - Max 40 chars/line
 * - 3–6 address lines
 * - No '#' symbol
 * - No "CANADA" on domestic
 * - Country name on last line for international
 */
class CanadaPostFormatter implements AddressFormatterInterface
{
    /** @var float Minimum character height in mm per T601 */
    private const MIN_CHAR_HEIGHT_MM = 2.0;

    /** @var float Maximum character height in mm per T601 */
    private const MAX_CHAR_HEIGHT_MM = 5.0;

    /** @var int Maximum characters per line per T601 */
    private const MAX_LINE_LENGTH = 40;

    /** @var float Minimum line spacing in mm per T601 */
    private const MIN_LINE_SPACING_MM = 0.5;

    /**
     * Canadian province/territory two-letter abbreviations.
     *
     * @var array<string, string> Full name (upper) => abbreviation
     */
    private const PROVINCE_ABBREVIATIONS = [
        'ALBERTA'                    => 'AB',
        'BRITISH COLUMBIA'           => 'BC',
        'MANITOBA'                   => 'MB',
        'NEW BRUNSWICK'              => 'NB',
        'NEWFOUNDLAND AND LABRADOR'  => 'NL',
        'NEWFOUNDLAND'               => 'NL',
        'NORTHWEST TERRITORIES'      => 'NT',
        'NOVA SCOTIA'                => 'NS',
        'NUNAVUT'                    => 'NU',
        'ONTARIO'                    => 'ON',
        'PRINCE EDWARD ISLAND'       => 'PE',
        'QUEBEC'                     => 'QC',
        'SASKATCHEWAN'               => 'SK',
        'YUKON'                      => 'YT',
    ];

    /**
     * {@inheritDoc}
     */
    public function formatForLabel(Address $address): array
    {
        $lines = [];

        // Line 1: Name (always present)
        $lines[] = $this->sanitizeLine($address->getName());

        // Line 2: Additional delivery info (addressLine2 — unit, suite, floor)
        if ($address->getAddressLine2() !== null && $address->getAddressLine2() !== '') {
            $lines[] = $this->sanitizeLine($address->getAddressLine2());
        }

        // Line 3: Civic/street address
        $lines[] = $this->sanitizeLine($address->getAddressLine1());

        // Last line: Municipality  Province  Postal Code
        $municipalityLine = $this->formatMunicipalityLine(
            $address->getCity(),
            $address->getProvince(),
            $address->getPostalCode()
        );
        $lines[] = $municipalityLine;

        // International: add country name on final line
        if (!$address->isDomestic()) {
            $country = strtoupper(trim($address->getCountry()));
            if ($country !== '' && $country !== 'CANADA') {
                $lines[] = $country;
            }
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
        return 'Canada Post';
    }

    /**
     * Format the municipality/province/postal code line.
     *
     * Per T601: MUNICIPALITY SP PROVINCE 2SP POSTAL CODE
     *
     * @param string $city       Municipality name
     * @param string $province   Province name or abbreviation
     * @param string $postalCode Postal code
     *
     * @return string Formatted line
     */
    private function formatMunicipalityLine(
        string $city,
        string $province,
        string $postalCode
    ): string {
        $municipality = strtoupper(trim($city));
        $prov = $this->getProvinceAbbreviation($province);
        $postal = $this->formatPostalCode($postalCode);

        // T601: municipality<1sp>province<2sp>postalCode
        return $municipality . ' ' . $prov . '  ' . $postal;
    }

    /**
     * Convert province name to two-letter abbreviation.
     *
     * If already a 2-letter code, returns it uppercased.
     * If a full name is recognized, returns the abbreviation.
     * Otherwise returns the input uppercased.
     *
     * @param string $province Province name or code
     *
     * @return string Two-letter abbreviation
     */
    private function getProvinceAbbreviation(string $province): string
    {
        $upper = strtoupper(trim($province));

        // Already an abbreviation
        if (strlen($upper) === 2) {
            return $upper;
        }

        // Lookup full name
        if (isset(self::PROVINCE_ABBREVIATIONS[$upper])) {
            return self::PROVINCE_ABBREVIATIONS[$upper];
        }

        // Unknown — return as-is uppercased (handles US states, etc.)
        return $upper;
    }

    /**
     * Format postal code to Canada Post standard.
     *
     * Ensures uppercase with single space: A1A 1A1
     *
     * @param string $postalCode Raw postal code
     *
     * @return string Formatted postal code
     */
    private function formatPostalCode(string $postalCode): string
    {
        $clean = strtoupper(preg_replace('/\s+/', '', trim($postalCode)));

        // Insert space in Canadian postal codes (6 chars without space)
        if (strlen($clean) === 6 && preg_match('/^[A-Z]\d[A-Z]\d[A-Z]\d$/', $clean)) {
            return substr($clean, 0, 3) . ' ' . substr($clean, 3);
        }

        return strtoupper(trim($postalCode));
    }

    /**
     * Sanitize a line for Canada Post compliance.
     *
     * - Converts to uppercase
     * - Removes '#' symbol
     * - Removes general punctuation (preserves hyphens for unit-civic separation,
     *   periods in proper names like ST. JOHN'S, and apostrophes)
     *
     * @param string $line Raw address line
     *
     * @return string Sanitized line
     */
    private function sanitizeLine(string $line): string
    {
        $upper = strtoupper(trim($line));

        // Remove '#' and 'NO' prefix used as unit number indicator
        $upper = preg_replace('/\s*#\s*/', ' ', $upper);

        // Remove commas (T601 says no punctuation)
        $upper = str_replace(',', '', $upper);

        // Collapse multiple spaces
        $upper = preg_replace('/\s+/', ' ', trim($upper));

        return $upper;
    }
}
