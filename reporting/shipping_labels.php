<?php
/**
 * Shipping / Mailing Labels
 *
 * FA report entry point for generating mailing and shipping labels.
 * This is the UI form + report generation logic.
 *
 * @package KsFraser\FaShippingLabel
 * @see FR-070, FR-071, FR-072, UC-001, UC-002, UC-003, UC-004
 */

$path_to_root = '../../..';

include_once($path_to_root . '/includes/session.inc');
include_once($path_to_root . '/includes/date_functions.inc');
include_once($path_to_root . '/includes/data_checks.inc');

// PSR-4 autoload for module classes
$module_dir = dirname(__DIR__);
if (file_exists($module_dir . '/vendor/autoload.php')) {
    require_once($module_dir . '/vendor/autoload.php');
} else {
    display_error(_('Module dependencies not found. Please activate the module to install them via Composer.'));
    die();
}

use KsFraser\FaShippingLabel\Address\Address;
use KsFraser\FaShippingLabel\Address\AddressFormatterInterface;
use KsFraser\FaShippingLabel\Address\CanadaPostFormatter;
use KsFraser\FaShippingLabel\ContactType\ContactTypeInterface;
use KsFraser\FaShippingLabel\ContactType\CustomerContact;
use KsFraser\FaShippingLabel\ContactType\SupplierContact;
use KsFraser\FaShippingLabel\ContactType\EmployeeContact;
use KsFraser\FaShippingLabel\Label\LabelType;
use KsFraser\FaShippingLabel\Plugin\OutputPluginInterface;
use KsFraser\FaShippingLabel\Plugin\OutputPluginRegistry;
use KsFraser\FaShippingLabel\Plugin\EnvelopePlugin\EnvelopeOutputPlugin;
use KsFraser\FaShippingLabel\Plugin\AveryPlugin\AveryOutputPlugin;
use KsFraser\FaShippingLabel\Plugin\ThermalPlugin\ThermalOutputPlugin;
use KsFraser\FaShippingLabel\Renderer\TcpdfRenderer;

// ---------------------------------------------------------------------------
// Bootstrap: Register contact types and output plugins
// ---------------------------------------------------------------------------

/** @var array<string, ContactTypeInterface> */
$contactTypes = [
    'customer' => new CustomerContact(),
    'supplier' => new SupplierContact(),
    // 'employee' => new EmployeeContact(), // Uncomment when supported
];

$renderer = new TcpdfRenderer();

$pluginRegistry = new OutputPluginRegistry();
$pluginRegistry->register(new EnvelopeOutputPlugin($renderer));
$pluginRegistry->register(new AveryOutputPlugin($renderer));
$pluginRegistry->register(new ThermalOutputPlugin($renderer));

/** @var AddressFormatterInterface */
$formatter = new CanadaPostFormatter();

// ---------------------------------------------------------------------------
// Handle form submission — generate label
// ---------------------------------------------------------------------------

if (isset($_POST['GENERATE'])) {
    $labelTypeValue = $_POST['label_type'] ?? LabelType::PAIRED;
    $contactTypeKey = $_POST['contact_type'] ?? 'customer';
    $contactId      = (int) ($_POST['contact_id'] ?? 0);
    $branchId       = !empty($_POST['branch_id']) ? (int) $_POST['branch_id'] : null;
    $pluginId       = $_POST['output_plugin'] ?? 'envelope';
    $formatId       = $_POST['output_format'] ?? '';

    // Validate label type
    $labelType = new LabelType($labelTypeValue);

    // Get addresses
    $returnLines = [];
    $destLines = [];

    if ($labelType->includesReturnAddress()) {
        $ct = $contactTypes[$contactTypeKey] ?? $contactTypes['customer'];
        $companyAddress = $ct->getCompanyReturnAddress();
        $returnLines = $formatter->formatForLabel($companyAddress);
    }

    if ($labelType->requiresContact()) {
        if ($contactId <= 0) {
            display_error(_('Please select a contact.'));
        } else {
            $ct = $contactTypes[$contactTypeKey] ?? $contactTypes['customer'];
            $contactAddress = $ct->getAddress($contactId, $branchId);
            $destLines = $formatter->formatForLabel($contactAddress);
        }
    }

    // Generate PDF
    if (!empty($returnLines) || !empty($destLines)) {
        $plugin = $pluginRegistry->get($pluginId);

        if (!$plugin->supportsLabelType($labelType)) {
            display_error(sprintf(
                _('The "%s" plugin does not support "%s" label type.'),
                $plugin->getName(),
                $labelType->getValue()
            ));
        } else {
            $pdf = $plugin->render($labelType, $returnLines, $destLines, $formatId);

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="shipping_label.pdf"');
            header('Content-Length: ' . strlen($pdf));
            echo $pdf;
            exit;
        }
    }
}

// ---------------------------------------------------------------------------
// Display form
// ---------------------------------------------------------------------------

$_POST['label_type']    = $_POST['label_type'] ?? LabelType::PAIRED;
$_POST['contact_type']  = $_POST['contact_type'] ?? 'customer';
$_POST['output_plugin'] = $_POST['output_plugin'] ?? 'envelope';

start_form();

start_table(TABLESTYLE2);

// Row 1: Label Type
table_section_title(_('Label Configuration'));

$labelTypeOptions = LabelType::getDisplayLabels();
combo_row(_('Label Type:'), 'label_type', $labelTypeOptions, null, true);

// Row 2: Contact Type (hidden for company-only)
$contactTypeOptions = [];
foreach ($contactTypes as $key => $ct) {
    $contactTypeOptions[$key] = $ct->getTypeName();
}

if ($_POST['label_type'] !== LabelType::COMPANY_ONLY) {
    combo_row(_('Contact Type:'), 'contact_type', $contactTypeOptions, null, true);

    // Row 3: Contact selector
    $selectedType = $_POST['contact_type'] ?? 'customer';
    if (isset($contactTypes[$selectedType])) {
        $contacts = $contactTypes[$selectedType]->listContacts();
        if (!empty($contacts)) {
            combo_row(_('Contact:'), 'contact_id', $contacts, null, true);

            // Row 4: Branch selector (if applicable)
            $contactId = (int) ($_POST['contact_id'] ?? 0);
            if ($contactId > 0) {
                $branches = $contactTypes[$selectedType]->listBranches($contactId);
                if (!empty($branches)) {
                    combo_row(_('Branch / Destination:'), 'branch_id', $branches, null, false);
                }
            }
        } else {
            label_row(_('Contact:'), _('No contacts available for this type.'));
        }
    }
}

// Row 5: Output Plugin
table_section_title(_('Output Settings'));

combo_row(_('Output Plugin:'), 'output_plugin', $pluginRegistry->getDisplayOptions(), null, true);

// Row 6: Output Format (populated from selected plugin)
$selectedPlugin = $_POST['output_plugin'] ?? 'envelope';
if ($pluginRegistry->has($selectedPlugin)) {
    $formats = $pluginRegistry->get($selectedPlugin)->getOutputFormats();
    combo_row(_('Output Format:'), 'output_format', $formats, null, false);
}

end_table(1);

submit_center('GENERATE', _('Generate Label'), true, '', 'default');

end_form();
