<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question',
        'answer',
        'created_by',
        'updated_by'
    ];

    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_at',
        'created_at',
        'updated_at',
        'status',
        'media'
    ];

    public static $rules = [];

    public function setQuestionAttribute($value)
    {
        $this->attributes['question'] = strip_tags($value);
    }
    public function getQuestionAttribute($value)
    {
        return ucfirst($value);
    }
}
