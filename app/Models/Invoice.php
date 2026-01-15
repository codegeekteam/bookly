<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'invoice_number',
        'invoice_date',
        'customer_name',
        'customer_email',
        'customer_phone',
        'subtotal',
        'vat_amount',
        'total_amount',
        'items',
        'company_details',
        'pdf_path',
    ];

    protected $casts = [
        'items' => 'array',
        'company_details' => 'array',
        'invoice_date' => 'date',
        'subtotal' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function getPdfUrl(): ?string
    {
        if (!$this->pdf_path) {
            return null;
        }

        return url('storage/' . $this->pdf_path);
    }
}
