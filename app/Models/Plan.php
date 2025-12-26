<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'number_of_months',
        'image',
        'price',
    ];


    protected $casts = [
        'price' => 'double',
        'number_of_months' => 'integer',
    ];


    public function items(): HasMany
    {
        return $this->hasMany(PlanItem::class, 'plan_id');
    }

    public function promoCode()
    {
        return $this->belongsToMany(PromoCode::class, 'promo_code_plan');
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? url('storage/'.$this->image) : null;
    }
}
