<?php
/**
 * Module Configuration
 *
 * Default settings for the FA_Rep_ShippingLabel module.
 *
 * @package KsFraser\FaShippingLabel
 */

declare(strict_types=1);

return [
    // Module metadata
    'name'    => 'FA Shipping / Mailing Labels',
    'version' => '1.0.0',
    'author'  => 'KsFraser',

    // Default carrier formatter
    'default_carrier' => 'canada_post',

    // Report ID used in FA reporting system
    'report_id' => 950,

    // Default output plugin
    'default_plugin' => 'envelope',

    // Default label type
    'default_label_type' => 'paired',
];
