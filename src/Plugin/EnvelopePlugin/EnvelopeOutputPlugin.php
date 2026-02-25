<?php
/**
 * Envelope Output Plugin
 *
 * Renders labels for direct printing on envelopes or full sheets.
 * Supports all LabelMedium envelope and paper sizes.
 *
 * @package KsFraser\FaShippingLabel\Plugin\EnvelopePlugin
 * @see FR-041
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Plugin\EnvelopePlugin;

use KsFraser\FaShippingLabel\Label\EnvelopeLayout;
use KsFraser\FaShippingLabel\Label\LabelLayout;
use KsFraser\FaShippingLabel\Label\LabelMedium;
use KsFraser\FaShippingLabel\Label\LabelType;
use KsFraser\FaShippingLabel\Plugin\OutputPluginInterface;
use KsFraser\FaShippingLabel\Renderer\LabelRendererInterface;
use InvalidArgumentException;

/**
 * Output plugin for direct envelope/sheet printing.
 *
 * Generates a single-page PDF sized to the selected medium,
 * with address blocks positioned per the layout engine.
 */
class EnvelopeOutputPlugin implements OutputPluginInterface
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
        return 'Envelope / Sheet';
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): string
    {
        return 'envelope';
    }

    /**
     * {@inheritDoc}
     */
    public function getOutputFormats(): array
    {
        $formats = [];
        foreach (LabelMedium::getAllMedia() as $key => $medium) {
            $formats[$key] = $medium->getName();
        }
        return $formats;
    }

    /**
     * {@inheritDoc}
     *
     * Supports all label types: company-only, contact-only, and paired.
     */
    public function supportsLabelType(LabelType $type): bool
    {
        return true;
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
        $medium = LabelMedium::getByKey($formatId);

        // Use EnvelopeLayout for envelopes, base LabelLayout for paper
        $layout = ($medium->getCategory() === 'envelope')
            ? new EnvelopeLayout($medium)
            : new LabelLayout($medium);

        return $this->renderer->render($layout, $type, $returnLines, $destLines);
    }
}
