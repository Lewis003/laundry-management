<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_name',
        'tagline',
        'location',
        'phone',
        'email',
        'mpesa_till_or_paybill',
        'receipt_footer',
        'currency',
        'tax_percent',
    ];

    protected $casts = [
        'tax_percent' => 'float',
    ];

    /**
     * Singleton accessor to retrieve current active shop setting.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'shop_name'             => 'Safishwa na Tai',
            'tagline'               => 'Commercial Laundry & Dry Cleaning',
            'location'              => 'Industrial Area, Nairobi',
            'phone'                 => '+254 700 000 001',
            'email'                 => 'info@safishwa.co.ke',
            'mpesa_till_or_paybill' => '542310',
            'receipt_footer'        => 'Thank you for choosing us! Garments not collected within 30 days are subject to disposal/auction.',
            'currency'              => 'KSh',
            'tax_percent'           => 16.00,
        ]);
    }
}

