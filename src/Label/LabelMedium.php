<?php
/**
 * Label Medium Definitions
 *
 * Registry of supported output media sizes for label printing.
 * All dimensions in millimeters.
 *
 * @package KsFraser\FaShippingLabel\Label
 * @see FR-041
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Label;

use InvalidArgumentException;

/**
 * Registry of label/envelope/paper medium sizes.
 *
 * Each medium is defined by its width, height, and category.
 * Dimensions are always in millimeters.
 */
class LabelMedium
{
    /** @var string */
    private $name;

    /** @var float Width in mm */
    private $widthMm;

    /** @var float Height in mm */
    private $heightMm;

    /** @var string Category: envelope, paper, label */
    private $category;

    /**
     * @param string $name     Human-readable name
     * @param float  $widthMm  Width in millimeters
     * @param float  $heightMm Height in millimeters
     * @param string $category Category (envelope/paper/label)
     */
    public function __construct(
        string $name,
        float $widthMm,
        float $heightMm,
        string $category
    ) {
        if ($widthMm <= 0 || $heightMm <= 0) {
            throw new InvalidArgumentException('Medium dimensions must be positive.');
        }

        $this->name = $name;
        $this->widthMm = $widthMm;
        $this->heightMm = $heightMm;
        $this->category = $category;
    }

    /** @return string */
    public function getName(): string
    {
        return $this->name;
    }

    /** @return float */
    public function getWidthMm(): float
    {
        return $this->widthMm;
    }

    /** @return float */
    public function getHeightMm(): float
    {
        return $this->heightMm;
    }

    /** @return string */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Get width in points (1 pt = 0.3528 mm, 1 mm = 2.8346 pt).
     *
     * @return float
     */
    public function getWidthPt(): float
    {
        return $this->widthMm * 2.8346;
    }

    /**
     * Get height in points.
     *
     * @return float
     */
    public function getHeightPt(): float
    {
        return $this->heightMm * 2.8346;
    }

    /**
     * Check if this medium fits within Letter/A4 for printing.
     *
     * @return bool True if either dimension ≤ 297mm (A4 long side)
     */
    public function fitsOnPrinterSheet(): bool
    {
        $maxDim = max($this->widthMm, $this->heightMm);
        return $maxDim <= 297.0; // A4 long edge
    }

    /**
     * Get all predefined media.
     *
     * @return array<string, self>
     */
    public static function getAllMedia(): array
    {
        return [
            '#10_envelope' => new self('#10 Envelope', 241.0, 105.0, 'envelope'),
            'dl_envelope'  => new self('DL Envelope', 220.0, 110.0, 'envelope'),
            'c5_envelope'  => new self('C5 Envelope', 229.0, 162.0, 'envelope'),
            'c4_envelope'  => new self('C4 Envelope', 324.0, 229.0, 'envelope'),
            '6x9_envelope' => new self('6×9 Envelope', 229.0, 152.0, 'envelope'),
            '9x12_envelope'=> new self('9×12 Envelope', 305.0, 229.0, 'envelope'),
            'letter_paper' => new self('Letter Paper', 216.0, 279.0, 'paper'),
            'a4_paper'     => new self('A4 Paper', 210.0, 297.0, 'paper'),
            '4x6_label'    => new self('4×6 Label', 152.0, 102.0, 'label'),
        ];
    }

    /**
     * Get a specific medium by key.
     *
     * @param string $key Medium key
     *
     * @return self
     * @throws InvalidArgumentException If key not found
     */
    public static function getByKey(string $key): self
    {
        $all = self::getAllMedia();
        if (!isset($all[$key])) {
            throw new InvalidArgumentException(
                sprintf("Unknown label medium '%s'. Available: %s", $key, implode(', ', array_keys($all)))
            );
        }
        return $all[$key];
    }
}
