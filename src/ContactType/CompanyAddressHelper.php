<?php
/**
 * Company Address Helper
 *
 * Retrieves company address from FA system preferences.
 * Shared by all ContactType implementations for return address.
 *
 * @package KsFraser\FaShippingLabel\ContactType
 * @see FR-023
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\ContactType;

use KsFraser\FaShippingLabel\Address\Address;

/**
 * Helper class to retrieve the company's own address from FA settings.
 */
class CompanyAddressHelper
{
    /**
     * Get the company address from FA system preferences.
     *
     * FA stores company info in sys_prefs: coy_name, coy_no,
     * postal_address, etc.
     *
     * @return Address
     */
    public static function getCompanyAddress(): Address
    {
        // FA global function to get company preferences
        $company = get_company_prefs();

        $name = $company['coy_name'] ?? 'Company';
        $address = $company['postal_address'] ?? '';

        return CustomerContact::parseAddressString($name, $address);
    }
}
