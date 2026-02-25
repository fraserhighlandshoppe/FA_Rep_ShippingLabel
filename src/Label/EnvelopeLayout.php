<?php
/**
 * Envelope Layout
 *
 * Specialized layout for envelopes, overriding the base layout
 * with envelope-specific positioning rules.
 *
 * @package KsFraser\FaShippingLabel\Label
 * @see FR-050, FR-051
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Label;

/**
 * Envelope-specific layout engine.
 *
 * Handles envelope orientation (landscape) and tighter positioning
 * suitable for envelope printing.
 */
class EnvelopeLayout extends LabelLayout
{
    /** @var float Smaller margin for envelopes */
    protected const MARGIN_MM = 10.0;

    /** @var float Return address takes less width on envelope */
    protected const RETURN_WIDTH_FRACTION = 0.35;

    /** @var float Return address takes less height on envelope */
    protected const RETURN_HEIGHT_FRACTION = 0.35;

    /**
     * {@inheritDoc}
     *
     * Destination address positioned in center-right of envelope.
     */
    public function getDestinationAddressPosition(): array
    {
        $width = $this->medium->getWidthMm() * self::DEST_WIDTH_FRACTION;
        $height = $this->medium->getHeightMm() * self::DEST_HEIGHT_FRACTION;

        // Envelopes: destination slightly right of center, vertically centered
        $x = $this->medium->getWidthMm() * 0.40;
        $y = ($this->medium->getHeightMm() - $height) / 2;

        // Ensure fits within right margin
        if ($x + $width > $this->medium->getWidthMm() - self::MARGIN_MM) {
            $x = $this->medium->getWidthMm() - $width - self::MARGIN_MM;
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
     *
     * Slightly smaller font for envelope return address.
     */
    public function getReturnFontSizePt(): float
    {
        return $this->scaleFontSize(7.0);
    }

    /**
     * {@inheritDoc}
     *
     * Standard destination font for envelopes.
     */
    public function getDestinationFontSizePt(): float
    {
        return $this->scaleFontSize(10.0);
    }
}
