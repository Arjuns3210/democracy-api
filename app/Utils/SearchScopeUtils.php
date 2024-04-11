<?php
/*
    *   Developed by : Ankita - Mypcot Infotech
    *   Created On : 01-10-2022
    *   https://www.mypcot.com/
*/

namespace App\Utils;

class SearchScopeUtils
{
    public static function fullSearchQuery($query, $word, $params)
    {
        $orwords = explode('|', $params);
        $query = $query->where(function ($query) use ($word, $orwords) {
            foreach ($orwords as $key) {
                $query->orWhere($key, 'like', '%' . $word . '%');
            }
        });
        return $query;
    }
}
