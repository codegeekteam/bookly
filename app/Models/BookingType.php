<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class BookingType extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'title',
        'slug',
    ];

    public $translatable = [
        'title',
    ];

    public $cast = ['title' => 'array'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
