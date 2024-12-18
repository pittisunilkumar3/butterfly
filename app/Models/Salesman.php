<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Salesman extends User
{
    protected  $table = 'users';

    protected $default = ["xid", "name", "profile_image"];

    protected $guarded = ['id', 'company_id', 'role_id', 'created_at', 'updated_at'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('type', function (Builder $builder) {
            $builder->where('users.user_type', '=', 'salesman');
        });
    }

    public function setUserTypeAttribute($value)
    {
        $this->attributes['user_type'] = 'salesman';
    }
}
