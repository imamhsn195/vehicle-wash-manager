<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Partner = 'partner';
    case SiteManager = 'site_manager';
    case Staff = 'staff';
    case Accountant = 'accountant';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Partner => 'Partner',
            self::SiteManager => 'Site Manager',
            self::Staff => 'Staff',
            self::Accountant => 'Accountant',
        };
    }
}
