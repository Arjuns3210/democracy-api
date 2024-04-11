<?php

namespace App\Repositories\webservices;

use App\Models\Faq;
use App\Utils\SearchScopeUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Class FaqRepository
 *
 * @package App\Repositories\webservices
 * @version jan 17, 2023
 */
class FaqRepository
{
    /**
     * Get Faq Data
     *
     * @param  int  $faq_id
     * @return null
     */
    public function findWithoutFail($faq_id)
    {
        try {
            $faq = Faq::find($faq_id);
            return $faq;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getByRequest(array $attributes)
    {
        $faq = Faq::where('status', '1');
        $data['total_count'] = $faq->count();
        if (isset($attributes['paginate'])) {
            $data['result'] = $faq->paginate($attributes['paginate']);
        } else {
            $data['result'] = $faq->get();
        }
        return $data;
    }

}
