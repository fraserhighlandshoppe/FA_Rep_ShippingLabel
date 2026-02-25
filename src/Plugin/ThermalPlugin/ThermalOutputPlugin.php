<?php
/**
 * Thermal Printer Output Plugin
 *
 * Renders single labels sized for thermal printer output.
 * One label per page, page sized to match thermal label stock.
 *
 * @package KsFraser\FaShippingLabel\Plugin\ThermalPlugin
 * @see FR-043
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Plugin\ThermalPlugin;

use KsFraser\FaShippingLabel\Label\LabelLayout;
use KsFraser\FaShippingLabel\Label\LabelMedium;
use KsFraser\FaShippingLabel\Label\LabelType;
use KsFraser\FaShippingLabel\Plugin\OutputPluginInterface;
use KsFraser\FaShippingLabel\Renderer\LabelRendererInterface;

/**
 * Output plugin for thermal printer labels.
 *
 * Generates a PDF with page size matching the thermal label stock.
 * Output is a single-label page suitable for thermal printing.
 */
class ThermalOutputPlugin implements OutputPluginInterface
{
    /** @var LabelRendererInterface */
    private $renderer;

    /**
     * @param LabelRendererInterface $renderer PDF renderer
     */
    public function __construct(LabelRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'Thermal Printer';
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): string
    {
        return 'thermal';
    }

    /**
     * {@inheritDoc}
     */
    public function getOutputFormats(): array
    {
        $formats = [];
        foreach (ThermalFormat::getAllFormats() as $key => $format) {
            $formats[$key] = $format->getName();
        }
        return $formats;
    }

    /**
     * {@inheritDoc}
     *
     * Thermal labels support COMPANY_ONLY and CONTACT_ONLY.
     * Paired labels typically don't fit on small thermal stock.
     */
    public function supportsLabelType(LabelType $type): bool
    {
        return $type->isCompanyOnly() || $type->isContactOnly();
    }

    /**
     * {@inheritDoc}
     */
    public function render(
        LabelType $type,
        array $returnLines,
        array $destLines,
        string $formatId
    ): string {
        $format = ThermalFormat::getByKey($formatId);

        // Create a LabelMedium from the thermal format
        $medium = new LabelMedium(
            $format->getName(),
            $format->getWidthMm(),
            $format->getHeightMm(),
            'label'
        );

        $layout = new LabelLayout($medium);
        $lines = $type->isCompanyOnly() ? $returnLines : $destLines;

        return $this->renderer->renderSingleLabel($layout, $lines);
    }
}
