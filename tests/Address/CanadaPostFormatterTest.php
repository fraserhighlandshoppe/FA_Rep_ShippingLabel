<?php
/**
 * Canada Post Formatter Tests
 *
 * @package KsFraser\FaShippingLabel\Tests\Address
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Tests\Address;

use KsFraser\FaShippingLabel\Address\Address;
use KsFraser\FaShippingLabel\Address\CanadaPostFormatter;
use PHPUnit\Framework\TestCase;

class CanadaPostFormatterTest extends TestCase
{
    /** @var CanadaPostFormatter */
    private $formatter;

    protected function setUp(): void
    {
        $this->formatter = new CanadaPostFormatter();
    }

    public function testFormatBasicDomesticAddress(): void
    {
        $address = new Address(
            'John Smith',
            '123 Main Street',
            null,
            'Ottawa',
            'ON',
            'K1A 0A6'
        );

        $lines = $this->formatter->formatForLabel($address);

        $this->assertCount(3, $lines);
        $this->assertSame('JOHN SMITH', $lines[0]);
        $this->assertSame('123 MAIN STREET', $lines[1]);
        $this->assertSame('OTTAWA ON  K1A 0A6', $lines[2]);
    }

    public function testFormatWithAddressLine2(): void
    {
        $address = new Address(
            'Jane Doe',
            '456 Elm Avenue',
            'Suite 2B',
            'Toronto',
            'Ontario',
            'M5V 2T6'
        );

        $lines = $this->formatter->formatForLabel($address);

        $this->assertCount(4, $lines);
        $this->assertSame('JANE DOE', $lines[0]);
        $this->assertSame('SUITE 2B', $lines[1]);
        $this->assertSame('456 ELM AVENUE', $lines[2]);
        // Province expanded to abbreviation
        $this->assertSame('TORONTO ON  M5V 2T6', $lines[3]);
    }

    public function testConvertsFullProvinceNameToAbbreviation(): void
    {
        $address = new Address(
            'Test Corp',
            '100 King St',
            null,
            'Vancouver',
            'British Columbia',
            'V6B 1A1'
        );

        $lines = $this->formatter->formatForLabel($address);

        $lastLine = end($lines);
        $this->assertStringContainsString('BC', $lastLine);
    }

    public function testPreservesTwoLetterProvinceCode(): void
    {
        $address = new Address(
            'Test Corp',
            '100 King St',
            null,
            'Halifax',
            'NS',
            'B3H 1A1'
        );

        $lines = $this->formatter->formatForLabel($address);
        $lastLine = end($lines);
        $this->assertStringContainsString('NS', $lastLine);
    }

    public function testRemovesHashSymbol(): void
    {
        $address = new Address(
            'Test Corp',
            '#200 - 123 Main St',
            null,
            'Ottawa',
            'ON',
            'K1A 0A6'
        );

        $lines = $this->formatter->formatForLabel($address);

        // No '#' should remain
        foreach ($lines as $line) {
            $this->assertStringNotContainsString('#', $line);
        }
    }

    public function testRemovesCommas(): void
    {
        $address = new Address(
            'Smith, John',
            '123 Main, Street',
            null,
            'Ottawa',
            'ON',
            'K1A 0A6'
        );

        $lines = $this->formatter->formatForLabel($address);

        foreach ($lines as $line) {
            $this->assertStringNotContainsString(',', $line);
        }
    }

    public function testFormatsPostalCodeWithSpace(): void
    {
        $address = new Address(
            'Test',
            '100 Main',
            null,
            'Ottawa',
            'ON',
            'K1A0A6'  // no space
        );

        $lines = $this->formatter->formatForLabel($address);
        $lastLine = end($lines);

        // Should have space in postal code
        $this->assertStringContainsString('K1A 0A6', $lastLine);
    }

    public function testInternationalAddressIncludesCountry(): void
    {
        $address = new Address(
            'John Doe',
            '100 Broadway',
            null,
            'New York',
            'NY',
            '10001',
            'United States'
        );

        $lines = $this->formatter->formatForLabel($address);

        $lastLine = end($lines);
        $this->assertSame('UNITED STATES', $lastLine);
    }

    public function testDomesticAddressExcludesCanada(): void
    {
        $address = new Address(
            'Test',
            '100 Main',
            null,
            'Ottawa',
            'ON',
            'K1A 0A6',
            'Canada'  // should NOT appear
        );

        $lines = $this->formatter->formatForLabel($address);

        foreach ($lines as $line) {
            $this->assertNotSame('CANADA', $line);
        }
    }

    public function testMunicipalityLineSpacing(): void
    {
        $address = new Address(
            'Test',
            '100 Main',
            null,
            'Ottawa',
            'ON',
            'K1A 0A6'
        );

        $lines = $this->formatter->formatForLabel($address);
        $lastLine = end($lines);

        // T601: municipality<SP>province<2SP>postalCode
        $this->assertMatchesRegularExpression(
            '/^OTTAWA ON  K1A 0A6$/',
            $lastLine
        );
    }

    public function testGetName(): void
    {
        $this->assertSame('Canada Post', $this->formatter->getName());
    }

    public function testCharHeightConstraints(): void
    {
        $this->assertSame(2.0, $this->formatter->getMinCharHeightMm());
        $this->assertSame(5.0, $this->formatter->getMaxCharHeightMm());
    }

    public function testMaxLineLength(): void
    {
        $this->assertSame(40, $this->formatter->getMaxLineLength());
    }

    public function testMinLineSpacing(): void
    {
        $this->assertSame(0.5, $this->formatter->getMinLineSpacingMm());
    }
}
