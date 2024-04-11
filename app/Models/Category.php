<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'category_name',
        'status',
        'created_by',
        'updated_by'
    ] ;

    public static $rules = [];

    public function setCategoryNameAttribute($value)
    {
        $this->attributes['category_name'] = strip_tags($value);
    }

    public function getCategoryNameAttribute($value)
    {
        return ucfirst($value);
    }
}
