<?php

namespace App\Enums;

enum RoomStatus: string
{
    case UNVISITED = 'unvisited';
    case AT_HOME = 'at_home';
    case NOT_AT_HOME = 'not_at_home';
    case POSTED = 'posted';
    case DO_NOT_CALL = 'do_not_call';
    case WITNESS = 'witness';
    case LANG_CHINESE = 'lang_chinese';
    case LANG_ENGLISH = 'lang_english';
    case LANG_SIGN = 'lang_sign';
    case LANG_NEPALI = 'lang_nepali';
    case LANG_OTHER = 'lang_other';

    public static function labels(): array
    {
        return [
            self::UNVISITED->value => __('Unvisited'),
            self::AT_HOME->value => __('At home'),
            self::NOT_AT_HOME->value => __('Not at home'),
            self::POSTED->value => __('Posted'),
            self::DO_NOT_CALL->value => __('Do not call'),
            self::WITNESS->value => __('Witness'),
            self::LANG_CHINESE->value => __('Chinese'),
            self::LANG_ENGLISH->value => __('English'),
            self::LANG_SIGN->value => __('Sign language'),
            self::LANG_NEPALI->value => __('Nepali'),
            self::LANG_OTHER->value => __('Other language'),
        ];
    }
}
