<?php
/*
    *   Developed by : Ankita - Mypcot Infotech
    *   Created On : 01-10-2022
    *   https://www.mypcot.com/
*/
namespace App\Utils;

class ResponseMessageUtils
{
    public static function sendResponse($result, $message = "", $total = null)
    {
        $data['result'] = $result;
        if(!empty($total)) {
            $data['total_count'] = $total;
        }
        if(!empty(\Request::input('page')) && !empty(\Request::input('paginate')) && !empty($total)) {
            $data['remaining_count'] = 0;
            $page = \Request::input('page');
            $paginate = \Request::input('paginate');
            $displayed = $page*$paginate;
            if(!empty($total) && $total > $displayed) {
                $data['remaining_count'] = $total - $displayed;
            }
        }
        return response()->json(array("success"=>1,"message"=>$message,"data"=>$data),200);
    }

    public static function sendError($error, $code = 200, $success = 0)
    {
        if(empty($code)) {
            $code = 200;
        }
        return response()->json(array("success"=>$success,"message"=>$error),$code);
    }

    public static function sendRefreshToken($data, $error, $code = 200, $success = 0)
    {
        $response['result'] = $data;
        return response()->json(array("success"=>$success,"message"=>$error,"data"=>$response),$code);
    }

    public static function sendSingleResponse($result, $message = "", $total = null)
    {
        $data['list'] = $result;
        if(!empty($total)) {
            $data['total_count'] = $total;
        }
        if(!empty(\Request::input('page')) && !empty(\Request::input('paginate')) && !empty($total)) {
            $data['remaining_count'] = 0;
            $page = \Request::input('page');
            $paginate = \Request::input('paginate');
            $displayed = $page*$paginate;
            if(!empty($total) && $total > $displayed) {
                $data['remaining_count'] = $total - $displayed;
            }
        }
        return response()->json($data)->getOriginalContent();
    }

    public static function sendMergedResponse($result, $message = "", $total = null)
    {
        $data['result'] = $result;
        return response()->json(array("success"=>1,"message"=>$message,"data"=>$data),200);
    }
}
