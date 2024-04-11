<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionOption extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        "question_id",
        "option",
        "status",
        'created_by',
        'updated_by'
    ] ;

    protected $hidden = [
        'status',
        'question_id',
        'created_by',
        'updated_by',
        'deleted_at',
        'created_at',
        'updated_at'
    ];

}
