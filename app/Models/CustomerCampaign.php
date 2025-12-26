<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class CustomerCampaign extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'title',
        'is_active',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'campaign_service', 'campaign_id', 'service_id')->whereNull('deleted_at');
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(ServiceProvider::class, 'campaign_provider', 'campaign_id', 'provider_id')->whereNull('deleted_at');
    }
}
