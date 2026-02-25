<?php
/**
 * Output Plugin Registry
 *
 * Discovers and manages output plugins. Plugins register themselves
 * via the register() method, typically called from hooks.php.
 *
 * @package KsFraser\FaShippingLabel\Plugin
 * @see FR-044
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Plugin;

use InvalidArgumentException;

/**
 * Registry for output format plugins.
 *
 * Manages plugin instances and provides lookup by ID.
 */
class OutputPluginRegistry
{
    /** @var array<string, OutputPluginInterface> Registered plugins by ID */
    private $plugins = [];

    /**
     * Register an output plugin.
     *
     * @param OutputPluginInterface $plugin The plugin to register
     *
     * @throws InvalidArgumentException If plugin ID is already registered
     */
    public function register(OutputPluginInterface $plugin): void
    {
        $id = $plugin->getId();
        if (isset($this->plugins[$id])) {
            throw new InvalidArgumentException(
                sprintf("Output plugin '%s' is already registered.", $id)
            );
        }
        $this->plugins[$id] = $plugin;
    }

    /**
     * Get a plugin by its ID.
     *
     * @param string $id Plugin identifier
     *
     * @return OutputPluginInterface
     * @throws InvalidArgumentException If plugin not found
     */
    public function get(string $id): OutputPluginInterface
    {
        if (!isset($this->plugins[$id])) {
            throw new InvalidArgumentException(
                sprintf(
                    "Output plugin '%s' not found. Available: %s",
                    $id,
                    implode(', ', array_keys($this->plugins))
                )
            );
        }
        return $this->plugins[$id];
    }

    /**
     * Get all registered plugins.
     *
     * @return array<string, OutputPluginInterface>
     */
    public function getAll(): array
    {
        return $this->plugins;
    }

    /**
     * Get plugins as options for UI display.
     *
     * @return array<string, string> Map of plugin ID => display name
     */
    public function getDisplayOptions(): array
    {
        $options = [];
        foreach ($this->plugins as $id => $plugin) {
            $options[$id] = $plugin->getName();
        }
        return $options;
    }

    /**
     * Check if a plugin is registered.
     *
     * @param string $id Plugin identifier
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return isset($this->plugins[$id]);
    }
}
