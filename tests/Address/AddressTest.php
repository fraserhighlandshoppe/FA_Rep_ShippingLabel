<?php
/**
 * Address Value Object Tests
 *
 * @package KsFraser\FaShippingLabel\Tests\Address
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Tests\Address;

use KsFraser\FaShippingLabel\Address\Address;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    public function testConstructWithValidData(): void
    {
        $address = new Address(
            'John Smith',
            '123 Main Street',
            'Unit 4',
            'Ottawa',
            'ON',
            'K1A 0A6',
            'Canada'
        );

        $this->assertSame('John Smith', $address->getName());
        $this->assertSame('123 Main Street', $address->getAddressLine1());
        $this->assertSame('Unit 4', $address->getAddressLine2());
        $this->assertSame('Ottawa', $address->getCity());
        $this->assertSame('ON', $address->getProvince());
        $this->assertSame('K1A 0A6', $address->getPostalCode());
        $this->assertSame('Canada', $address->getCountry());
    }

    public function testConstructWithNullAddressLine2(): void
    {
        $address = new Address('Jane Doe', '456 Elm St', null, 'Toronto', 'ON', 'M5V 2T6');

        $this->assertNull($address->getAddressLine2());
        $this->assertSame('', $address->getCountry());
    }

    public function testTrimsWhitespace(): void
    {
        $address = new Address('  John  ', '  123 Main  ', '  Unit 4  ', '  Ottawa  ', '  ON  ', '  K1A 0A6  ');

        $this->assertSame('John', $address->getName());
        $this->assertSame('123 Main', $address->getAddressLine1());
        $this->assertSame('Unit 4', $address->getAddressLine2());
    }

    public function testThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'name'");

        new Address('', '123 Main', null, 'Ottawa', 'ON', 'K1A 0A6');
    }

    public function testThrowsOnEmptyCity(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'city'");

        new Address('John', '123 Main', null, '   ', 'ON', 'K1A 0A6');
    }

    public function testThrowsOnEmptyPostalCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'postalCode'");

        new Address('John', '123 Main', null, 'Ottawa', 'ON', '');
    }

    public function testIsDomesticWhenCountryEmpty(): void
    {
        $address = new Address('John', '123 Main', null, 'Ottawa', 'ON', 'K1A 0A6');
        $this->assertTrue($address->isDomestic());
    }

    public function testIsDomesticWhenCountryIsCanada(): void
    {
        $address = new Address('John', '123 Main', null, 'Ottawa', 'ON', 'K1A 0A6', 'Canada');
        $this->assertTrue($address->isDomestic());
        
        $address2 = new Address('John', '123 Main', null, 'Ottawa', 'ON', 'K1A 0A6', 'CANADA');
        $this->assertTrue($address2->isDomestic());
    }

    public function testIsNotDomesticForInternational(): void
    {
        $address = new Address('John', '123 Main', null, 'New York', 'NY', '10001', 'United States');
        $this->assertFalse($address->isDomestic());
    }

    public function testIsCanadianPostalCode(): void
    {
        $address = new Address('John', '123 Main', null, 'Ottawa', 'ON', 'K1A 0A6');
        $this->assertTrue($address->isCanadianPostalCode());

        $noSpace = new Address('John', '123 Main', null, 'Ottawa', 'ON', 'K1A0A6');
        $this->assertTrue($noSpace->isCanadianPostalCode());
    }

    public function testIsNotCanadianPostalCodeForUs(): void
    {
        $address = new Address('John', '123 Main', null, 'New York', 'NY', '10001', 'US');
        $this->assertFalse($address->isCanadianPostalCode());
    }

    public function testToArray(): void
    {
        $address = new Address('John', '123 Main', 'Unit 2', 'Ottawa', 'ON', 'K1A 0A6', 'Canada');
        $expected = [
            'name'         => 'John',
            'addressLine1' => '123 Main',
            'addressLine2' => 'Unit 2',
            'city'         => 'Ottawa',
            'province'     => 'ON',
            'postalCode'   => 'K1A 0A6',
            'country'      => 'Canada',
        ];

        $this->assertSame($expected, $address->toArray());
    }

    public function testFromArrayRoundTrip(): void
    {
        $data = [
            'name'         => 'John',
            'addressLine1' => '123 Main',
            'addressLine2' => 'Unit 2',
            'city'         => 'Ottawa',
            'province'     => 'ON',
            'postalCode'   => 'K1A 0A6',
            'country'      => 'Canada',
        ];

        $address = Address::fromArray($data);
        $this->assertSame($data, $address->toArray());
    }

    public function testFromArrayMissingRequired(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Address::fromArray(['name' => 'John']); // missing other fields
    }

    public function testFromArrayWithDefaults(): void
    {
        $data = [
            'name'         => 'John',
            'addressLine1' => '123 Main',
            'city'         => 'Ottawa',
            'province'     => 'ON',
            'postalCode'   => 'K1A 0A6',
        ];

        $address = Address::fromArray($data);
        $this->assertNull($address->getAddressLine2());
        $this->assertSame('', $address->getCountry());
    }
}
