<?php

namespace AHATechnocrats\Product\Models;

use AHATechnocrats\Activity\Models\ActivityProxy;
use AHATechnocrats\Activity\Traits\LogsActivity;
use AHATechnocrats\Attribute\Traits\CustomAttribute;
use AHATechnocrats\Product\Contracts\Product as ProductContract;
use AHATechnocrats\Tag\Models\TagProxy;
use AHATechnocrats\Warehouse\Models\LocationProxy;
use AHATechnocrats\Warehouse\Models\WarehouseProxy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model implements ProductContract
{
    use CustomAttribute, LogsActivity;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleting(function ($product) {
            \DB::table('omics_product_aliases')->where('product_id', $product->id)->delete();
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'sku',
        'description',
        'quantity',
        'price',
        'category',
        'is_active',
        'canonical_product_id',
        'mapping_status',
        'mapping_confidence',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'mapping_confidence' => 'decimal:2',
    ];

    /**
     * Get the product warehouses that owns the product.
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(WarehouseProxy::modelClass(), 'product_inventories');
    }

    /**
     * Get the product locations that owns the product.
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(LocationProxy::modelClass(), 'product_inventories', 'product_id', 'warehouse_location_id');
    }

    /**
     * Get the product inventories that owns the product.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(ProductInventoryProxy::modelClass());
    }

    /**
     * The tags that belong to the Products.
     */
    public function tags()
    {
        return $this->belongsToMany(TagProxy::modelClass(), 'product_tags');
    }

    /**
     * Get the activities.
     */
    public function activities()
    {
        return $this->belongsToMany(ActivityProxy::modelClass(), 'product_activities');
    }
}
