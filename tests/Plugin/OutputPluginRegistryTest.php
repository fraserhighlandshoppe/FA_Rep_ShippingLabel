<?php
/**
 * OutputPluginRegistry Tests
 *
 * @package KsFraser\FaShippingLabel\Tests\Plugin
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Tests\Plugin;

use KsFraser\FaShippingLabel\Label\LabelType;
use KsFraser\FaShippingLabel\Plugin\OutputPluginInterface;
use KsFraser\FaShippingLabel\Plugin\OutputPluginRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OutputPluginRegistryTest extends TestCase
{
    /** @var OutputPluginRegistry */
    private $registry;

    protected function setUp(): void
    {
        $this->registry = new OutputPluginRegistry();
    }

    public function testRegisterAndGet(): void
    {
        $plugin = $this->createMockPlugin('test_plugin', 'Test Plugin');
        $this->registry->register($plugin);

        $retrieved = $this->registry->get('test_plugin');
        $this->assertSame($plugin, $retrieved);
    }

    public function testRegisterDuplicateThrows(): void
    {
        $plugin1 = $this->createMockPlugin('dupe', 'Plugin 1');
        $plugin2 = $this->createMockPlugin('dupe', 'Plugin 2');

        $this->registry->register($plugin1);

        $this->expectException(InvalidArgumentException::class);
        $this->registry->register($plugin2);
    }

    public function testGetNonExistentThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->registry->get('nonexistent');
    }

    public function testHas(): void
    {
        $plugin = $this->createMockPlugin('exists', 'Exists');
        $this->registry->register($plugin);

        $this->assertTrue($this->registry->has('exists'));
        $this->assertFalse($this->registry->has('nope'));
    }

    public function testGetAll(): void
    {
        $p1 = $this->createMockPlugin('a', 'Alpha');
        $p2 = $this->createMockPlugin('b', 'Beta');

        $this->registry->register($p1);
        $this->registry->register($p2);

        $all = $this->registry->getAll();
        $this->assertCount(2, $all);
        $this->assertArrayHasKey('a', $all);
        $this->assertArrayHasKey('b', $all);
    }

    public function testGetDisplayOptions(): void
    {
        $p1 = $this->createMockPlugin('a', 'Alpha');
        $p2 = $this->createMockPlugin('b', 'Beta');

        $this->registry->register($p1);
        $this->registry->register($p2);

        $options = $this->registry->getDisplayOptions();
        $this->assertSame(['a' => 'Alpha', 'b' => 'Beta'], $options);
    }

    /**
     * Create a mock OutputPluginInterface.
     */
    private function createMockPlugin(string $id, string $name): OutputPluginInterface
    {
        $mock = $this->createMock(OutputPluginInterface::class);
        $mock->method('getId')->willReturn($id);
        $mock->method('getName')->willReturn($name);
        return $mock;
    }
}
