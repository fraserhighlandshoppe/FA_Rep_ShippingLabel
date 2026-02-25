<?php
/**
 * Employee Contact Type (Stub)
 *
 * Placeholder implementation for future Employee address support.
 * FA does not have a built-in Employee entity with addresses.
 *
 * @package KsFraser\FaShippingLabel\ContactType
 * @see FR-022
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\ContactType;

use KsFraser\FaShippingLabel\Address\Address;
use RuntimeException;

/**
 * Stub contact type for Employees.
 *
 * All data-access methods throw RuntimeException until
 * an Employee data source is available in FA.
 */
class EmployeeContact implements ContactTypeInterface
{
    /** @var string Error message for unsupported operations */
    private const NOT_SUPPORTED_MSG = 'Employee contact type is not yet supported. '
        . 'FA does not have a built-in Employee entity with addresses.';

    /**
     * {@inheritDoc}
     */
    public function getTypeName(): string
    {
        return 'Employee';
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException Always — not yet supported
     */
    public function listContacts(): array
    {
        throw new RuntimeException(self::NOT_SUPPORTED_MSG);
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException Always — not yet supported
     */
    public function listBranches(int $contactId): array
    {
        throw new RuntimeException(self::NOT_SUPPORTED_MSG);
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException Always — not yet supported
     */
    public function getAddress(int $contactId, ?int $branchId = null): Address
    {
        throw new RuntimeException(self::NOT_SUPPORTED_MSG);
    }

    /**
     * {@inheritDoc}
     *
     * Company address IS available even for the Employee stub.
     */
    public function getCompanyReturnAddress(): Address
    {
        return CompanyAddressHelper::getCompanyAddress();
    }
}
