<?php

namespace App\Enums;

enum Shift: string
{
    case Morning = 'morning';
    case Evening = 'evening';

    public function label(): string
    {
        return match ($this) {
            self::Morning => 'Morning',
            self::Evening => 'Evening',
        };
    }
}
