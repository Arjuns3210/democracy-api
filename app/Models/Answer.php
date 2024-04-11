<?php

namespace App\Models;

use Carbon\Traits\Timestamp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory, Timestamp;
    protected $fillable = [
        'user_id',
        'contest_id',
        'question_id',
        'option_id',
        'answer_timing'
    ];
    public $casts = [
        'answer_timing' => 'integer',
    ];
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }
    public function contest()
    {
        return $this->belongsTo(Contest::class, 'contest_id');
    }
    public function option()
    {
        return $this->belongsTo(QuestionOption::class, 'option_id');
    }
}
