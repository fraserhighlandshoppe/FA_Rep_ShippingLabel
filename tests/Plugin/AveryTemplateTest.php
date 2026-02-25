<?php
/**
 * AveryTemplate Tests
 *
 * @package KsFraser\FaShippingLabel\Tests\Plugin
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Tests\Plugin;

use KsFraser\FaShippingLabel\Plugin\AveryPlugin\AveryTemplate;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AveryTemplateTest extends TestCase
{
    public function testAvery5160Template(): void
    {
        $template = AveryTemplate::getByKey('avery_5160');

        $this->assertSame('Avery 5160 (30/sheet)', $template->getName());
        $this->assertSame(3, $template->getColumns());
        $this->assertSame(10, $template->getRows());
        $this->assertSame(30, $template->getLabelsPerSheet());
    }

    public function testAvery5163Template(): void
    {
        $template = AveryTemplate::getByKey('avery_5163');

        $this->assertSame(2, $template->getColumns());
        $this->assertSame(5, $template->getRows());
        $this->assertSame(10, $template->getLabelsPerSheet());
    }

    public function testGetLabelPosition(): void
    {
        $template = AveryTemplate::getByKey('avery_5160');

        // First label: top-left
        $pos = $template->getLabelPosition(0);
        $this->assertSame(5.0, $pos['x']); // marginLeft
        $this->assertSame(12.7, $pos['y']); // marginTop

        // Second label (next column right)
        $pos1 = $template->getLabelPosition(1);
        $this->assertGreaterThan($pos['x'], $pos1['x']);
    }

    public function testGetLabelPositionOutOfRange(): void
    {
        $template = AveryTemplate::getByKey('avery_5160');

        $this->expectException(InvalidArgumentException::class);
        $template->getLabelPosition(30); // 0–29 valid
    }

    public function testGetLabelPositionNegative(): void
    {
        $template = AveryTemplate::getByKey('avery_5160');

        $this->expectException(InvalidArgumentException::class);
        $template->getLabelPosition(-1);
    }

    public function testAllTemplatesValidate(): void
    {
        foreach (AveryTemplate::getAllTemplates() as $key => $template) {
            $this->assertTrue(
                $template->validate(),
                sprintf("Template '%s' failed validation: labels exceed sheet bounds.", $key)
            );
        }
    }

    public function testGetByKeyUnknown(): void
    {
        $this->expectException(InvalidArgumentException::class);
        AveryTemplate::getByKey('nonexistent');
    }

    public function testGetAllTemplatesCount(): void
    {
        $all = AveryTemplate::getAllTemplates();
        $this->assertCount(5, $all);
    }
}
