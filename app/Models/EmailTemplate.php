<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;

class EmailTemplate extends BaseModel
{
    protected $table = 'email_templates';

    protected $default = ['xid', 'name', 'subject', 'body'];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $hidden = ['id'];

    protected $appends = ['xid', 'x_company_id'];

    protected $filterable = ['name'];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'status' => 'integer',
        'sharable' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }
}
