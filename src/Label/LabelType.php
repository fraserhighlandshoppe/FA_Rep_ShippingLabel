<?php
/**
 * Label Type Enumeration
 *
 * Defines the types of labels that can be generated:
 * company-only, contact-only, or paired (return + destination).
 *
 * @package KsFraser\FaShippingLabel\Label
 * @see FR-030, FR-031, FR-032
 */

declare(strict_types=1);

namespace KsFraser\FaShippingLabel\Label;

/**
 * Label type enumeration.
 *
 * PHP 7.3 compatible — uses class constants instead of enum.
 */
class LabelType
{
    /** @var string Print only the company's address */
    public const COMPANY_ONLY = 'company_only';

    /** @var string Print only the contact's address */
    public const CONTACT_ONLY = 'contact_only';

    /** @var string Print both return (company) and destination (contact) addresses */
    public const PAIRED = 'paired';

    /** @var string[] All valid label types */
    private const VALID_TYPES = [
        self::COMPANY_ONLY,
        self::CONTACT_ONLY,
        self::PAIRED,
    ];

    /** @var string The selected label type */
    private $type;

    /**
     * Factory method for PAIRED label type.
     *
     * @return self
     */
    public static function paired(): self
    {
        return new self(self::PAIRED);
    }

    /**
     * Factory method for CONTACT_ONLY label type.
     *
     * @return self
     */
    public static function contactOnly(): self
    {
        return new self(self::CONTACT_ONLY);
    }

    /**
     * Factory method for COMPANY_ONLY label type.
     *
     * @return self
     */
    public static function companyOnly(): self
    {
        return new self(self::COMPANY_ONLY);
    }

    /**
     * @param string $type One of the class constants
     *
     * @throws \InvalidArgumentException If type is not valid
     */
    public function __construct(string $type)
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid label type '%s'. Valid types: %s",
                    $type,
                    implode(', ', self::VALID_TYPES)
                )
            );
        }
        $this->type = $type;
    }

    /**
     * Get the label type value.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->type;
    }

    /**
     * Check if this is a company-only label.
     *
     * @return bool
     */
    public function isCompanyOnly(): bool
    {
        return $this->type === self::COMPANY_ONLY;
    }

    /**
     * Check if this is a contact-only label.
     *
     * @return bool
     */
    public function isContactOnly(): bool
    {
        return $this->type === self::CONTACT_ONLY;
    }

    /**
     * Check if this is a paired label.
     *
     * @return bool
     */
    public function isPaired(): bool
    {
        return $this->type === self::PAIRED;
    }

    /**
     * Whether this label type requires a contact address.
     *
     * @return bool
     */
    public function requiresContact(): bool
    {
        return $this->type !== self::COMPANY_ONLY;
    }

    /**
     * Whether this label type includes a return address.
     *
     * @return bool
     */
    public function includesReturnAddress(): bool
    {
        return $this->type === self::COMPANY_ONLY || $this->type === self::PAIRED;
    }

    /**
     * Get all valid label types.
     *
     * @return string[]
     */
    public static function getValidTypes(): array
    {
        return self::VALID_TYPES;
    }

    /**
     * Get human-readable labels for UI display.
     *
     * @return array<string, string>
     */
    public static function getDisplayLabels(): array
    {
        return [
            self::COMPANY_ONLY => 'Company Label',
            self::CONTACT_ONLY => 'Contact Label',
            self::PAIRED       => 'Paired (Envelope)',
        ];
    }
}
