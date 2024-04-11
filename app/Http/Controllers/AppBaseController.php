<?php

namespace App\Http\Controllers;
use App\Utils\ResponseMessageUtils;
use App\Utils\MessageUtils;
use App\Utils\ApiUtils;
use Response;
use Session;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class AppBaseController extends Controller
{
    public function sendResponse($result, $message = "", $total = null)
    {
        return ResponseMessageUtils::sendResponse($result, $message, $total);
    }
    public function sendSingleResponse($result, $message = "", $total = null)
    {
        return ResponseMessageUtils::sendSingleResponse($result, $message, $total);
    }
    public function sendMergedResponse($result, $message = "", $total = null)
    {
        return ResponseMessageUtils::sendMergedResponse($result, $message, $total);
    }

    public function sendError($error, $code = 200, $success = 0)
    {
        return ResponseMessageUtils::sendError($error, $code, $success);
    }

    public function sendRefreshToken($data, $error, $code = 200, $success = 0)
    {
        return ResponseMessageUtils::sendRefreshToken($data, $error, $code, $success);
    }

    public function generateRandomString()
    {
        return MessageUtils::generateRandomString();
    }

    

    public function setStartupMeData($user_id) {
        $data = config('global.startup_data');
        
        foreach ($data as $main_key => $main_value) {
            foreach ($data[$main_key] as $inter_key => $inter_value) {
                if(!is_array($inter_value)) {
                    if(!is_bool($inter_value) && $inter_value == ':based_on_login') {
                        if($user_id == 0) {
                            $data[$main_key][$inter_key] = false;
                        } else {
                            $data[$main_key][$inter_key] = true;
                        }
                    }
                    if(!is_bool($inter_value) && $inter_value == ':based_on_logout') {
                        if($user_id == 0) {
                            $data[$main_key][$inter_key] = true;
                        } else {
                            $data[$main_key][$inter_key] = false;
                        }
                    }
                } else {
                    foreach ($data[$main_key][$inter_key] as $key => $value) {
                        if(!is_bool($value) && $value == ':based_on_login') {
                            if($user_id == 0) {
                                $data[$main_key][$inter_key][$key] = false;
                            } else {
                                $data[$main_key][$inter_key][$key] = true;
                            }
                        }
                        if(!is_bool($value) && $value == ':based_on_logout') {
                            if($user_id == 0) {
                                $data[$main_key][$inter_key][$key] = true;
                            } else {
                                $data[$main_key][$inter_key][$key] = false;
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

    public function sendOTPMessagePost($otp, $phone) {

        $smsUrl = config('global.sms_url');
        $smsTemplate = config('global.sms_template');
        $apikey = config('global.sms_api_key');
        $username = config('global.sms_username');
        $template_id = config('global.sms_template_id');
        $sender_id = config('global.sms_sender_id');
        $route = config('global.sms_route');
        \Log::error($smsUrl);
        \Log::error($smsTemplate);
        \Log::error($apikey);
        \Log::error($username);
        \Log::error($template_id);
        \Log::error($sender_id);
        \Log::error($route);

        if (empty($smsUrl) || empty($smsTemplate) || empty($apikey) || empty($username) || empty($template_id) || empty($sender_id) || empty($route)) {
            return false;
        }

        $smsTemplate = str_replace('{#var1#}', $otp, $smsTemplate);
        $smsTemplate = str_replace('{#var2#}', $smsUrl, $smsTemplate);

        $data = array(
            'username'=> $username,
            'apikey'=> $apikey,
            'apirequest'=>'Text',
            'sender'=> $sender_id,
            'route'=> $route,
            'format'=>'JSON',
            'message'=> $smsTemplate,
            'mobile'=> $phone,
            'TemplateID' => $template_id,
        );
        \Log::error($data);

        $uri = 'http://smsao.eweb.co.in/sms-panel/api/http/index.php';

        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);

        $resp = curl_exec($ch);
        $error = curl_error($ch);
        \Log::error($error);
        $err_no_curl = curl_errno($ch);
        \Log::error($err_no_curl);
        curl_close($ch);
        \Log::error($resp);
        return $resp;

    }
    
    public function sendOTPMessage($otp, $phone) {

        $smsUrl = config('global.sms_url');
        $smsTemplate = config('global.sms_template');
        $apikey = config('global.sms_api_key');
        $username = config('global.sms_username');
        $template_id = config('global.sms_template_id');
        $sender_id = config('global.sms_sender_id');
        $route = config('global.sms_route');
        \Log::error($smsUrl);
        \Log::error($smsTemplate);
        \Log::error($apikey);
        \Log::error($username);
        \Log::error($template_id);
        \Log::error($sender_id);
        \Log::error($route);

        if (empty($smsUrl) || empty($smsTemplate) || empty($apikey) || empty($username) || empty($template_id) || empty($sender_id) || empty($route)) {
            return false;
        }

        $smsTemplate = str_replace('{#var1#}', $otp, $smsTemplate);
        $smsTemplate = str_replace('{#var2#}', $smsUrl, $smsTemplate);
        $smsTemplate = urlencode($smsTemplate);

        $data = array(
            'username'=> $username,
            'apikey'=> $apikey,
            'apirequest'=>'Text',
            'sender'=> $sender_id,
            'route'=> $route,
            'format'=>'JSON',
            'message'=> $smsTemplate,
            'mobile'=> $phone,
            'TemplateID' => $template_id,
        );
        \Log::error($data);

        $uri = 'http://smsao.eweb.co.in/sms-panel/api/http/index.php?username=ytgita&apikey=B4A0E-8CFB7&apirequest=Text&sender=SPSAAT&mobile=8796246926&message=YOUR%20OTP%20IS%20%221234%22%20THANK%20YOU%20FOR%20ORDERING%20YATHARTH%20GEETA%20BOOK.%20https://www.yatharthgeeta.com%20SPSAAT&route=OTP&TemplateID=1507163412083510561&format=JSON';
        \Log::error($uri);
        
        $client = new Client();
        
        $response = $client->get($uri);


        $resp = json_decode($response->getBody(), true);
        
        \Log::error($resp);
        return $resp;

    }

}
