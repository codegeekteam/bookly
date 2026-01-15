<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankDetails extends Model
{
    protected $table = 'bank_details';
    protected $fillable = [
        'user_id',
        'bank_name',
        'account_holder_name',
        'iban',
        'swift_code',
        'account_number',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
