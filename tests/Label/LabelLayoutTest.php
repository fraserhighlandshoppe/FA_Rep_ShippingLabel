<?php
/**
 * LabelLayout Tests
 *
 * @package KsFraser\FaShippingLabel\Tests\Label
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Tests\Label;

use KsFraser\FaShippingLabel\Label\EnvelopeLayout;
use KsFraser\FaShippingLabel\Label\LabelLayout;
use KsFraser\FaShippingLabel\Label\LabelMedium;
use PHPUnit\Framework\TestCase;

class LabelLayoutTest extends TestCase
{
    public function testReturnAddressPositionWithinBounds(): void
    {
        $medium = LabelMedium::getByKey('#10_envelope');
        $layout = new LabelLayout($medium);

        $pos = $layout->getReturnAddressPosition();

        $this->assertGreaterThan(0, $pos['x']);
        $this->assertGreaterThan(0, $pos['y']);
        $this->assertGreaterThan(0, $pos['width']);
        $this->assertGreaterThan(0, $pos['height']);
        $this->assertLessThan($medium->getWidthMm(), $pos['x'] + $pos['width']);
        $this->assertLessThan($medium->getHeightMm(), $pos['y'] + $pos['height']);
    }

    public function testDestinationAddressPositionWithinBounds(): void
    {
        $medium = LabelMedium::getByKey('#10_envelope');
        $layout = new LabelLayout($medium);

        $pos = $layout->getDestinationAddressPosition();

        $this->assertGreaterThan(0, $pos['x']);
        $this->assertGreaterThan(0, $pos['y']);
        $this->assertLessThanOrEqual($medium->getWidthMm(), $pos['x'] + $pos['width']);
        $this->assertLessThanOrEqual($medium->getHeightMm(), $pos['y'] + $pos['height']);
    }

    public function testReturnFontSizeInRange(): void
    {
        $medium = LabelMedium::getByKey('#10_envelope');
        $layout = new LabelLayout($medium);

        $fontSize = $layout->getReturnFontSizePt();
        $this->assertGreaterThan(0, $fontSize);
        $this->assertLessThanOrEqual(14.0, $fontSize); // Canada Post max
    }

    public function testGetMedium(): void
    {
        $medium = LabelMedium::getByKey('letter_paper');
        $layout = new LabelLayout($medium);

        $this->assertSame($medium, $layout->getMedium());
    }

    public function testEnvelopeLayoutDestinationIsCenterRight(): void
    {
        $medium = LabelMedium::getByKey('#10_envelope');
        $layout = new EnvelopeLayout($medium);

        $pos = $layout->getDestinationAddressPosition();

        // Destination should be in right half of envelope
        $this->assertGreaterThanOrEqual($medium->getWidthMm() * 0.3, $pos['x']);
    }

    public function testFontSizeScaling(): void
    {
        $small = new LabelMedium('Small', 100.0, 50.0, 'label');
        $large = new LabelMedium('Large', 300.0, 200.0, 'paper');

        $smallLayout = new LabelLayout($small);
        $largeLayout = new LabelLayout($large);

        // Larger medium should get equal or larger font
        $this->assertGreaterThanOrEqual(
            $smallLayout->getDestinationFontSizePt(),
            $largeLayout->getDestinationFontSizePt()
        );
    }
}
