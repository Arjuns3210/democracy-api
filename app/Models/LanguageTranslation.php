<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class LanguageTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $fillable = [
        'name'
    ];

    protected $dates = ['deleted_at'];

    protected $hidden = [
        'deleted_at'
    ];

    public function language()
    {
        return $this->belongsTo(App\Models\Language::class);
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strip_tags($value);
    }
}
