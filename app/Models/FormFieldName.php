<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;

class FormFieldName extends BaseModel
{
    protected $table = 'form_field_names';

    protected $default = ['xid', 'field_name', 'similar_field_names', 'deletable', 'visible'];

    protected $guarded = ['id', 'deletable', 'created_at', 'updated_at'];

    protected $hidden = ['id'];

    protected $appends = ['xid', 'x_company_id'];

    protected $filterable = ['name', 'visible'];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'similar_field_names' => 'array',
        'visible' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }
}
