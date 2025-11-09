<?php

namespace App\Enums;

enum VisitStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case PENDING_REASSIGNMENT = 'pending_reassignment';
    case REASSIGNED = 'reassigned';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';

    public static function labels(): array
    {
        return [
            self::IN_PROGRESS->value => __('訪問中'),
            self::PENDING_REASSIGNMENT->value => __('再割当待機中'),
            self::REASSIGNED->value => __('再割当済み'),
            self::COMPLETED->value => __('完了'),
            self::CANCELED->value => __('未着手で返却'),
        ];
    }

    public static function badgeClasses(): array
    {
        return [
            self::IN_PROGRESS->value => 'text-bg-primary',
            self::PENDING_REASSIGNMENT->value => 'bg-warning',
            self::REASSIGNED->value => 'text-bg-secondary',
            self::COMPLETED->value => 'bg-success',
            self::CANCELED->value => 'text-bg-light',
        ];
    }

    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }

    public static function default(): self
    {
        return self::IN_PROGRESS;
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    public function badgeClass(): string
    {
        return self::badgeClasses()[$this->value];
    }
}
