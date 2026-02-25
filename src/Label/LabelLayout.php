<?php
/**
 * Label Layout Engine
 *
 * Computes address block positions for a given medium,
 * following Canada Post positioning guidelines.
 *
 * @package KsFraser\FaShippingLabel\Label
 * @see FR-050, FR-051, FR-052, FR-053
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Label;

/**
 * Default label layout engine.
 *
 * Positions:
 * - Return address: top-left corner, ~15mm inset
 * - Destination: center-to-lower-center of medium
 * - Font sizes scale with medium size
 */
class LabelLayout implements LabelLayoutInterface
{
    /** @var float Margin inset from edges in mm */
    protected const MARGIN_MM = 15.0;

    /** @var float Fraction of medium width for return address block */
    protected const RETURN_WIDTH_FRACTION = 0.40;

    /** @var float Fraction of medium height for return address block */
    protected const RETURN_HEIGHT_FRACTION = 0.30;

    /** @var float Fraction of medium width for destination address block */
    protected const DEST_WIDTH_FRACTION = 0.55;

    /** @var float Fraction of medium height for destination address block */
    protected const DEST_HEIGHT_FRACTION = 0.35;

    /** @var LabelMedium */
    protected $medium;

    /**
     * @param LabelMedium $medium The target output medium
     */
    public function __construct(LabelMedium $medium)
    {
        $this->medium = $medium;
    }

    /**
     * {@inheritDoc}
     *
     * Return address: top-left corner, inset by margin.
     */
    public function getReturnAddressPosition(): array
    {
        $width = $this->medium->getWidthMm() * self::RETURN_WIDTH_FRACTION;
        $height = $this->medium->getHeightMm() * self::RETURN_HEIGHT_FRACTION;

        return [
            'x'      => self::MARGIN_MM,
            'y'      => self::MARGIN_MM,
            'width'  => min($width, $this->medium->getWidthMm() - 2 * self::MARGIN_MM),
            'height' => min($height, $this->medium->getHeightMm() * 0.4),
        ];
    }

    /**
     * {@inheritDoc}
     *
     * Destination address: horizontally centered, vertically in lower-center.
     */
    public function getDestinationAddressPosition(): array
    {
        $width = $this->medium->getWidthMm() * self::DEST_WIDTH_FRACTION;
        $height = $this->medium->getHeightMm() * self::DEST_HEIGHT_FRACTION;

        // Horizontal: centered
        $x = ($this->medium->getWidthMm() - $width) / 2;

        // Vertical: lower-center (start at ~50% of medium height)
        $y = $this->medium->getHeightMm() * 0.50;

        // Ensure it doesn't exceed bottom margin
        if ($y + $height > $this->medium->getHeightMm() - self::MARGIN_MM) {
            $y = $this->medium->getHeightMm() - $height - self::MARGIN_MM;
        }

        return [
            'x'      => $x,
            'y'      => $y,
            'width'  => $width,
            'height' => $height,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getMedium(): LabelMedium
    {
        return $this->medium;
    }

    /**
     * {@inheritDoc}
     *
     * Return address: smaller font, scales with medium.
     * Targets ~3mm char height → ~8.5pt
     */
    public function getReturnFontSizePt(): float
    {
        return $this->scaleFontSize(8.0);
    }

    /**
     * {@inheritDoc}
     *
     * Destination address: larger font, scales with medium.
     * Targets ~4mm char height → ~11.3pt
     */
    public function getDestinationFontSizePt(): float
    {
        return $this->scaleFontSize(11.0);
    }

    /**
     * Scale font size relative to medium area vs reference medium (#10 envelope).
     *
     * Larger media get proportionally larger fonts, but capped at
     * Canada Post's 5mm maximum (~14pt).
     *
     * @param float $basePt Base font size for #10 envelope
     *
     * @return float Scaled font size in points
     */
    protected function scaleFontSize(float $basePt): float
    {
        // Reference area: #10 envelope (241 × 105 = 25,305 sq mm)
        $referenceArea = 241.0 * 105.0;
        $mediumArea = $this->medium->getWidthMm() * $this->medium->getHeightMm();

        $scale = sqrt($mediumArea / $referenceArea);
        $scaled = $basePt * $scale;

        // Cap at 14pt (~5mm) per Canada Post maximum
        return min($scaled, 14.0);
    }
}
