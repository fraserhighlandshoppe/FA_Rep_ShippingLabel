<?php
/**
 * LabelType Tests
 *
 * @package KsFraser\FaShippingLabel\Tests\Label
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Tests\Label;

use KsFraser\FaShippingLabel\Label\LabelType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LabelTypeTest extends TestCase
{
    public function testCompanyOnly(): void
    {
        $type = new LabelType(LabelType::COMPANY_ONLY);

        $this->assertSame('company_only', $type->getValue());
        $this->assertTrue($type->isCompanyOnly());
        $this->assertFalse($type->isContactOnly());
        $this->assertFalse($type->isPaired());
        $this->assertFalse($type->requiresContact());
        $this->assertTrue($type->includesReturnAddress());
    }

    public function testContactOnly(): void
    {
        $type = new LabelType(LabelType::CONTACT_ONLY);

        $this->assertTrue($type->isContactOnly());
        $this->assertTrue($type->requiresContact());
        $this->assertFalse($type->includesReturnAddress());
    }

    public function testPaired(): void
    {
        $type = new LabelType(LabelType::PAIRED);

        $this->assertTrue($type->isPaired());
        $this->assertTrue($type->requiresContact());
        $this->assertTrue($type->includesReturnAddress());
    }

    public function testInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LabelType('invalid_type');
    }

    public function testGetValidTypes(): void
    {
        $types = LabelType::getValidTypes();
        $this->assertCount(3, $types);
        $this->assertContains(LabelType::COMPANY_ONLY, $types);
        $this->assertContains(LabelType::CONTACT_ONLY, $types);
        $this->assertContains(LabelType::PAIRED, $types);
    }

    public function testGetDisplayLabels(): void
    {
        $labels = LabelType::getDisplayLabels();
        $this->assertCount(3, $labels);
        $this->assertArrayHasKey(LabelType::COMPANY_ONLY, $labels);
        $this->assertArrayHasKey(LabelType::PAIRED, $labels);
    }
}
