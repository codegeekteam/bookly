<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class GiftCardTheme extends Model implements HasMedia
{
    use HasFactory,InteractsWithMedia, HasTranslations;

    public $translatable = [
        'title',
    ];

    protected $fillable = [
        'title','active'
    ];

    protected $casts = [
        'title' => 'json',
    ];

    public function mainImage(): Attribute
    {
        return Attribute::make(function () {
            return $this->getFirstMediaUrl('main_image') ?? 'https://via.placeholder.com/150';
        });
    }

}
