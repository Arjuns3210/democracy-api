<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Session;
use App\Repositories\webservices\StartupRepository;

class StartupApiController extends AppBaseController
{
     /** @var  StartupRepository */
    private $startupRepository;

    public function __construct(StartupRepository $startupRepo)
    {
        $this->startupRepository = $startupRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = Session::get('userId');
        $data = $this->startupRepository->getByRequest($user_id);
        return $this->sendResponse($data, trans('auth.data_fetched'));
    }
}
