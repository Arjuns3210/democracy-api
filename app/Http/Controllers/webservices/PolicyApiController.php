<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\PolicyResource;
use App\Models\GeneralSetting;
use App\Repositories\webservices\PolicyRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PolicyApiController extends AppBaseController
{
    /** @var  PolicyRepository */
    private $policyRepository;

    public function __construct(PolicyRepository $PolicyRepo)
    {
        $this->policyRepository = $PolicyRepo;
    }
    /**
     * Display the specified resource.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function show(Request $request)
    {
        try {
            $type = $request->input('type');
            $policy = $this->policyRepository->findWithoutFail($type);
            if($type == 'about'){
                $policy['default_image'] = config('global.image_base_url').'/backend/default_image/logo.png';
            }
            return $this->sendResponse(new PolicyResource($policy));
        } catch (\Exception $e) {
            \Log::info(json_encode($e->getMessage()));

            return $this->sendError(trans('auth.something_went_wrong'), 500);
        }
    }

}
