<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends BaseModel
{
    use HasFactory;

    protected $table = 'campaigns';

    protected $default = ['xid', 'name'];

    protected $guarded = ['id', 'status', 'started_on', 'completed_on', 'completed_by', 'created_by', 'updated_by', 'last_action_by', 'created_at', 'updated_at'];

    protected $hidden = ['id', 'company_id', 'form_id', 'email_template_id', 'created_by', 'updated_by', 'last_actioner', 'completed_by'];

    protected $appends = ['xid', 'x_company_id', 'x_form_id', 'x_email_template_id', 'x_created_by', 'x_updated_by', 'x_last_action_by', 'x_completed_by', 'upcoming_lead_action'];

    protected $filterable = ['name'];

    protected $hashableGetterFunctions = [
        'getXCompanyIdAttribute' => 'company_id',
        'getXFormIdAttribute' => 'form_id',
        'getXEmailTemplateIdAttribute' => 'email_template_id',
        'getXCreatedByAttribute' => 'created_by',
        'getXUpdatedByAttribute' => 'updated_by',
        'getXLastActionByAttribute' => 'last_action_by',
        'getXCompletedByAttribute' => 'completed_by',
    ];

    protected $casts = [
        'company_id' => Hash::class . ':hash',
        'form_id' => Hash::class . ':hash',
        'email_template_id' => Hash::class . ':hash',
        'created_by' => Hash::class . ':hash',
        'updated_by' => Hash::class . ':hash',
        'last_action_by' => Hash::class . ':hash',
        'completed_by' => Hash::class . ':hash',
        'allow_reference_prefix' => 'integer',
        'detail_fields' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function getUpcomingLeadActionAttribute()
    {
        if ($this->remaining_leads == 0 && $this->total_leads == 0) {
            return 'start_and_new_lead';
        } else if ($this->remaining_leads == 0) {
            return 'new_lead';
        } else if ($this->remaining_leads == $this->total_leads) {
            return 'start';
        } else {
            return 'resume';
        }
    }

    public function campaignUsers()
    {
        return $this->hasMany(CampaignUser::class, 'campaign_id', 'id');
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function emailTemplate()
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function lastActioner()
    {
        return $this->belongsTo(User::class, 'last_action_by', 'id');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by', 'id');
    }
}
