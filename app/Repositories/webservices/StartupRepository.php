<?php

namespace App\Repositories\webservices;

//Common
use Illuminate\Http\Request;
use App\Models\GeneralSetting;

/**
 * Class StartupRepository
 *
 * @package App\Repositories\webservices
 * @version Sep 23, 2023
 */
class StartupRepository
{
    /**
     * Get Startup Data
     *
     * @param int $user_id
     */
    public function getByRequest($user_id) {
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
        $result['screens'] = $data;
        $result['login_with'] = config('global.login_with');
        $result['mandatory_update'] = false;
        $result['redirection_url'] = null;
        if(!empty(\Request::header('version'))) {
            $version = \Request::header('version');
            if(!empty(\Request::header('platform')) && in_array(\Request::header('platform'),  ['android', 'ios'])) {
                $platform = \Request::header('platform');
            } else {
                $platform = 'android';
            }
            $versions = GeneralSetting::where('type', $platform.'_version')->first()->value;
            $url = GeneralSetting::where('type', $platform.'_url')->first()->value;
            $dbversion = json_decode($versions, true);
            if (!in_array($version, $dbversion)) {
                $result['mandatory_update'] = true;
            }
            $result['redirection_url'] = $url;
        }
        return $result;
    }
}
