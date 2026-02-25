<?php
/**
 * ThermalFormat Tests
 *
 * @package KsFraser\FaShippingLabel\Tests\Plugin
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Tests\Plugin;

use KsFraser\FaShippingLabel\Plugin\ThermalPlugin\ThermalFormat;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ThermalFormatTest extends TestCase
{
    public function testConstructValid(): void
    {
        $format = new ThermalFormat('Test', 100.0, 50.0);

        $this->assertSame('Test', $format->getName());
        $this->assertSame(100.0, $format->getWidthMm());
        $this->assertSame(50.0, $format->getHeightMm());
    }

    public function testRejectsZeroDimensions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ThermalFormat('Bad', 0.0, 50.0);
    }

    public function testGetAllFormats(): void
    {
        $all = ThermalFormat::getAllFormats();

        $this->assertArrayHasKey('thermal_4x6', $all);
        $this->assertArrayHasKey('thermal_2x1', $all);
        $this->assertArrayHasKey('thermal_dymo', $all);
        $this->assertCount(3, $all);
    }

    public function testGetByKey(): void
    {
        $format = ThermalFormat::getByKey('thermal_4x6');
        $this->assertSame('4×6 Shipping', $format->getName());
    }

    public function testGetByKeyUnknown(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ThermalFormat::getByKey('nonexistent');
    }
}
