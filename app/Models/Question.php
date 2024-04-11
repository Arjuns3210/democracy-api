<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'question',
        'category_id',
        'level',
        'type',
        'question_code',
        'max_allowed_answer',
        'status',
        'created_by',
        'updated_by'
    ] ;
    
    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_at',
        'created_at',
        'updated_at'
    ];
    public static $rules = [];

    public function option(){
        return $this->hasMany(QuestionOption::class, 'question_id');
    }
    public function answer(){
        return $this->hasMany(Answer::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function contestQuestion()
    {
        return $this->hasMany(ContestQuestion::class);
    }

    public function setQuestionAttribute($value)
    {
        $this->attributes['question'] = strip_tags($value);
    }

    public function getQuestionAttribute($value)
    {
        return ucfirst($value);
    }

}
