<?php
/**
 * Avery Label Template Definitions
 *
 * Registry of Avery (and compatible) label sheet templates
 * with grid dimensions, margins, and label sizes.
 *
 * @package KsFraser\FaShippingLabel\Plugin\AveryPlugin
 * @see FR-042
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Plugin\AveryPlugin;

use InvalidArgumentException;

/**
 * Avery label template definition.
 *
 * Defines the physical layout of a label sheet:
 * label dimensions, grid (rows × columns), margins, and gaps.
 * All dimensions in millimeters.
 */
class AveryTemplate
{
    /** @var string Template name (e.g., "Avery 5160") */
    private $name;

    /** @var float Label width in mm */
    private $labelWidthMm;

    /** @var float Label height in mm */
    private $labelHeightMm;

    /** @var int Number of columns */
    private $columns;

    /** @var int Number of rows */
    private $rows;

    /** @var float Left margin of sheet in mm */
    private $marginLeftMm;

    /** @var float Top margin of sheet in mm */
    private $marginTopMm;

    /** @var float Horizontal gap between labels in mm */
    private $gapHorizontalMm;

    /** @var float Vertical gap between labels in mm */
    private $gapVerticalMm;

    /** @var float Sheet width in mm */
    private $sheetWidthMm;

    /** @var float Sheet height in mm */
    private $sheetHeightMm;

    /**
     * @param string $name             Template name
     * @param float  $labelWidthMm     Individual label width
     * @param float  $labelHeightMm    Individual label height
     * @param int    $columns          Number of label columns
     * @param int    $rows             Number of label rows
     * @param float  $marginLeftMm     Sheet left margin
     * @param float  $marginTopMm      Sheet top margin
     * @param float  $gapHorizontalMm  Horizontal gap between labels
     * @param float  $gapVerticalMm    Vertical gap between labels
     * @param float  $sheetWidthMm     Sheet width (Letter or A4)
     * @param float  $sheetHeightMm    Sheet height
     */
    public function __construct(
        string $name,
        float $labelWidthMm,
        float $labelHeightMm,
        int $columns,
        int $rows,
        float $marginLeftMm,
        float $marginTopMm,
        float $gapHorizontalMm,
        float $gapVerticalMm,
        float $sheetWidthMm,
        float $sheetHeightMm
    ) {
        $this->name = $name;
        $this->labelWidthMm = $labelWidthMm;
        $this->labelHeightMm = $labelHeightMm;
        $this->columns = $columns;
        $this->rows = $rows;
        $this->marginLeftMm = $marginLeftMm;
        $this->marginTopMm = $marginTopMm;
        $this->gapHorizontalMm = $gapHorizontalMm;
        $this->gapVerticalMm = $gapVerticalMm;
        $this->sheetWidthMm = $sheetWidthMm;
        $this->sheetHeightMm = $sheetHeightMm;
    }

    /** @return string */
    public function getName(): string { return $this->name; }

    /** @return float */
    public function getLabelWidthMm(): float { return $this->labelWidthMm; }

    /** @return float */
    public function getLabelHeightMm(): float { return $this->labelHeightMm; }

    /** @return int */
    public function getColumns(): int { return $this->columns; }

    /** @return int */
    public function getRows(): int { return $this->rows; }

    /** @return float */
    public function getMarginLeftMm(): float { return $this->marginLeftMm; }

    /** @return float */
    public function getMarginTopMm(): float { return $this->marginTopMm; }

    /** @return float */
    public function getGapHorizontalMm(): float { return $this->gapHorizontalMm; }

    /** @return float */
    public function getGapVerticalMm(): float { return $this->gapVerticalMm; }

    /** @return float */
    public function getSheetWidthMm(): float { return $this->sheetWidthMm; }

    /** @return float */
    public function getSheetHeightMm(): float { return $this->sheetHeightMm; }

    /**
     * Get total labels per sheet.
     *
     * @return int
     */
    public function getLabelsPerSheet(): int
    {
        return $this->columns * $this->rows;
    }

