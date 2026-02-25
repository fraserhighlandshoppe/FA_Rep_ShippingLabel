<?php
/**
 * Avery Label Sheet Output Plugin
 *
 * Renders labels in a grid layout matching Avery label sheets.
 * Fills the sheet with the same address (company or contact).
 *
 * @package KsFraser\FaShippingLabel\Plugin\AveryPlugin
 * @see FR-042
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Plugin\AveryPlugin;

use KsFraser\FaShippingLabel\Label\LabelType;
use KsFraser\FaShippingLabel\Plugin\OutputPluginInterface;
use KsFraser\FaShippingLabel\Renderer\LabelRendererInterface;

/**
 * Output plugin for Avery label sheet printing.
 *
 * Generates a Letter/A4-sized PDF with labels positioned
 * on a grid matching Avery template die-cut positions.
 */
class AveryOutputPlugin implements OutputPluginInterface
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
        return 'Avery Label Sheets';
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): string
    {
        return 'avery';
    }

    /**
     * {@inheritDoc}
     */
    public function getOutputFormats(): array
    {
        $formats = [];
        foreach (AveryTemplate::getAllTemplates() as $key => $template) {
            $formats[$key] = $template->getName();
        }
        return $formats;
    }

    /**
     * {@inheritDoc}
     *
     * Avery sheets support COMPANY_ONLY and CONTACT_ONLY label types.
     * Paired labels (with return + destination) don't fit on small Avery labels.
     */
    public function supportsLabelType(LabelType $type): bool
    {
        return $type->isCompanyOnly() || $type->isContactOnly();
    }

    /**
     * {@inheritDoc}
     *
     * Renders a full sheet of labels, filling all positions with the
     * selected address (company or contact).
     */
    public function render(
        LabelType $type,
        array $returnLines,
        array $destLines,
        string $formatId
    ): string {
        $template = AveryTemplate::getByKey($formatId);
        $lines = $type->isCompanyOnly() ? $returnLines : $destLines;

        return $this->renderer->renderAverySheet($template, $lines);
    }
}
