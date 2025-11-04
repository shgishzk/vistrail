<?php

namespace App\Enums;

enum SelfLockType: string
{
    case HAS_LOCK = 'has_lock';
    case NO_LOCK = 'no_lock';

    public static function labels(): array
    {
        return [
            self::HAS_LOCK->value => __('Has lock'),
            self::NO_LOCK->value => __('No lock'),
        ];
    }
}
