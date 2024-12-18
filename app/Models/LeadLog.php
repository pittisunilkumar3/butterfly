<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;

class LeadLog extends BaseModel
{
    protected $table = 'lead_logs';

    protected $default = ['xid'];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $hidden = ['id', 'company_id', 'lead_id', 'user_id'];

    protected $appends = ['xid', 'x_company_id', 'x_lead_id', 'x_user_id', 'x_created_by_id'];

    protected $filterable = ['log_type', 'lead_id', 'campaign_id', 'user_id'];

    protected $dates = ['date_time'];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
        'getXLeadIdAttribute' => 'lead_id',
        'getXUserIdAttribute' => 'user_id',
        'getXCreatedByIdAttribute' => 'created_by_id',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'lead_id' => Hash::class . ':hash',
        'user_id' => Hash::class . ':hash',
        'created_by_id' => Hash::class . ':hash',
        'time_taken' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withoutGlobalScopes();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id', 'id')->withoutGlobalScopes();
    }
}
