<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\FaqResource;
use Illuminate\Http\Request;
use App\Repositories\webservices\FaqRepository;

class FaqApiController extends AppBaseController
{
    /** @var  FaqRepository */
    private $FaqRepository;

    public function __construct(FaqRepository $faqRepo)
    {
        $this->FaqRepository = $faqRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $input = $request->all();
            $msg = trans('auth.data_fetched');
            $faq = $this->FaqRepository->getByRequest($input);
            if(count($faq['result']) == 0) {
                $msg = trans('auth.faq_not_found');
            }
            return $this->sendResponse(FaqResource::collection($faq['result']), $msg, $faq['total_count']);
        } catch(\Exception $e) {
            \Log::info(json_encode($e->getMessage()));

            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }

}
