<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use App\Http\Resources\ContactResource;
use App\Repositories\webservices\ContactRepository;
use App\Http\Requests\CreateContactApiRequest;
use Illuminate\Support\Facades\DB;

class ContactApiController extends AppBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $contactRepository;

    public function __construct(ContactRepository $contactRepo)
    {
        $this->contactRepository = $contactRepo;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateContactApiRequest $request)
    {
        try {
            DB::beginTransaction();
            $input = $request->all();
            $contact = $this->contactRepository->create($input);
            \DB::commit();
            return $this->sendResponse(new ContactResource($contact), trans('auth.contact_created'));
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e->getMessage());
            return $this->sendError(trans('auth.something_went_wrong'), 500);
        }
    }


    /**
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        try {
            $types = ['yt_link', 'insta_link'];
            $settings = GeneralSetting::whereIn('type', $types)->select('value','type')->get();

            $result = [];

            foreach ($settings as $setting) {
                $result[$setting->type] = $setting->value;
            }
            return $this->sendResponse($result, trans('auth.data_fetched'));

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return $this->sendError(trans('auth.something_went_wrong'), 500);
        }
    }

}
