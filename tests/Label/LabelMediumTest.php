<?php
/**
 * LabelMedium Tests
 *
 * @package KsFraser\FaShippingLabel\Tests\Label
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Tests\Label;

use KsFraser\FaShippingLabel\Label\LabelMedium;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LabelMediumTest extends TestCase
{
    public function testConstructValid(): void
    {
        $medium = new LabelMedium('Test Label', 100.0, 50.0, 'label');

        $this->assertSame('Test Label', $medium->getName());
        $this->assertSame(100.0, $medium->getWidthMm());
        $this->assertSame(50.0, $medium->getHeightMm());
        $this->assertSame('label', $medium->getCategory());
    }

    public function testRejectsZeroDimensions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LabelMedium('Bad', 0.0, 50.0, 'label');
    }

    public function testRejectsNegativeDimensions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LabelMedium('Bad', -10.0, 50.0, 'label');
    }

    public function testPointConversion(): void
    {
        $medium = new LabelMedium('Test', 100.0, 50.0, 'label');

        // 100mm × 2.8346 ≈ 283.46pt
        $this->assertEqualsWithDelta(283.46, $medium->getWidthPt(), 0.01);
        $this->assertEqualsWithDelta(141.73, $medium->getHeightPt(), 0.01);
    }

    public function testFitsOnPrinterSheet(): void
    {
        $small = new LabelMedium('Small', 100.0, 50.0, 'label');
        $this->assertTrue($small->fitsOnPrinterSheet());

        $large = new LabelMedium('Large', 400.0, 300.0, 'label');
        $this->assertFalse($large->fitsOnPrinterSheet());
    }

    public function testGetAllMedia(): void
    {
        $all = LabelMedium::getAllMedia();

        $this->assertArrayHasKey('#10_envelope', $all);
        $this->assertArrayHasKey('dl_envelope', $all);
        $this->assertArrayHasKey('letter_paper', $all);
        $this->assertArrayHasKey('a4_paper', $all);
        $this->assertArrayHasKey('4x6_label', $all);
    }

    public function testGetByKey(): void
    {
        $medium = LabelMedium::getByKey('#10_envelope');
        $this->assertSame('#10 Envelope', $medium->getName());
        $this->assertSame('envelope', $medium->getCategory());
    }

    public function testGetByKeyUnknown(): void
    {
        $this->expectException(InvalidArgumentException::class);
        LabelMedium::getByKey('nonexistent');
    }
}
