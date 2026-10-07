<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case CollegeAdmin = 'college_admin';

    /**
     * Roles with full administrative access to the panel.
     *
     * @return array<int, self>
     */
    public static function administrative(): array
    {
        return [self::SuperAdmin, self::CollegeAdmin];
    }
}
