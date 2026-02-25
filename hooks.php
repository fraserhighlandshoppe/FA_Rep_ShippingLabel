<?php
/**
 * FA Module Hooks
 *
 * Registers the Shipping Label module with FrontAccounting.
 * Extends FA's hooks class to add menu entries and report registration.
 *
 * @package KsFraser\FaShippingLabel
 */

define('SS_SHIPLABEL', 110); // Security section for shipping labels

/**
 * Module hooks for FA_Rep_ShippingLabel.
 *
 * Integrates the module into FA's menu system and registers
 * the shipping label report and output plugins.
 */
class hooks_rep_shipping_label extends hooks
{
    /** @var string Module name */
    public $module_name = 'rep_shipping_label';

    /**
     * Install any tables or initial data.
     *
     * Currently no database tables needed — the module reads
     * existing FA data.
     *
     * @return bool
     */
    public function install_tabs($app)
    {
        return true;
    }

    /**
     * Install extension checks.
     *
     * @return bool
     */
    public function install_options($app)
    {
        return true;
    }

    /**
     * Add module menu entries.
     *
     * @param array $apps Application menu structure
     *
     * @return array|null
     */
    public function install_access()
    {
        $security_sections = array(
            SS_SHIPLABEL => _('Shipping Labels'),
        );
        return $security_sections;
    }

    /**
     * Register report in FA's reporting system.
     */
    public function activate_extension($company, $check_only = false)
    {
        if ($check_only) {
            return true;
        }

        // Attempt to run composer install on activation
        $module_path = dirname(__FILE__);
        if (file_exists($module_path . '/composer.json')) {
            $current_dir = getcwd();
            chdir($module_path);
            
            // Try to find composer
            $composer = 'composer';
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $composer = 'composer.phar'; // Fallback for some windows envs if composer not in path
                if (!file_exists($composer)) $composer = 'composer';
            }

            // Run composer install --no-dev to minimize footprint on Prod/UAT
            // Using shell_exec as it's common in FA environments for extension management
            @shell_exec($composer . ' install --no-dev --no-interaction --optimize-autoloader 2>&1');
            
            chdir($current_dir);
        }

        return true;
    }

    /**
     * Register the shipping label report.
     */
    public function replist()
    {
        return array(
            'shipping_labels' => array(
                'name'    => _('Shipping / Mailing Labels'),
                'access'  => SS_SHIPLABEL,
                'file'    => 'shipping_labels', // refers to reporting/shipping_labels.php
                'params'  => array(),
            ),
        );
    }
}
