<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Category extends Model implements HasMedia
{
    use HasFactory , HasTranslations, InteractsWithMedia;

    public $translatable = ['title'];

    public $casts = ['title' => 'json'];

    protected $fillable = ['title', 'is_active'];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'category_service');
    }

    public function CategoryIcon()
    {
        return Attribute::make(
            get: fn ($value) => $this->getFirstMediaUrl('category_images') ? $this->getMedia('category_images')->last()->getUrl() : null,
            set: fn ($value) => $this->addMedia($value)->toMediaCollection('category_images')
        );
    }
}
