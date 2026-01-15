<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSectionProvider extends Model
{
    use HasFactory;
    protected $table='home_section_providers';
    protected $fillable = [
        'title',
    ];

    public function providers() : BelongsToMany {
        return $this->belongsToMany(ServiceProvider::class, 'home_section_providers');
    }
}
