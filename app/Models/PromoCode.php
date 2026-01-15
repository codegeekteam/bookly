<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'maximum_redeems', 'discount_type_id', 'discount_amount', 'maximum_discount', 'start_date', 'end_date', 'is_for_services', 'is_for_plans', 'count_of_redeems'];

    public function discountType()
    {
        return $this->belongsTo(DiscountType::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'promo_code_service');
    }

    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'promo_code_plan');
    }
}
