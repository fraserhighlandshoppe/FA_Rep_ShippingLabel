<?php
/**
 * Thermal Label Format Definitions
 *
 * Registry of thermal printer label sizes.
 *
 * @package KsFraser\FaShippingLabel\Plugin\ThermalPlugin
 * @see FR-043
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Plugin\ThermalPlugin;

use InvalidArgumentException;

/**
 * Thermal printer label format definition.
 *
 * Defines label dimensions for thermal printer output.
 * All dimensions in millimeters.
 */
class ThermalFormat
{
    /** @var string Format name */
    private $name;

    /** @var float Label width in mm */
    private $widthMm;

    /** @var float Label height in mm */
    private $heightMm;

    /**
     * @param string $name     Human-readable name
     * @param float  $widthMm  Label width in mm
     * @param float  $heightMm Label height in mm
     */
    public function __construct(string $name, float $widthMm, float $heightMm)
    {
        if ($widthMm <= 0 || $heightMm <= 0) {
            throw new InvalidArgumentException('Thermal format dimensions must be positive.');
        }

        $this->name = $name;
        $this->widthMm = $widthMm;
        $this->heightMm = $heightMm;
    }

    /** @return string */
    public function getName(): string { return $this->name; }

    /** @return float */
    public function getWidthMm(): float { return $this->widthMm; }

    /** @return float */
    public function getHeightMm(): float { return $this->heightMm; }

    /**
     * Get all predefined thermal formats.
     *
     * @return array<string, self>
     */
    public static function getAllFormats(): array
    {
        return [
            // 4×6" shipping label (standard for most carriers)
            'thermal_4x6' => new self('4×6 Shipping', 152.0, 102.0),

            // 2.25×1.25" product/barcode label
            'thermal_2x1' => new self('2¼×1¼ Product', 57.0, 32.0),

            // Dymo LabelWriter standard (2-5/16 × 4")
            'thermal_dymo' => new self('Dymo LabelWriter (2⁵⁄₁₆×4)', 59.0, 102.0),
        ];
    }

    /**
     * Get a specific format by key.
     *
     * @param string $key Format key
     *
     * @return self
     * @throws InvalidArgumentException If key not found
     */
    public static function getByKey(string $key): self
    {
        $all = self::getAllFormats();
        if (!isset($all[$key])) {
            throw new InvalidArgumentException(
                sprintf("Unknown thermal format '%s'. Available: %s", $key, implode(', ', array_keys($all)))
            );
        }
        return $all[$key];
    }
}
