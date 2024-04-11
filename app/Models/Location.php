<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'name',
        'status',
        'created_by',
        'updated_by'
    ] ;

    public static $rules = [];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strip_tags($value);
    }

    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }
}
