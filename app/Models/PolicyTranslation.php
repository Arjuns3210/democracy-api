<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $fillable = [
        'content'
    ];


    public function policy()
    {
        return $this->belongsTo(App\Models\Policy::class);
    }
}
