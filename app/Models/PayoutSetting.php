<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'holding_period_days',
        'payout_days',
    ];

    protected $casts = [
        'payout_days' => 'array',
        'holding_period_days' => 'integer',
    ];

    /**
     * Get the singleton instance of payout settings
     */
    public static function get()
    {
        return static::first() ?? static::create([
            'holding_period_days' => 7,
            'payout_days' => [1, 3], // Monday and Wednesday
        ]);
    }

    /**
     * Check if a given date is a payout day
     */
    public function isPayoutDay(Carbon $date): bool
    {
        $dayOfWeek = $date->dayOfWeek; // 0 = Sunday, 6 = Saturday
        return in_array($dayOfWeek, $this->payout_days);
    }

    /**
     * Get the next payout date from a given date
     */
    public function getNextPayoutDate(Carbon $from): Carbon
    {
        $date = $from->copy();

        // Look ahead up to 7 days to find next payout day
        for ($i = 0; $i < 7; $i++) {
            if ($this->isPayoutDay($date)) {
                return $date;
            }
            $date->addDay();
        }

        // If no payout day found in next 7 days, return 7 days from now
        return $from->copy()->addDays(7);
    }

    /**
     * Calculate when an amount becomes available for payout
     */
    public function calculateAvailableAt(Carbon $completedAt): Carbon
    {
        return $completedAt->copy()->addDays($this->holding_period_days);
    }
}
