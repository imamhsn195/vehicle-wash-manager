<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case MallContract = 'mall_contract';
    case Equipment = 'equipment';
    case EquipmentMaintenance = 'equipment_maintenance';
    case StaffHousing = 'staff_housing';
    case StaffFood = 'staff_food';
    case OfficeRent = 'office_rent';
    case Consumables = 'consumables';
    case Utilities = 'utilities';
    case Insurance = 'insurance';
    case Transport = 'transport';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MallContract => 'Mall Contract',
            self::Equipment => 'Equipment',
            self::EquipmentMaintenance => 'Equipment Maintenance',
            self::StaffHousing => 'Staff Housing',
            self::StaffFood => 'Staff Food',
            self::OfficeRent => 'Office Rent',
            self::Consumables => 'Consumables',
            self::Utilities => 'Utilities',
            self::Insurance => 'Insurance',
            self::Transport => 'Transport',
            self::Other => 'Other',
        };
    }
}
