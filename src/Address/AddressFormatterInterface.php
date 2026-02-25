<?php
/**
 * Address Formatter Interface
 *
 * Strategy pattern contract for carrier/nation-specific address formatting.
 * Implement this interface to add new formatting standards (e.g., UPS, DHL,
 * Royal Mail, Deutsche Post).
 *
 * @package KsFraser\FaShippingLabel\Address
 * @see FR-012
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Address;

/**
 * Contract for address formatting strategies.
 *
 * Each implementation formats addresses according to specific carrier
 * or national postal standards.
 */
interface AddressFormatterInterface
{
    /**
     * Format an Address for printing on a label.
     *
     * Returns an array of formatted text lines ready for rendering.
     * Each line has already had carrier-specific rules applied
     * (uppercase, punctuation, spacing, etc.).
     *
     * @param Address $address The address to format
     *
     * @return string[] Formatted lines, top to bottom
     */
    public function formatForLabel(Address $address): array;

    /**
     * Get the minimum character height in millimeters.
     *
     * @return float Minimum height in mm
     */
    public function getMinCharHeightMm(): float;

    /**
     * Get the maximum character height in millimeters.
     *
     * @return float Maximum height in mm
     */
    public function getMaxCharHeightMm(): float;

    /**
     * Get the maximum number of characters per line.
     *
     * @return int Maximum characters (excluding spaces for some standards)
     */
    public function getMaxLineLength(): int;

    /**
     * Get the minimum line spacing in millimeters.
     *
     * @return float Minimum spacing between lines in mm
     */
    public function getMinLineSpacingMm(): float;

    /**
     * Get the name of this formatter (e.g., "Canada Post", "UPS").
     *
     * @return string Human-readable formatter name
     */
    public function getName(): string;
}
