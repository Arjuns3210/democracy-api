<?php

namespace App\Repositories\webservices;

//Common
use App\Models\Language;
use App\Utils\SearchScopeUtils;
use Illuminate\Http\Request;

/**
 * Class BookRepository
 *
 * @package App\Repositories\webservices
 * @version Sep 25, 2023
 */
class LanguageRepository
{
    /**
     * Get Book Data
     *
     * @param  int  $language_id
     * @return null
     */

   public function getByRequest(array $attributes)
    {
       $language = Language::where('status', '1')
                           ->where('visible_on_app', '1');

       if (!empty($attributes['id'])) {
           $ids = explode(',', $attributes['id']);
           $language->whereIn('id', $ids);
       }

       $language->orderBy('sequence', 'asc');

       $data['total_count'] = $language->count();

       if (isset($attributes['paginate'])) {
           $data['result'] = $language->paginate($attributes['paginate']);
       } else {
           $data['result'] = $language->get();
       }
       return $data;
    }
}
