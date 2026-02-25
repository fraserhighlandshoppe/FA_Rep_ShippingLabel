<?php
/**
 * Output Plugin Interface
 *
 * Contract for output format plugins. Each plugin handles a different
 * output target (envelope, Avery sheet, thermal label, etc.).
 *
 * @package KsFraser\FaShippingLabel\Plugin
 * @see FR-040
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Plugin;

use KsFraser\FaShippingLabel\Label\LabelType;

/**
 * Contract for output format plugins.
 *
 * Plugins provide:
 * - A list of supported output formats (e.g., "Avery 5160", "#10 Envelope")
 * - Rendering logic to generate PDF content for a given format
 * - Declaration of which label types they support
 */
interface OutputPluginInterface
{
    /**
     * Get the human-readable plugin name.
     *
     * @return string e.g., "Envelope/Sheet", "Avery Label Sheets", "Thermal Printer"
     */
    public function getName(): string;

    /**
     * Get the unique plugin identifier.
     *
     * @return string e.g., "envelope", "avery", "thermal"
     */
    public function getId(): string;

    /**
     * Get available output formats.
     *
     * @return array<string, string> Map of format ID => display name
     */
    public function getOutputFormats(): array;

    /**
     * Check if this plugin supports a given label type.
     *
     * @param LabelType $type The label type to check
     *
     * @return bool
     */
    public function supportsLabelType(LabelType $type): bool;

    /**
     * Render labels as PDF content.
     *
     * @param LabelType $type          The label type (company/contact/paired)
     * @param string[]  $returnLines   Formatted return address lines (may be empty)
     * @param string[]  $destLines     Formatted destination address lines (may be empty)
     * @param string    $formatId      The output format ID from getOutputFormats()
     *
     * @return string PDF content as binary string
     */
    public function render(
        LabelType $type,
        array $returnLines,
        array $destLines,
        string $formatId
    ): string;
}
