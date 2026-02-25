<?php
/**
 * Label Layout Interface
 *
 * Contract for layout engines that position address blocks on media.
 *
 * @package KsFraser\FaShippingLabel\Label
 * @see FR-050, FR-051, FR-052, FR-053
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Label;

/**
 * Contract for label layout calculations.
 *
 * A layout determines where address blocks are positioned
 * on a given medium.
 */
interface LabelLayoutInterface
{
    /**
     * Get the position and dimensions of the return address block.
     *
     * @return array{x: float, y: float, width: float, height: float} Position in mm
     */
    public function getReturnAddressPosition(): array;

    /**
     * Get the position and dimensions of the destination address block.
     *
     * @return array{x: float, y: float, width: float, height: float} Position in mm
     */
    public function getDestinationAddressPosition(): array;

    /**
     * Get the medium this layout targets.
     *
     * @return LabelMedium
     */
    public function getMedium(): LabelMedium;

    /**
     * Get the recommended font size in points for the return address.
     *
     * @return float
     */
    public function getReturnFontSizePt(): float;

    /**
     * Get the recommended font size in points for the destination address.
     *
     * @return float
     */
    public function getDestinationFontSizePt(): float;
}
