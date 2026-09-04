<?php

namespace App\Enums;

/**
 * Prevents invalid state jumps (e.g. jumping from received directly to picked_up).
 */
enum JobStatus: string
{
    case RECEIVED = 'received';
    case IN_PROGRESS = 'in_progress';
    case READY = 'ready';
    case PICKED_UP = 'picked_up';
    case CANCELLED = 'cancelled';

    /**
     * Defines which target status is legally reachable from the current status.
     */
    public function canTransitionTo(JobStatus $target): bool
    {
        return match ($this) {
            self::RECEIVED => in_array($target, [self::IN_PROGRESS, self::CANCELLED]),
            self::IN_PROGRESS => in_array($target, [self::READY, self::CANCELLED]),
            self::READY => in_array($target, [self::PICKED_UP]),
            self::PICKED_UP, self::CANCELLED => false, // Terminal states: cannot transition further
        };
    }

    /**
     * UI Label.
     */
    public function label(): string
    {
        return match ($this) {
            self::RECEIVED => 'Received (Tagged)',
            self::IN_PROGRESS => 'In Progress (Washing)',
            self::READY => 'Ready (On Shelf Rack)',
            self::PICKED_UP => 'Picked Up (Collected)',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Tailwind CSS Badge Colors for UI.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::RECEIVED => 'bg-amber-100 text-amber-800 border-amber-300',
            self::IN_PROGRESS => 'bg-blue-100 text-blue-800 border-blue-300 animate-pulse',
            self::READY => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::PICKED_UP => 'bg-gray-100 text-gray-800 border-gray-300',
            self::CANCELLED => 'bg-red-100 text-red-800 border-red-300',
        };
    }
}
