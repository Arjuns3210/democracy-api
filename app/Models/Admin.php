<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'admin_name',
        'nick_name',
        'email',
        'country_id',
        'phone',
        'password',
        'address',
        'role_id',
        'is_head',
        'login_allowed',
        'status',
        'force_pwd_change_flag',
        'pwd_expiry_date',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'admin_name' => 'string',
        'email' => 'string',
        'country_id' => 'integer',
        'language_id' => 'integer',
        'phone' => 'string',
        'address' => 'string',
        'role_id' => 'integer',
        'status' => 'boolean'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [];

    /**
     * uses : To get role of admin staff
     */
    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    public function setAdminNameAttribute($value)
    {
        $this->attributes['admin_name'] = strip_tags($value);
    }

    public function setNickNameAttribute($value)
    {
        $this->attributes['nick_name'] = strip_tags($value);
    }

    public function setAddressAttribute($value)
    {
        $this->attributes['address'] = str_replace("||br||", "<br/>", strip_tags(str_replace("<br/>", "||br||", $value)));
    }
}
