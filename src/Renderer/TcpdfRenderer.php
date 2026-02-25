<?php
/**
 * TCPDF Renderer
 *
 * Renders labels using the TCPDF library.
 * Provides better UTF-8 support and precise positioning than FrontReport.
 *
 * @package KsFraser\FaShippingLabel\Renderer
 * @see FR-060
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Renderer;

use KsFraser\FaShippingLabel\Label\LabelLayoutInterface;
use KsFraser\FaShippingLabel\Label\LabelType;
use KsFraser\FaShippingLabel\Plugin\AveryPlugin\AveryTemplate;
use TCPDF;

/**
 * PDF renderer using TCPDF.
 */
class TcpdfRenderer implements LabelRendererInterface
{
    /** @var string Font family for labels */
    private const FONT_FAMILY = 'helvetica';

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

        $pdf = new TCPDF(
            $medium->getWidthMm() > $medium->getHeightMm() ? 'L' : 'P',
            'mm',
            [$medium->getWidthMm(), $medium->getHeightMm()],
            true,
            'UTF-8',
            false
        );

        $this->configurePdf($pdf);
        $pdf->AddPage();

        // Render return address block (if applicable)
        if ($type->isCompanyOnly() || $type->isPaired()) {
            $pos = $layout->getReturnAddressPosition();
            $fontSize = $layout->getReturnFontSizePt();
            $this->drawAddressBlock($pdf, $returnLines, $pos, $fontSize);
        }

        // Render destination address block (if applicable)
        if ($type->isContactOnly() || $type->isPaired()) {
            $pos = $layout->getDestinationAddressPosition();
            $fontSize = $layout->getDestinationFontSizePt();
            $this->drawAddressBlock($pdf, $destLines, $pos, $fontSize);
        }

        return $pdf->Output('', 'S');
    }

    /**
     * {@inheritDoc}
     */
    public function renderAverySheet(AveryTemplate $template, array $lines): string
    {
        $pdf = new TCPDF(
            'P',
            'mm',
            [$template->getSheetWidthMm(), $template->getSheetHeightMm()],
            true,
            'UTF-8',
            false
        );

        $this->configurePdf($pdf);
        $pdf->AddPage();

        $fontSize = $this->calcFontSizeForLabel(
            $template->getLabelWidthMm(),
            $template->getLabelHeightMm(),
            count($lines)
        );

        $total = $template->getLabelsPerSheet();
        for ($i = 0; $i < $total; $i++) {
            $pos = $template->getLabelPosition($i);

            $block = [
                'x'      => $pos['x'],
                'y'      => $pos['y'],
                'width'  => $template->getLabelWidthMm(),
                'height' => $template->getLabelHeightMm(),
            ];

            $this->drawAddressBlock($pdf, $lines, $block, $fontSize, 2.0);
        }

        return $pdf->Output('', 'S');
    }

    /**
     * {@inheritDoc}
     */
    public function renderSingleLabel(LabelLayoutInterface $layout, array $lines): string
    {
        $medium = $layout->getMedium();

        $pdf = new TCPDF(
            $medium->getWidthMm() > $medium->getHeightMm() ? 'L' : 'P',
            'mm',
            [$medium->getWidthMm(), $medium->getHeightMm()],
            true,
            'UTF-8',
            false
        );

        $this->configurePdf($pdf);
        $pdf->AddPage();

        $padding = 5.0;
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

        $this->drawAddressBlock($pdf, $lines, $block, $fontSize);

        return $pdf->Output('', 'S');
    }

    /**
     * Configure common PDF settings.
     */
    private function configurePdf(TCPDF $pdf): void
    {
        $pdf->SetCreator('FA_Rep_ShippingLabel');
        $pdf->SetAuthor('FrontAccounting');
        $pdf->SetTitle('Shipping Label');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
    }

    /**
     * Draw an address block at the specified position.
     */
    private function drawAddressBlock(
        TCPDF $pdf,
        array $lines,
        array $position,
        float $fontSize,
        float $padding = 3.0
    ): void {
        if (empty($lines)) {
            return;
        }

        $pdf->SetFont(self::FONT_FAMILY, '', $fontSize);

        $x = $position['x'] + $padding;
        $y = $position['y'] + $padding;
        $lineHeight = $fontSize * 0.3528 * 1.4; // Pt to mm with 1.4 leading

        foreach ($lines as $line) {
            $pdf->SetXY($x, $y);
            $pdf->Cell($position['width'] - 2 * $padding, $lineHeight, $line, 0, 0, 'L');
            $y += $lineHeight;
        }
    }

    /**
     * Calculate appropriate font size for a label of given dimensions.
     */
    private function calcFontSizeForLabel(
        float $widthMm,
        float $heightMm,
        int $lineCount
    ): float {
        if ($lineCount <= 0) {
            return 10.0;
        }

        $padding = 4.0;
        $availableHeight = $heightMm - $padding;
        $lineHeightMm = $availableHeight / $lineCount;

        $fontSizePt = $lineHeightMm / 1.4 / 0.3528;

        return max(5.7, min($fontSizePt, 14.0));
    }
}
