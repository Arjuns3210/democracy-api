<?php

namespace App\Models;

use Carbon\Traits\Timestamp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectAnswer extends Model
{
    use HasFactory, Timestamp;
    protected $fillable = [
        'contest_id',
        'option_id',
        'question_id',
        'answer_count'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
    public function contest()
    {
        return $this->hasMany(Contest::class, 'contest_id');
    }
    public function option()
    {
        return $this->belongsTo(QuestionOption::class, 'option_id');
    }
}
