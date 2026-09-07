<?php

namespace App\Enums;

enum JobStatus: string
{
    case RECEIVED    = 'received';
    case IN_PROGRESS = 'in_progress';
    case READY       = 'ready';
    case COLLECTED   = 'collected';
    case PICKED_UP   = 'picked_up';
    case COMPLETED   = 'completed';
    case CANCELLED   = 'cancelled';

    /**
     * Human-readable label for UI badges.
     */
    public function label(): string
    {
        return match ($this) {
            self::RECEIVED    => 'Received',
            self::IN_PROGRESS => 'In Progress (Washing)',
            self::READY       => 'Ready for Pickup',
            self::COLLECTED   => 'Collected',
            self::PICKED_UP   => 'Picked Up',
            self::COMPLETED   => 'Completed',
            self::CANCELLED   => 'Cancelled',
        };
    }

    /**
     * Tailwind badge color classes.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::RECEIVED                      => 'bg-blue-50 text-blue-700 border-blue-200',
            self::IN_PROGRESS                   => 'bg-amber-50 text-amber-700 border-amber-200',
            self::READY                         => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::COLLECTED, self::PICKED_UP,
            self::COMPLETED                     => 'bg-slate-100 text-slate-700 border-slate-200',
            self::CANCELLED                     => 'bg-rose-50 text-rose-700 border-rose-200',
        };
    }
}
