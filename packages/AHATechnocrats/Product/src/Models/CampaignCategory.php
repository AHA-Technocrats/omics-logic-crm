<?php

namespace AHATechnocrats\Product\Models;

use AHATechnocrats\Product\Contracts\CampaignCategory as CampaignCategoryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignCategory extends Model implements CampaignCategoryContract
{
    protected $table = 'campaign_categories';

    protected $fillable = [
        'name',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(ProductProxy::modelClass(), 'category_id');
    }
}
