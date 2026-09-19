<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateEvent extends Model
{
    protected $fillable = ['affiliate_product_id', 'brand_id', 'kind', 'session_id'];

    public function product(): BelongsTo { return $this->belongsTo(AffiliateProduct::class, 'affiliate_product_id'); }
    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); }
}
