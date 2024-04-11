<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuggestedQuestionOption extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'suggested_question_id',
        'option',
        'status',
        'created_by',
        'updated_by',
    ];

    public function question()
    {
        return $this->belongsTo(SuggestedQuestion::class, 'suggested_question_id');
    }
}
