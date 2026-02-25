<?php
/**
 * FrontReport PDF Renderer
 *
 * Renders labels using FA's built-in FrontReport class (FPDF-based).
 * This renderer requires an active FA session.
 *
 * @package KsFraser\FaShippingLabel\Renderer
 * @see FR-060, FR-061
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Renderer;

use KsFraser\FaShippingLabel\Label\LabelLayoutInterface;
use KsFraser\FaShippingLabel\Label\LabelType;
use KsFraser\FaShippingLabel\Plugin\AveryPlugin\AveryTemplate;

/**
 * PDF renderer using FA's FrontReport class.
 *
 * This renderer integrates with FA's reporting/includes/pdf_report.inc
 * to generate PDFs consistent with FA's other reports.
 */
class FrontReportRenderer implements LabelRendererInterface
{
    /** @var string Font family for labels */
    private const FONT_FAMILY = 'Helvetica';

    /**
     * {@inheritDoc}
     */
    public function render(
        LabelLayoutInterface $layout,
        LabelType $type,
        array $returnLines,
        array $destLines
    ): string {
        $medium = $layout->getMedium();

        // Start output buffering to capture PDF
        ob_start();

        // Create FA report with custom page size
        $pageSize = [$medium->getWidthPt(), $medium->getHeightPt()];
        $rep = new \FrontReport('Shipping Label', '', $pageSize, 0);
        $rep->SetHeaderType(null); // No standard FA header
        $rep->NewPage();

        // Render return address block (if applicable)
        if ($type->isCompanyOnly() || $type->isPaired()) {
            $pos = $layout->getReturnAddressPosition();
            $fontSize = $layout->getReturnFontSizePt();
            $this->drawAddressBlock($rep, $returnLines, $pos, $fontSize);
        }

        // Render destination address block (if applicable)
        if ($type->isContactOnly() || $type->isPaired()) {
            $pos = $layout->getDestinationAddressPosition();
            $fontSize = $layout->getDestinationFontSizePt();
            $this->drawAddressBlock($rep, $destLines, $pos, $fontSize);
        }

        $rep->End();

        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderAverySheet(AveryTemplate $template, array $lines): string
    {
        ob_start();

        $pageSize = [$template->getSheetWidthMm() * 2.8346, $template->getSheetHeightMm() * 2.8346];
        $rep = new \FrontReport('Avery Labels', '', $pageSize, 0);
        $rep->SetHeaderType(null);
        $rep->NewPage();

        // Calculate appropriate font size for the label dimensions
        $fontSize = $this->calcFontSizeForLabel(
            $template->getLabelWidthMm(),
            $template->getLabelHeightMm(),
            count($lines)
        );

        // Fill every label position on the sheet
        $total = $template->getLabelsPerSheet();
        for ($i = 0; $i < $total; $i++) {
            $pos = $template->getLabelPosition($i);

            $block = [
                'x'      => $pos['x'],
                'y'      => $pos['y'],
                'width'  => $template->getLabelWidthMm(),
                'height' => $template->getLabelHeightMm(),
            ];

            $this->drawAddressBlock($rep, $lines, $block, $fontSize, 2.0);
        }

        $rep->End();

        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderSingleLabel(LabelLayoutInterface $layout, array $lines): string
    {
        $medium = $layout->getMedium();

        ob_start();

        $pageSize = [$medium->getWidthPt(), $medium->getHeightPt()];
        $rep = new \FrontReport('Label', '', $pageSize, 0);
        $rep->SetHeaderType(null);
        $rep->NewPage();

        // Center the address on the label with padding
        $padding = 5.0; // mm
        $block = [
            'x'      => $padding,
            'y'      => $padding,
            'width'  => $medium->getWidthMm() - 2 * $padding,
            'height' => $medium->getHeightMm() - 2 * $padding,
        ];

        $fontSize = $this->calcFontSizeForLabel(
            $block['width'],
            $block['height'],
            count($lines)
        );

        $this->drawAddressBlock($rep, $lines, $block, $fontSize);

        $rep->End();

        return ob_get_clean();
    }

    /**
     * Draw an address block at the specified position.
     *
     * @param \FrontReport $rep       FA report object
     * @param string[]     $lines     Formatted address lines
     * @param array        $position  {x, y, width, height} in mm
     * @param float        $fontSize  Font size in points
     * @param float        $padding   Internal padding in mm
     */
    private function drawAddressBlock(
        $rep,
        array $lines,
        array $position,
        float $fontSize,
        float $padding = 3.0
    ): void {
        if (empty($lines)) {
            return;
        }

        $rep->SetFont(self::FONT_FAMILY, '', $fontSize);

        $x = ($position['x'] + $padding) * 2.8346; // mm to pt
        $y = ($position['y'] + $padding) * 2.8346;
        $lineHeight = $fontSize * 1.4; // pt

        foreach ($lines as $line) {
            $rep->SetXY($x, $y);
            $rep->Cell(0, $lineHeight, $line, 0, 0, 'L');
            $y += $lineHeight;
        }
    }

    /**
     * Calculate appropriate font size for a label of given dimensions.
     *
     * Attempts to fit all lines within the label height,
     * respecting Canada Post character height limits (2–5mm).
     *
     * @param float $widthMm  Available width in mm
     * @param float $heightMm Available height in mm
     * @param int   $lineCount Number of address lines
     *
     * @return float Font size in points
     */
    private function calcFontSizeForLabel(
        float $widthMm,
        float $heightMm,
        int $lineCount
    ): float {
        if ($lineCount <= 0) {
            return 10.0;
        }

        $padding = 4.0; // mm total padding
        $availableHeight = $heightMm - $padding;
        $lineHeightMm = $availableHeight / $lineCount;

        // Convert mm to pt: 1pt ≈ 0.3528mm, so fontSize ≈ lineHeight / 1.4 / 0.3528
        $fontSizePt = $lineHeightMm / 1.4 / 0.3528;

        // Clamp between ~2mm and ~5mm character height (≈5.7pt to 14.2pt)
        return max(5.7, min($fontSizePt, 14.0));
    }
}
