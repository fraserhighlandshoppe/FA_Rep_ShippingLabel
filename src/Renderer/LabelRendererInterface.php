<?php
/**
 * Label Renderer Interface
 *
 * Contract for PDF rendering engines.
 *
 * @package KsFraser\FaShippingLabel\Renderer
 * @see FR-060
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Renderer;

use KsFraser\FaShippingLabel\Label\LabelLayoutInterface;
use KsFraser\FaShippingLabel\Label\LabelType;
use KsFraser\FaShippingLabel\Plugin\AveryPlugin\AveryTemplate;

/**
 * Contract for label PDF rendering.
 *
 * Implementations generate PDF content using different libraries
 * (FA FrontReport, TCPDF, etc.).
 */
interface LabelRendererInterface
{
    /**
     * Render a label with return and/or destination address.
     *
     * @param LabelLayoutInterface $layout      Layout with positions
     * @param LabelType            $type        Label type for rendering logic
     * @param string[]             $returnLines Formatted return address lines
     * @param string[]             $destLines   Formatted destination address lines
     *
     * @return string PDF content as binary string
     */
    public function render(
        LabelLayoutInterface $layout,
        LabelType $type,
        array $returnLines,
        array $destLines
    ): string;

    /**
     * Render a sheet of Avery labels with the same address.
     *
     * @param AveryTemplate $template Avery template definition
     * @param string[]      $lines    Formatted address lines
     *
     * @return string PDF content as binary string
     */
    public function renderAverySheet(AveryTemplate $template, array $lines): string;

    /**
     * Render a single label (for thermal printing).
     *
     * @param LabelLayoutInterface $layout Layout with medium dimensions
     * @param string[]             $lines  Formatted address lines
     *
     * @return string PDF content as binary string
     */
    public function renderSingleLabel(LabelLayoutInterface $layout, array $lines): string;
}
