<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class FAQ extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'faqs';

    protected $fillable = [
        'question',
        'answer',
    ];

    public $translatable = [
        'question',
        'answer',
    ];

    protected $casts = [
        'question' => 'array',
        'answer' => 'array',
    ];
}
