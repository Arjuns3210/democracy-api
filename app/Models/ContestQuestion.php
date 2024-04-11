<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContestQuestion extends Model
{
    public function contest()
    {
        return $this->hasMany(Contest::class, 'contest_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}

