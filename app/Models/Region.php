<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Region extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'title',
        'region_id',
    ];

    public $translatable = [
        'title',
    ];

    protected $casts = [
        'title' => 'json',
    ];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
