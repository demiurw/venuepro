<?php

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';

    /**
     * Get all values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::PENDING => 'Pending Verification',
        };
    }

    /**
     * Get status description
     */
    public function description(): string
    {
        return match($this) {
            self::ACTIVE => 'User can access the system normally',
            self::INACTIVE => 'User account is disabled',
            self::PENDING => 'User needs to verify their account',
        };
    }

    /**
     * Check if status allows system access
     */
    public function allowsAccess(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if status requires verification
     */
    public function requiresVerification(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Get CSS class for UI display
     */
    public function cssClass(): string
    {
        return match($this) {
            self::ACTIVE => 'text-green-600 bg-green-50',
            self::INACTIVE => 'text-red-600 bg-red-50',
            self::PENDING => 'text-yellow-600 bg-yellow-50',
        };
    }
}