<?php

namespace App\Enums;

enum MachineStatus: string
{
    case AVAILABLE   = 'available';
    case IN_USE      = 'in_use';
    case MAINTENANCE = 'maintenance';
    case RETIRED     = 'retired';

    /**
     * Allowed next status transitions.
     *
     * @return array<self>
     */
    public function allowedNext(): array
    {
        return match ($this) {
            self::AVAILABLE   => [self::IN_USE, self::MAINTENANCE, self::RETIRED],
            self::IN_USE      => [self::AVAILABLE, self::MAINTENANCE],
            self::MAINTENANCE => [self::AVAILABLE, self::RETIRED],
            self::RETIRED     => [self::MAINTENANCE, self::AVAILABLE],
        };
    }

    /**
     * Check if transition to target status is valid.
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedNext(), true);
    }

    /**
     * Human-readable status label.
     */
    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE   => 'Available',
            self::IN_USE      => 'In Use (Running)',
            self::MAINTENANCE => 'Under Maintenance',
            self::RETIRED     => 'Retired / Offline',
        };
    }

    /**
     * Tailwind CSS badge classes.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::AVAILABLE   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::IN_USE      => 'bg-blue-50 text-blue-700 border-blue-200',
            self::MAINTENANCE => 'bg-amber-50 text-amber-700 border-amber-200',
            self::RETIRED     => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }
}