    /**
     * Get the position (x, y) of a specific label by index (0-based).
     *
     * @param int $index Label index (0 = top-left, fills left-to-right, top-to-bottom)
     *
     * @return array{x: float, y: float} Position in mm from sheet origin
     * @throws InvalidArgumentException If index is out of range
     */
    public function getLabelPosition(int $index): array
    {
        if ($index < 0 || $index >= $this->getLabelsPerSheet()) {
            throw new InvalidArgumentException(
                sprintf("Label index %d out of range (0–%d).", $index, $this->getLabelsPerSheet() - 1)
            );
        }

        $col = $index % $this->columns;
        $row = intdiv($index, $this->columns);

        $x = $this->marginLeftMm + $col * ($this->labelWidthMm + $this->gapHorizontalMm);
        $y = $this->marginTopMm + $row * ($this->labelHeightMm + $this->gapVerticalMm);

        return ['x' => $x, 'y' => $y];
    }

    /**
     * Validate that labels fit within the sheet dimensions.
     *
     * @return bool True if all labels fit without exceeding sheet edges
     */
    public function validate(): bool
    {
        $totalWidth = $this->marginLeftMm
            + $this->columns * $this->labelWidthMm
            + ($this->columns - 1) * $this->gapHorizontalMm;

        $totalHeight = $this->marginTopMm
            + $this->rows * $this->labelHeightMm
            + ($this->rows - 1) * $this->gapVerticalMm;

        return $totalWidth <= $this->sheetWidthMm
            && $totalHeight <= $this->sheetHeightMm;
    }

    /**
     * Get all predefined Avery templates.
     *
     * @return array<string, self>
     */
    public static function getAllTemplates(): array
    {
        $letter_w = 215.9;
        $letter_h = 279.4;
        $a4_w = 210.0;
        $a4_h = 297.0;

        return [
            // Avery 5160: 1" × 2⅝" (25.4mm × 66.7mm), 30/sheet, Letter
            'avery_5160' => new self(
                'Avery 5160 (30/sheet)',
                66.7, 25.4,    // label W × H
                3, 10,         // cols × rows
                5.0, 12.7,     // margins L, T
                3.2, 0.0,      // gaps H, V
                $letter_w, $letter_h
            ),

            // Avery 5163: 2" × 4" (50.8mm × 101.6mm), 10/sheet, Letter
            'avery_5163' => new self(
                'Avery 5163 (10/sheet)',
                101.6, 50.8,
                2, 5,
                4.8, 12.7,
                5.1, 0.0,
                $letter_w, $letter_h
            ),

            // Avery 5167: ½" × 1¾" (12.7mm × 44.5mm), 80/sheet, Letter
            'avery_5167' => new self(
                'Avery 5167 (80/sheet)',
                44.5, 12.7,
                4, 20,
                8.5, 12.7,
                7.9, 0.0,
                $letter_w, $letter_h
            ),

            // Avery L7160: 21.2mm × 63.5mm, 21/sheet, A4
            'avery_l7160' => new self(
                'Avery L7160 (21/sheet)',
                63.5, 38.1,
                3, 7,
                7.2, 15.1,
                2.5, 0.0,
                $a4_w, $a4_h
            ),

            // Avery L7163: 38.1mm × 99.1mm, 14/sheet, A4
            'avery_l7163' => new self(
                'Avery L7163 (14/sheet)',
                99.1, 38.1,
                2, 7,
                4.7, 15.1,
                2.5, 0.0,
                $a4_w, $a4_h
            ),
        ];
    }

    /**
     * Get a specific template by key.
     *
     * @param string $key Template key
     *
     * @return self
     * @throws InvalidArgumentException If key not found
     */
    public static function getByKey(string $key): self
    {
        $all = self::getAllTemplates();
        if (!isset($all[$key])) {
            throw new InvalidArgumentException(
                sprintf("Unknown Avery template '%s'. Available: %s", $key, implode(', ', array_keys($all)))
            );
        }
        return $all[$key];
    }
}
