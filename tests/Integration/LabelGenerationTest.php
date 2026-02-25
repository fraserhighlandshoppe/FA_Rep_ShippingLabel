<?php
/**
 * Label Generation Integration Test
 *
 * Verifies that the entire pipeline from Address -> Formatter -> Plugin -> Renderer
 * produces a valid PDF document.
 *
 * @package KsFraser\FaShippingLabel\Tests\Integration
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Tests\Integration;

use PHPUnit\Framework\TestCase;
use KsFraser\FaShippingLabel\Address\Address;
use KsFraser\FaShippingLabel\Address\CanadaPostFormatter;
use KsFraser\FaShippingLabel\Label\LabelType;
use KsFraser\FaShippingLabel\Label\LabelMedium;
use KsFraser\FaShippingLabel\Label\LabelLayout;
use KsFraser\FaShippingLabel\Plugin\EnvelopePlugin\EnvelopeOutputPlugin;
use KsFraser\FaShippingLabel\Plugin\AveryPlugin\AveryOutputPlugin;
use KsFraser\FaShippingLabel\Plugin\ThermalPlugin\ThermalOutputPlugin;
use KsFraser\FaShippingLabel\Renderer\TcpdfRenderer;

class LabelGenerationTest extends TestCase
{
    private $renderer;
    private $formatter;

    protected function setUp(): void
    {
        $this->renderer = new TcpdfRenderer();
        $this->formatter = new CanadaPostFormatter();
    }

    /**
     * Test generating a paired envelope PDF.
     */
    public function testGenerateEnvelopePdf(): void
    {
        $companyAddress = Address::fromArray([
            'name' => 'KS FRASER CO',
            'addressLine1' => '123 MAIN ST',
            'city' => 'OTTAWA',
            'province' => 'ON',
            'postalCode' => 'K1A 0B1',
            'country' => 'CANADA'
        ]);

        $contactAddress = Address::fromArray([
            'name' => 'JOHN DOE',
            'addressLine1' => '456 ELM AVE',
            'city' => 'TORONTO',
            'province' => 'ON',
            'postalCode' => 'M5V 2L7',
            'country' => 'CANADA'
        ]);

        $returnLines = $this->formatter->formatForLabel($companyAddress);
        $destLines = $this->formatter->formatForLabel($contactAddress);

        $plugin = new EnvelopeOutputPlugin($this->renderer);
        $pdf = $plugin->render(LabelType::paired(), $returnLines, $destLines, '#10_envelope');

        $this->assertIsString($pdf);
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('TCPDF', $pdf);
    }

    /**
     * Test generating an Avery sheet PDF.
     */
    public function testGenerateAverySheetPdf(): void
    {
        $address = Address::fromArray([
            'name' => 'BATCH SHIPPER',
            'addressLine1' => '789 OAK CIR',
            'city' => 'VANCOUVER',
            'province' => 'BC',
            'postalCode' => 'V6B 1A1',
            'country' => 'CANADA'
        ]);

        $lines = $this->formatter->formatForLabel($address);

        $plugin = new AveryOutputPlugin($this->renderer);
        $pdf = $plugin->render(LabelType::contactOnly(), [], $lines, 'avery_5160');

        $this->assertIsString($pdf);
        $this->assertStringStartsWith('%PDF-', $pdf);
    }

    /**
     * Test generating a Thermal label PDF.
     */
    public function testGenerateThermalPdf(): void
    {
        $address = Address::fromArray([
            'name' => 'THERMAL TEST',
            'addressLine1' => '101 PINETAIL WAY',
            'city' => 'CALGARY',
            'province' => 'AB',
            'postalCode' => 'T2P 2H1',
            'country' => 'CANADA'
        ]);

        $lines = $this->formatter->formatForLabel($address);

        $plugin = new ThermalOutputPlugin($this->renderer);
        $pdf = $plugin->render(LabelType::contactOnly(), [], $lines, 'thermal_4x6');

        $this->assertIsString($pdf);
        $this->assertStringStartsWith('%PDF-', $pdf);
    }
}
