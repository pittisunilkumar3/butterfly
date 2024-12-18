<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Classes\common;
use App\Scopes\CompanyScope;

class Product extends BaseModel
{
    protected $table = 'products';

    protected $default = ['xid', 'name', 'product_type', 'logo', 'price', 'tax_rate', 'tax_label'];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $hidden = ['id'];

    protected $appends = ['xid', 'logo_url',];

    protected $filterable = ['name'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function getLogoUrlAttribute()
    {
        $productLogoPath = Common::getFolderPath('productLogoPath');

        return $this->logo == null ? asset('images/product.png') : Common::getFileUrl($productLogoPath, $this->logo);
    }
}
