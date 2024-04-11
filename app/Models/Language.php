<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Astrotomic\Translatable\Translatable;

class Language extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Translatable;

    protected $fillable = [
        'language_code',
        'status',
        'sequence'
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'status'=>'boolean'
    ];

    public $translatedAttributes = ['name'];

    public const TRANSLATED_BLOCK = [
        'name' => 'input',
    ];
    public function languageTranslations()
    {
        return $this->hasMany(\App\Models\LanguageTranslation::class);
    }
}
