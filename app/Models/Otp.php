<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mobile_no',
        'mobile_no_with_code',
        'otp_code',
        'expiry_time',
        'workflow',
        'verify_count',
        'otp_verified'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at'
    ];

    public $casts = [
        'mobile_no'           => 'string',
        'mobile_no_with_code' => 'string',
        'otp_code'            => 'string',
        'expiry_time'         => 'datetime',
        'workflow'            => 'string',
        'verify_count'        => 'integer'
    ];
}
