<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\ListLanguageApiRequest;
use Illuminate\Http\Request;
use App\Repositories\webservices\LanguageRepository;
use App\Http\Resources\LanguageResource;

class LanguageApiController extends AppBaseController
{
    /** @var  LanguageRepository */
    private $languageRepository;

    public function __construct(LanguageRepository $langRepo)
    {
        $this->languageRepository = $langRepo;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ListLanguageApiRequest $request)
    {
        try {

            $input = $request->all();
            $msg = trans('auth.data_fetched');
            $lang = $this->languageRepository->getByRequest($input);
            if(count($lang['result']) == 0) {
                $msg = trans('auth.language_not_found');
            }
            return $this->sendResponse(LanguageResource::collection($lang['result']), $msg, $lang['total_count']);

        } catch(\Exception $e) {
            \Log::info(json_encode($e->getMessage()));

            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }
}
