<?php
/**
 * Contact Type Interface
 *
 * Contract for contact data access. Each contact type (Customer, Supplier,
 * Employee) implements this to provide address data from its FA data source.
 *
 * @package KsFraser\FaShippingLabel\ContactType
 * @see FR-020, FR-021, FR-022, FR-023, FR-024, FR-025
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\ContactType;

use KsFraser\FaShippingLabel\Address\Address;

/**
 * Contract for contact type data access.
 *
 * Implementations query FA database tables to retrieve
 * contact and address information.
 */
interface ContactTypeInterface
{
    /**
     * Get the human-readable name for this contact type.
     *
     * @return string e.g., "Customer", "Supplier", "Employee"
     */
    public function getTypeName(): string;

    /**
     * List all available contacts of this type.
     *
     * @return array<int, string> Map of contact ID => display name
     */
    public function listContacts(): array;

    /**
     * List branches/destinations for a specific contact.
     *
     * Returns an empty array if the contact type does not support branches.
     *
     * @param int $contactId The contact's ID
     *
     * @return array<int, string> Map of branch ID => branch name
     */
    public function listBranches(int $contactId): array;

    /**
     * Get the mailing address for a specific contact and branch.
     *
     * @param int      $contactId The contact's ID
     * @param int|null $branchId  Optional branch/destination ID
     *
     * @return Address The contact's address
     */
    public function getAddress(int $contactId, ?int $branchId = null): Address;

    /**
     * Get the company's return (sender) address from FA settings.
     *
     * @return Address The company's address
     */
    public function getCompanyReturnAddress(): Address;
}
