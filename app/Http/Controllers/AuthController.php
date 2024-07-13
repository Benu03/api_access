<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Libraries\Helpers;
use App\Models\UserModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use App\Models\SessionToken;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPassword;


class AuthController extends Controller
{
    public function login(Request $request)
    {  
        log::info('Begin login User '.$request->username);
        if(isset($request['username']) && isset($request['password'])){

            $data = [ 'username' => $request->username , 'password' => $request->password ];      
            $loginCheckUser = UserModel::LoginCheckValidation($data);
    
                if(isset($loginCheckUser))
                {
                        $moduleCheck = UserModel::GetModuleUser($loginCheckUser->username);
                        $LoginCheckValidation = $loginCheckUser;
                        if(isset($request->access) && isset($request->device_id))
                        {   
                            if($request->access == 'single device')
                            {   
                                if(isset($LoginCheckValidation))
                                {
                                    if($LoginCheckValidation->device_update == false)
                                    {
                                        try 
                                        {
                                            $Device = UserModel::UpdateDeviceIdFirst($username = $loginCheckUser->username,$deviceId = $request->device_id);
                                            $DatainsertSession = [
                                                'session_token' =>  hash('sha256',Str::random(128)),
                                                'username' => $loginCheckUser->username,
                                                'ip' => $request->getClientIp(true),
                                                'agent' => $request->server('HTTP_USER_AGENT'),
                                                'is_login' => true,
                                                'access' => $request->access,
                                                'app_version' => $request->app_version
                                            ];

                                            $loginSession = UserModel::InsertLoginSession($DatainsertSession);
                                        } 
                                        catch (\Exception $e) 
                                        {
                                        Log::channel('slack_sso')->critical($e);
                                        $message = isset($request->language) ? config('message.' . $request->language . '.7') : config('message.en.7');
                                        return response()->json(
                                            [   'status'       =>  400,
                                                'success'   =>  false,
                                                'message'   =>  $message,
                                                'data'      =>  [$e]
                                            ], 400);
                                        }  
                                        
                                        if(isset($request->fcm_token))
                                        {
                                            $FcmController = new FcmController();
                                            $resultfcm = $FcmController->ValidationFcm($username = $loginCheckUser->username, $fcm_token = $request->fcm_token);        
                                        }
                                        
                                        log::info('End login User '.$loginCheckUser->username);
                                        $message = isset($request->language) ? config('message.' . $request->language . '.3') : config('message.en.3');
                                        return response()->json(
                                            [   'status'    =>  200,
                                                'success'   =>  true,
                                                'message'   =>   $message,
                                                'data'      => [
                                                                'session' => UserModel::GetSessionUser($loginCheckUser->username),
                                                                'user_data' => UserModel::GetUserData($data),
                                                                'module' => UserModel::GetModuleUser($loginCheckUser->username),
                                                            ]
                                            ], 200);
                                    }
                                
                                    elseif($LoginCheckValidation->device_id == $request->device_id)
                                    {
                                        $checksession = UserModel::GetSessionRelogin($loginCheckUser->username);                                                                
                                        if(isset($checksession))
                                        {
                                            $until_date = strtotime($checksession->until_date);
                                            $now = time();
                                            $session_interval = env('SESSION_INTERVAL', '7 days'); 
                                            if ($until_date > $now) {
                                                $model = new SessionToken;
                                                $new_until_date = strtotime(date("Y-m-d H:i:s") . ' +' . $session_interval);
                                                $new_until_date_formatted = date("Y-m-d H:i:s", $new_until_date); 
                                            
                                                $model->where('session_token', $checksession->session_token)
                                                      ->whereRaw('access is null')
                                                      ->update([
                                                          'ip' => $request->getClientIp(true),
                                                          'agent' => $request->server('HTTP_USER_AGENT'),
                                                          'is_login' => true,
                                                          'access' => $request->access,
                                                          'until_date' => $new_until_date_formatted, 
                                                          'app_version' => $request->app_version
                                                      ]);
                                            }
                                            else
                                            {                
                                                $DatainsertSession = [
                                                    'session_token' =>  hash('sha256',Str::random(128)),
                                                    'username' => $loginCheckUser->username,
                                                    'ip' => $request->getClientIp(true),
                                                    'agent' => $request->server('HTTP_USER_AGENT'),
                                                    'is_login' => true,
                                                    'access' => $request->access,
                                                    'app_version' => $request->app_version
                                                ];
                
                                                $loginSession = UserModel::InsertLoginSession($DatainsertSession);                
                                            }
                                        }      
                                        else
                                        {
                                            $DatainsertSession = [
                                                'session_token' =>  hash('sha256',Str::random(128)),
                                                'username' => $loginCheckUser->username,
                                                'ip' => $request->getClientIp(true),
                                                'agent' => $request->server('HTTP_USER_AGENT'),
                                                'is_login' => true,
                                                'access' => $request->access,
                                                'app_version' => $request->app_version
                                            ];
                                            $loginSession = UserModel::InsertLoginSession($DatainsertSession);
                                        }

                                        if(isset($request->fcm_token))
                                        {
                                            $FcmController = new FcmController();
                                            $resultfcm = $FcmController->ValidationFcm($username = $loginCheckUser->username, $fcm_token = $request->fcm_token);        
                                        }
                    
                                        log::info('End login User '.$loginCheckUser->username);
                                            $data_device = [
                                                'ios_id' => isset($request['ios_id']) ? $request['ios_id'] : null,
                                                'android_id' => isset($request['android_id']) ? $request['android_id'] : null,
                                                'device_name' => isset($request['device_name']) ? $request['device_name'] : null,
                                                'os_version' => isset($request['os_version']) ? $request['os_version'] : null,
                                                'ram' => isset($request['ram']) ? $request['ram'] : null,
                                                'storage' => isset($request['storage']) ? $request['storage'] : null
                                            ];                                            
                                                $username = $loginCheckUser->username;
                                            if ($data_device['android_id'] !== null && $data_device['device_name'] !== null) {                   
                                                $this->device($data_device, $username);
                                            }
                                    
                                        $message = isset($request->language) ? config('message.' . $request->language . '.3') : config('message.en.3');
                                        return response()->json(
                                            [   'status'    =>  200,
                                                'success'   =>  true,
                                                'message'   =>  $message,
                                                'data'      => [
                                                                'session' => UserModel::GetSessionUser($loginCheckUser->username),
                                                                'user_data' => UserModel::GetUserData($data),
                                                                'module' => UserModel::GetModuleUser($loginCheckUser->username),
                                                            ]
                                            ], 200);

                                    }

                                    elseif($LoginCheckValidation->device_id == null)
                                    {
                                        $checksession = UserModel::GetSessionRelogin($loginCheckUser->username);                                                                
                                        if(isset($checksession))
                                        {
                                            $Device = UserModel::UpdateDeviceIdFirst($username = $loginCheckUser->username,$deviceId = $request->device_id);
                                            $until_date = strtotime($checksession->until_date);
                                            $now = time();

                                            $session_interval = env('SESSION_INTERVAL', '7 days'); 
                                            if ($until_date > $now) {
                                                $model = new SessionToken;
                                                $new_until_date = strtotime(date("Y-m-d H:i:s") . ' +' . $session_interval);
                                                $new_until_date_formatted = date("Y-m-d H:i:s", $new_until_date); 
                                            
                                                $model->where('session_token', $checksession->session_token)
                                                      ->whereRaw('access is null')
                                                      ->update([
                                                          'ip' => $request->getClientIp(true),
                                                          'agent' => $request->server('HTTP_USER_AGENT'),
                                                          'is_login' => true,
                                                          'access' => $request->access,
                                                          'until_date' => $new_until_date_formatted, 
                                                          'app_version' => $request->app_version
                                                      ]);
                                            }
                                            else
                                            {
                
                                                $DatainsertSession = [
                                                    'session_token' =>  hash('sha256',Str::random(128)),
                                                    'username' => $loginCheckUser->username,
                                                    'ip' => $request->getClientIp(true),
                                                    'agent' => $request->server('HTTP_USER_AGENT'),
                                                    'is_login' => true,
                                                    'access' => $request->access,
                                                    'app_version' => $request->app_version
                                                ];
                
                                                $loginSession = UserModel::InsertLoginSession($DatainsertSession);
                
                                            }

                                        }      
                                        else
                                        {

                                            $DatainsertSession = [
                                                'session_token' =>  hash('sha256',Str::random(128)),
                                                'username' => $loginCheckUser->username,
                                                'ip' => $request->getClientIp(true),
                                                'agent' => $request->server('HTTP_USER_AGENT'),
                                                'is_login' => true,
                                                'access' => $request->access,
                                                'app_version' => $request->app_version
                                            ];

                                            $loginSession = UserModel::InsertLoginSession($DatainsertSession);

                                        }
                    
                                        if(isset($request->fcm_token))
                                        {
                                            $FcmController = new FcmController();
                                            $resultfcm = $FcmController->ValidationFcm($username = $loginCheckUser->username, $fcm_token = $request->fcm_token);        
                                        }
                                        
                                        log::info('End login User '.$loginCheckUser->username);
                                            $data_device = [
                                                'ios_id' => isset($request['ios_id']) ? $request['ios_id'] : null,
                                                'android_id' => isset($request['android_id']) ? $request['android_id'] : null,
                                                'device_name' => isset($request['device_name']) ? $request['device_name'] : null,
                                                'os_version' => isset($request['os_version']) ? $request['os_version'] : null,
                                                'ram' => isset($request['ram']) ? $request['ram'] : null,
                                                'storage' => isset($request['storage']) ? $request['storage'] : null
                                            ];
                                            
                                                $username = $loginCheckUser->username;
                                            if ($data_device['android_id'] !== null && $data_device['device_name'] !== null) {                   
                                                $this->device($data_device, $username);
                                            }

                                        $message = isset($request->language) ? config('message.' . $request->language . '.3') : config('message.en.3');
                                        return response()->json(
                                            [   'status'    =>  200,
                                                'success'   =>  true,
                                                'message'   =>  $message,
                                                'data'      => [
                                                                'session' => UserModel::GetSessionUser($loginCheckUser->username),
                                                                'user_data' => UserModel::GetUserData($data),
                                                                'module' => UserModel::GetModuleUser($loginCheckUser->username),
                                                            ]
                                            ], 200);

                                    }

                                    else
                                    {   
                                            log::info('End login User');
                                            $message = isset($request->language) ? config('message.' . $request->language . '.8') : config('message.en.8');
                                            return response()->json(
                                                [   'status'    =>  401,
                                                    'success'   =>  false,
                                                    'message'   =>  $message,
                                                    'data'      =>  []
                                                ], 401);        
                                    }
                                }
                                else
                                {
                                    log::info('End login User '.$loginCheckUser->username);
                                    $message = isset($request->language) ? config('message.' . $request->language . '.6') : config('message.en.6');
                                    return response()->json(
                                        [   'status'    =>  401,
                                            'success'   =>  false,
                                            'message'   =>  $message,
                                            'data'      =>  []
                                        ], 401);
                                }
                            }
                            elseif(($request->access == 'single login'))
                            {
                                $checksession = UserModel::GetSessionRelogin($loginCheckUser->username);           
                                if(isset($LoginCheckValidation))
                                {
                                    if($checksession->is_login == true)
                                    {
                                        $checkSessionSingelLogin = UserModel::SessionSingelLogin($username = $loginCheckUser->username, $access = $request->access);
                                        foreach($checkSessionSingelLogin as $x => $val) 
                                        {
                                            $ssn = json_decode(json_encode($val), true);
                                            $datafcm = [
                                                'token' => $ssn['fcm_token'],
                                                'title'=> 'Singel Login',
                                                'message'=> 'Username '.$ssn['username'].' Sudah Login Di Device Lain'
                                            ];
                                            $FcmController = new FcmController();
                                            $resultfcm = $FcmController->SendMessage($datafcm);    
                                        
                                            $SessionLogin = new SessionToken;
                                            $SessionLogin->where('username', $ssn['username'])->where('access', $request->access)
                                                                ->where('is_login', true)
                                                                ->whereRaw("created_date >= '".date("Y-m-d")."'")
                                                                ->update( ['is_login' => false,
                                                                    'access' => $request->access]
                                                                    );
                                        }

                                        $DatainsertSession = [
                                            'session_token' =>  hash('sha256',Str::random(128)),
                                            'username' => $loginCheckUser->username,
                                            'ip' => $request->getClientIp(true),
                                            'agent' => $request->server('HTTP_USER_AGENT'),
                                            'is_login' => true,
                                            'access' => $request->access,
                                            'fcm_token' => $request->fcm_token,
                                            'app_version' => $request->app_version
                                        ];
        
                                        $loginSession = UserModel::InsertLoginSession($DatainsertSession);     
                                        if(isset($request->fcm_token))
                                        {
                                            $FcmController = new FcmController();
                                            $resultfcm = $FcmController->ValidationFcm($username = $loginCheckUser->username, $fcm_token = $request->fcm_token);        
                                        }
                                        
                                        log::info('End login User '.$loginCheckUser->username);
                                        $data_device = [
                                            'ios_id' => isset($request['ios_id']) ? $request['ios_id'] : null,
                                            'android_id' => isset($request['android_id']) ? $request['android_id'] : null,
                                            'device_name' => isset($request['device_name']) ? $request['device_name'] : null,
                                            'os_version' => isset($request['os_version']) ? $request['os_version'] : null,
                                            'ram' => isset($request['ram']) ? $request['ram'] : null,
                                            'storage' => isset($request['storage']) ? $request['storage'] : null
                                        ];
                                        
                                            $username = $loginCheckUser->username;
                                        if ($data_device['android_id'] !== null && $data_device['device_name'] !== null) {                   
                                            $this->device($data_device, $username);
                                        }
                                        $message = isset($request->language) ? config('message.' . $request->language . '.3') : config('message.en.3');
                                        return response()->json(
                                            [   'status'    =>  200,
                                                'success'   =>  true,
                                                'message'   =>  $message,
                                                'data'      => [
                                                                'session' => UserModel::GetSessionUser($loginCheckUser->username),
                                                                'user_data' => UserModel::GetUserData($data),
                                                                'module' => UserModel::GetModuleUser($loginCheckUser->username),
                                                            ]
                                            ], 200);
                                    }
                                    else
                                    {   
                                        $DatainsertSession = [
                                            'session_token' =>  hash('sha256',Str::random(128)),
                                            'username' => $loginCheckUser->username,
                                            'ip' => $request->getClientIp(true),
                                            'agent' => $request->server('HTTP_USER_AGENT'),
                                            'is_login' => true,
                                            'access' => $request->access,
                                            'fcm_token' => $request->fcm_token,
                                            'app_version' => $request->app_version
                                        ];
        
                                        $loginSession = UserModel::InsertLoginSession($DatainsertSession);                                        
                                        if(isset($request->fcm_token))
                                        {
                                            $FcmController = new FcmController();
                                            $resultfcm = $FcmController->ValidationFcm($username = $loginCheckUser->username, $fcm_token = $request->fcm_token);        
                                        }
                                        
                                        log::info('End login User '.$loginCheckUser->username);
                                          $data_device = [
                                            'ios_id' => isset($request['ios_id']) ? $request['ios_id'] : null,
                                            'android_id' => isset($request['android_id']) ? $request['android_id'] : null,
                                            'device_name' => isset($request['device_name']) ? $request['device_name'] : null,
                                            'os_version' => isset($request['os_version']) ? $request['os_version'] : null,
                                            'ram' => isset($request['ram']) ? $request['ram'] : null,
                                            'storage' => isset($request['storage']) ? $request['storage'] : null
                                        ];
                                        
                                            $username = $loginCheckUser->username;
                                        if ($data_device['android_id'] !== null && $data_device['device_name'] !== null) {                   
                                            $this->device($data_device, $username);
                                        }
                                        $message = isset($request->language) ? config('message.' . $request->language . '.3') : config('message.en.3');
                                        return response()->json(
                                            [   'status'    =>  200,
                                                'success'   =>  true,
                                                'message'   =>  $message,
                                                'data'      => [
                                                                'session' => UserModel::GetSessionUser($loginCheckUser->username),
                                                                'user_data' => UserModel::GetUserData($data),
                                                                'module' => UserModel::GetModuleUser($loginCheckUser->username),
                                                            ]
                                            ], 200);
                                    }
                                }
                                else
                                {
                                    log::info('End login User '.$loginCheckUser->username);
                                    $message = isset($request->language) ? config('message.' . $request->language . '.6') : config('message.en.6');
                                    return response()->json(
                                        [   'status'    =>  401,
                                            'success'   =>  false,
                                            'message'   =>  $message,
                                            'data'      =>  []
                                        ], 401);
                                }                                
                            }
                            else 
                            {
                                log::info('End login User');
                                $message = isset($request->language) ? config('message.' . $request->language . '.9') : config('message.en.9');
                                return response()->json(
                                    [   'status'    =>  401,
                                        'success'   =>  false,
                                        'message'   =>  $message,
                                        'data'      =>  []
                                    ], 401);
                            }
                        }
                        else
                        {
                                    if(isset($LoginCheckValidation))
                                    {                                    
                                        if($LoginCheckValidation->is_confirm == false)
                                        {   
                                            $DatainsertSession = [
                                                'session_token' =>  hash('sha256',Str::random(128)),
                                                'username' => $loginCheckUser->username,
                                                'ip' => $request->getClientIp(true),
                                                'agent' => $request->server('HTTP_USER_AGENT'),
                                                'is_login' => true,
                                                'app_version' => $request->app_version
                                            ];
                
                                            $loginSession = UserModel::InsertLoginSession($DatainsertSession);
                                            $message = isset($request->language) ? config('message.' . $request->language . '.5') : config('message.en.5');
                                            return response()->json(
                                                [   'status'    =>  200,
                                                    'success'   =>  true,
                                                    'message'   => $message,
                                                    'data'      =>  [
                                                        'username' => $loginCheckUser->username,
                                                        'session' => $DatainsertSession['session_token']
                                                    ]
                                                ], 200);

                                        }
                                        $checksession = UserModel::GetSessionRelogin($loginCheckUser->username);
                                       
                                        if(isset($checksession) && $checksession->access == null)
                                        {                                            
                                            $until_date = strtotime($checksession->until_date);
                                            $now = time();
                                            // $diff_in_seconds = $now - $until_date;
                                            // $diff_in_minutes = floor($diff_in_seconds / 60);
                                            $session_interval = env('SESSION_INTERVAL', '7 days'); 
                                            if ($until_date > $now) {
                                                $model = new SessionToken;
                                                $new_until_date = strtotime(date("Y-m-d H:i:s") . ' +' . $session_interval);
                                                $new_until_date_formatted = date("Y-m-d H:i:s", $new_until_date); 
                                            
                                                $model->where('session_token', $checksession->session_token)
                                                      ->whereRaw('access is null')
                                                      ->update([
                                                          'ip' => $request->getClientIp(true),
                                                          'agent' => $request->server('HTTP_USER_AGENT'),
                                                          'is_login' => true,
                                                          'until_date' => $new_until_date_formatted, 
                                                          'app_version' => $request->app_version
                                                      ]);
                                            }
                                            else
                                            {
                                                $DatainsertSession = [
                                                    'session_token' =>  hash('sha256',Str::random(128)),
                                                    'username' => $loginCheckUser->username,
                                                    'ip' => $request->getClientIp(true),
                                                    'agent' => $request->server('HTTP_USER_AGENT'),
                                                    'is_login' => true,
                                                    'app_version' => $request->app_version
                                                ];
                                                $loginSession = UserModel::InsertLoginSession($DatainsertSession);
                                            }
                                        }      
                                        else
                                        {
                                            $DatainsertSession = [
                                                'session_token' =>  hash('sha256',Str::random(128)),
                                                'username' => $loginCheckUser->username,
                                                'ip' => $request->getClientIp(true),
                                                'agent' => $request->server('HTTP_USER_AGENT'),
                                                'is_login' => true,
                                                'app_version' => $request->app_version
                                            ];
                                            $loginSession = UserModel::InsertLoginSession($DatainsertSession);
                                        }
                    
                                        if(isset($request->fcm_token)){
                                            $FcmController = new FcmController();
                                            $resultfcm = $FcmController->ValidationFcm($username = $loginCheckUser->username, $fcm_token = $request->fcm_token);        
                                        }
                                        
                                        log::info('End login User '.$loginCheckUser->username);
                                          $data_device = [
                                            'ios_id' => isset($request['ios_id']) ? $request['ios_id'] : null,
                                            'android_id' => isset($request['android_id']) ? $request['android_id'] : null,
                                            'device_name' => isset($request['device_name']) ? $request['device_name'] : null,
                                            'os_version' => isset($request['os_version']) ? $request['os_version'] : null,
                                            'ram' => isset($request['ram']) ? $request['ram'] : null,
                                            'storage' => isset($request['storage']) ? $request['storage'] : null
                                        ];
                                        
                                            $username = $loginCheckUser->username;
                                        if ($data_device['android_id'] !== null && $data_device['device_name'] !== null) {                   
                                            $this->device($data_device, $username);
                                        }
                     
                                        $message = isset($request->language) ? config('message.' . $request->language . '.3') : config('message.en.3');
                                        return response()->json(
                                            [   'status'    =>  200,
                                                'success'   =>  true,
                                                'message'   =>  $message,
                                                'data'      => [
                                                                'session' => UserModel::GetSessionUser($loginCheckUser->username),
                                                                'user_data' => UserModel::GetUserData($data),
                                                                'module' => UserModel::GetModuleUser($loginCheckUser->username),
                                                            ]
                                            ], 200);
                                        
                                    }
                                    else
                                    {
                                        log::info('End login User '.$loginCheckUser->username);
                                        $message = isset($request->language) ? config('message.' . $request->language . '.6') : config('message.en.6');

                                        return response()->json(
                                            [   'status'    =>  401,
                                                'success'   =>  false,
                                                'message'   =>  $message,
                                                'data'      =>  []
                                            ], 401);
                                    }
                        }
                }
                else
                {
                    log::info('End login User '.$request['username']);
                    $message = isset($request->language) ? config('message.' . $request->language . '.0') : config('message.en.0');
                    return response()->json(
                        [   'status'    =>  401,
                            'success'   =>  false,
                            'message'   =>  $message,
                            'data'      =>  []
                        ], 401);  
                }
        }
        else
        {
            log::info('End login User');
            $message = isset($request->language) ? config('message.' . $request->language . '.10') : config('message.en.10');
            return response()->json(
                [   'status'    =>  401,
                    'success'   =>  false,
                    'message'   =>  $message ,
                    'data'      =>  []
                ], 401);
        }
    }

    public function device($data_device,$username)
    {       
        $currentDateTime = date('Y-m-d H:i:s');
        try {
            DB::connection('sso')->beginTransaction();
    
            $checkDevice = DB::connection('sso')->table('auth.auth_device_users')
                ->where('username', $username)
                ->where('device_name', $data_device['device_name'])
                ->where('android_id', $data_device['android_id'])
                ->where('is_active', true)
                ->first();
    
            if ($checkDevice) {              
                DB::connection('sso')->table('auth.auth_device_users')
                    ->where('username', $username)
                    ->where('device_name', $checkDevice->device_name)
                    ->where('android_id', $checkDevice->android_id)
                    ->update([
                        'os_version' => $data_device['os_version'],
                        'ram' => $data_device['ram'],
                        'storage' => $data_device['storage'],
                        'created_date' => $currentDateTime,
                    ]);
            } else {              
                DB::connection('sso')->table('auth.auth_device_users')
                    ->where('username', $username)
                    ->update(['is_active' => false]);   
                 $dataInsert = [
                    'username' => $username,
                    'ios_id' => $data_device['ios_id'],
                    'android_id' => $data_device['android_id'],
                    'device_name' => $data_device['device_name'],
                    'os_version' => $data_device['os_version'],
                    'ram' => $data_device['ram'],
                    'storage' => $data_device['storage'],
                    'is_active' => true,
                    'created_date' => $currentDateTime,
                ];    
                DB::connection('sso')->table('auth.auth_device_users')->insert($dataInsert);
            }
    
            DB::connection('sso')->commit();    
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Device information updated successfully.',
            ]);
        } catch (\Exception $e) {
            DB::connection('sso')->rollBack();    
            Log::channel('slack')->critical($e);
            $message = isset($request->language) ? config('message.' . $request->language . '.26') : config('message.en.26');    
            return response()->json([
                'status' => 400,
                'success' => false,
                'message' => $message,
                'data' => [$e],
            ], 400);
        }
    }

    public function logout(Request $request)
    {
        log::info('Begin login User '.$request->username);
        if(isset($request['username']) && isset($request['session_token'])){
           $data = [ 'username' => $request->username , 'session_token' => $request->session_token ]; 
            $loginCheckUser = UserModel::LoginCheckUser($data);
            if(isset($loginCheckUser))
            {
                UserModel::SessionLogout($data);                  
                $message = isset($request->language) ? config('message.' . $request->language . '.2') : config('message.en.2');
                return response()->json(
                    [   'status'    =>  200,
                        'success'   =>  true,
                        'message'   =>  $message,
                        'data'      =>  []
                    ], 200);
            }
            else 
            {
                log::info('End login User '.$request->username);
                $message = isset($request->language) ? config('message.' . $request->language . '.0') : config('message.en.0');
                return response()->json(
                    [   'status'    =>  401,
                        'success'   =>  false,
                        'message'   =>  $message,
                        'data'      =>  []
                    ], 401);
            }
        }
        else
        {   
            log::info('End login User '.$request->username);
            $message = isset($request->language) ? config('message.' . $request->language . '.1') : config('message.en.1');
            return response()->json(
                [   'status'    =>  401,
                    'success'   =>  false,
                    'message'   =>   $message,
                    'data'      =>  []
                ], 401);        
        }
    }

    public function forgot(Request $request)
    {
        Log::info('Begin Forgot Password');
        if(empty($request['email'])){        
            $message = isset($request->language) ? config('message.' . $request->language . '.1') : config('message.en.1');
          Log::error('Param Not Avaliable - End Forgot Password');
          return response()->json([
                'status'    =>  401,
                'success'   =>  false,
                'message'   =>  $message,
                'data'      =>  []
            ], 400);
        }
        else{
  
          Log::info($request['email']);
          $data = [ 'email' => $request->email ];
          $loginCheckUser = UserModel::forgotCheckUser($data);  
         
            if($loginCheckUser == null)
            {
                Log::error('No Account Match - End Forgot Password');
                $message = isset($request->language) ? config('message.' . $request->language . '.12') : config('message.en.12');
                return response()->json([
                    'status'    =>  401,
                    'success'   =>  false,
                    'message'   =>  $message,
                    'data'      =>  []
                ], 400);
    
            }
            else
            {
                $otp = strval(mt_rand(1111,9999));
                        // if(isset($request->reset_by))
                        // {
                        //     // if($request->reset_by == 'WA')
                        //     // {
                        //     //     $PersonalData =  UserModel::CheckUserForgot($email = $request->email);
                        //     //     $message = '*MOBILE APP*'. PHP_EOL . PHP_EOL .
                        //     //             '*Kode OTP* : ' . $otp . PHP_EOL . PHP_EOL .
                        //     //             '*Note* : Mohon Untuk Tidak memberikan Kode OTP Kepada Orang Lain, Jika Ada Issue Terkait hubungi HELPDESK';

                        //     //     $dataWa = [
                        //     //         'system'      =>  'SSO SERVICE',
                        //     //         'subject'    =>  'Forgot Password',
                        //     //         'phone'        =>  $PersonalData->wa_number,
                        //     //         'wa_type'          =>  'PERSONAL',
                        //     //         'message'          =>  $message  ];
                        //     //         try 
                        //     //         {
                        //     //             $this->sendWa($dataWa);
                        //     //             Log::info('Check Wa - End Forgot Password');
                                      
               
                        //     //             $message = isset($request->language) ? config('message.' . $request->language . '.11') : config('message.en.11');
                        //     //             return response()->json([
                        //     //                     'status'    =>  200,
                        //     //                     'success'   =>  true,
                        //     //                     'message'   =>  $message,
                        //     //                     'data'      =>  [
                        //     //                         'wa_number'   => $PersonalData->wa_number,
                        //     //                         'otp'     => $otp]], 200);
                        //     //         } 
                        //     //         catch (\Throwable $th) 
                        //     //         {
                        //     //             Log::error('Check Wa - End Forgot Password');
                        //     //             Log::channel('slack_sso')->critical($th);
                        //     //             $message = isset($request->language) ? config('message.' . $request->language . '.26') : config('message.en.26');

                        //     //             return response()->json([
                        //     //                     'status'    =>  500,
                        //     //                     'success'   =>  false,
                        //     //                     'message'   =>  $message,
                        //     //                     'data'      =>  []], 500);
                        //     //         }
                        //     // }
                        //     // else
                        //     // {
                        //         $datamail = [
                        //                 'system'      =>  'Puninar System',
                        //                 'function'    =>  'Forgot Password',
                        //                 'from'        =>  'no-reply@puninar.com',
                        //                 'to'          =>   $loginCheckUser->email,
                        //                 'cc'          =>  null,
                        //                 'bcc'         =>  null,
                        //                 'subject'     =>  'Puninar System Forgot Password',
                        //                 'body'        =>'<p><span>Hi</span><br /><br /><span>Your OTP Code = <b>'.$otp.'</b></span><br />
                        //                                 <br/><span>Please input the code into Puninar Application</span><br />
                        //                                 <br/><span>Regards,</span><br /><span>Admin IT Puninar</span><br /><strong>PUNINAR LOGISTICS</strong><br/>
                        //                                 </p>',
                        //                 'attachment'         =>  "N",
                        //                 'attachment_base64'  => "base64"];
                        //         try 
                        //         {
                        //             $this->sendmail($datamail);
                        //             Log::info('Check Email - End Forgot Password');                   
                        //             $message = isset($request->language) ? config('message.' . $request->language . '.13') : config('message.en.13');
                        //             return response()->json([
                        //                     'status'    =>  200,
                        //                     'success'   =>  true,
                        //                     'message'   =>  $message,
                        //                     'data'      =>  [
                        //                         'email'   => strtolower($request['email']),
                        //                         'otp'     => $otp]], 200);
                        //         } 
                        //         catch (\Throwable $th) 
                        //         {
                        //             Log::error('Check Email - End Forgot Password');
                        //             Log::channel('slack')->critical($th);
                        //             $message = isset($request->language) ? config('message.' . $request->language . '.26') : config('message.en.26');
                        //             return response()->json([
                        //                     'status'    =>  500,
                        //                     'success'   =>  false,
                        //                     'message'   =>  $message,
                        //                     'data'      =>  []], 500);
                        //         }
                        //     // }
                        // }
                        // else
                        // {   

                            $datamail = [
                                        'username'          =>  $loginCheckUser->username,
                                        'email'          =>  $loginCheckUser->username,
                                        'fullname'         => $loginCheckUser->fullname,
                                        'otp'         => $otp                                    
                                    ];

                            
                            try 
                            {
                                Mail::to($loginCheckUser->email)->send(new ForgotPassword($datamail));
                                $message = isset($request->language) ? config('message.' . $request->language . '.13') : config('message.en.13');
                                return response()->json([
                                        'status'    =>  200,
                                        'success'   =>  true,
                                        'message'   =>  $message,
                                        'data'      =>  [
                                            'email'   => strtolower($request['email']),
                                            'otp'     => $otp]], 200);
                            }
                            catch (\Throwable $th) 
                            {
                                Log::error('Check Email - End Forgot Password');
                                Log::channel('slack')->critical($th);
                                $message = isset($request->language) ? config('message.' . $request->language . '.26') : config('message.en.26');
                                return response()->json([
                                        'status'    =>  500,
                                        'success'   =>  false,
                                        'message'   =>  $message,
                                        'data'      =>  []], 500);
                            }



                            //     $datamail = [
                            //         'system'      =>  'Internal System',
                            //         'function'    =>  'Forgot Password',
                            //         'from'        =>  'no-reply@puninar.com',
                            //         'to'          =>   $loginCheckUser->email,
                            //         'cc'          =>  null,
                            //         'bcc'         =>  null,
                            //         'subject'     =>  'TS3 System Forgot Password',
                            //         'body'        =>'<p><span>Hi</span><br /><br /><span>Your OTP Code = <b>'.$otp.'</b></span><br />
                            //                         <br/><span>Please input the code into Puninar Application</span><br />
                            //                         <br /><span>Regards,</span><br /><span>Admin IT TS3</span><br /><strong>PUNINAR LOGISTICS</strong><br/>
                            //                         </p>',
                            //         'attachment'         =>  "N",
                            //         'attachment_base64'  => "base64"];
                            // try 
                            // {
                            //     $this->sendmail($datamail);
                            //     Log::info('Check Email - End Forgot Password');
               
                            //     $message = isset($request->language) ? config('message.' . $request->language . '.13') : config('message.en.13');

                            //     return response()->json([
                            //             'status'    =>  200,
                            //             'success'   =>  true,
                            //             'message'   =>  $message,
                            //             'data'      =>  [
                            //                 'email'   => strtolower($request['email']),
                            //                 'otp'     => $otp]], 200);
                            // } 
                            // catch (\Throwable $th) 
                            // {
                            //     Log::error('Check Email - End Forgot Password');
                            //     Log::channel('slack')->critical($th);
                            //     $message = isset($request->language) ? config('message.' . $request->language . '.26') : config('message.en.26');
                            //     return response()->json([
                            //             'status'    =>  500,
                            //             'success'   =>  false,
                            //             'message'   =>  $message,
                            //             'data'      =>  []], 500);
                            // }
                        // }



            } 
        }          
    }
  
    public function sendmail($datamail)
    {
       
        Log::info('Begin Send Mail'); 
        $url = env('API_NOTIFICATION_URL').'/email/receipt-email-request-jobs';
        $clientpost = Http::withHeaders(['Content-Type' => 'application/json','token' => env('TOKEN_API_NOTIFICATION')])->send('POST',$url,['body' =>  json_encode($datamail) ]);    
        $result = json_decode($clientpost,true);       
        if($result['status']== 200)
        {
            $url_trigger = env('API_NOTIFICATION_URL').'/email/send-email-automatic';
            $clientpost_trigger = Http::withHeaders(['Content-Type' => 'application/json','token' => env('TOKEN_API_NOTIFICATION')])->send('POST',$url_trigger);    
            $result_trigger = json_decode($clientpost_trigger,true);       

          Log::info('End Send Mail'); 
     
            $message = isset($request->language) ? config('message.' . $request->language . '.14') : config('message.en.14');

                return response()->json(
                ['status'       =>  200,
                    'success'   =>  true,
                    'message'   =>  $message,
                    'data'      =>  $result
                ], 200);
            }
          else{
            Log::info('End Send Mail'); 

                $message = isset($request->language) ? config('message.' . $request->language . '.15') : config('message.en.15');
              return response()->json(
              ['status'       =>  401,
                  'success'   =>  false,
                  'message'   =>   $message,
                  'data'      =>  $result
              ], 401);
          }          
           
    }

    public function sendWa($dataWa)
    {
        Log::info('Begin Send Mail'); 
        $url = env('API_NOTIFICATION_URL').'/whatsapp/receipt-wa-request';
        $clientpost = Http::withHeaders(['Content-Type' => 'application/json','token' => env('TOKEN_API_NOTIFICATION')])->send('POST',$url,['body' =>  json_encode($dataWa) ]);    
        $result = json_decode($clientpost,true);       
        if($result['status']== 200)
        {

          Log::info('End Send Wa'); 
            $message = isset($request->language) ? config('message.' . $request->language . '.16') : config('message.en.16');
                return response()->json(
                ['status'       =>  200,
                    'success'   =>  true,
                    'message'   =>  $message,
                    'data'      =>  $result
                ], 200);
            }
          else{
            Log::info('End Send Wa'); 

                $message = isset($request->language) ? config('message.' . $request->language . '.17') : config('message.en.17');
              return response()->json(
              ['status'       =>  401,
                  'success'   =>  false,
                  'message'   =>  $message,
                  'data'      =>  $result
              ], 401);
          }          
           
    }
  
    public function reset(Request $request)
    {
       
        if(empty($request->username) || empty($request->new_password))
        {        
            Log::error('Param Not Avaliable - End Reset Password');
                $message = isset($request->language) ? config('message.' . $request->language . '.1') : config('message.en.1');
            return response()->json([
                  'status'    =>  401,
                  'success'   =>  false,
                  'message'   =>  $message,
                  'data'      =>  []
              ], 401);
        }
        else
        {
            $data = [
                'username' => $request->username,
                'new_password' => $request->new_password
            ];
            if(!preg_match('/^[\w-]+$/', $request->new_password))
            {
                    $message = isset($request->language) ? config('message.' . $request->language . '.18') : config('message.en.18');

                return response()->json([
                    'status'    =>  401,
                    'success'   =>  false,
                    'message'   =>  $message,
                    'data'      =>  []
                ], 401);
            }

            UserModel::resetPassword($data);
            Log::error('End Reset Password '.$request->username);                
            $message = isset($request->language) ? config('message.' . $request->language . '.19') : config('message.en.19');
            return response()->json([
                'status'    =>  200,
                'success'   =>  true,
                'message'   =>  $message,
                'data'      =>  []
            ], 200);
        }
 

    }
  
    public function change(Request $request)
    {
        Log::info('Begin Change Password');
        if(empty($request['username']) || empty($request['old_password']) || empty($request['new_password']))
        {        
          Log::error('Param Not Avaliable - End Change Password');
            $message = isset($request->language) ? config('message.' . $request->language . '.1') : config('message.en.1');
          return response()->json([
                'status'    =>  401,
                'success'   =>  false,
                'message'   =>  $message,
                'data'      =>  []
            ], 401);
        }
        else
        {        
            $data = [ 
                'username' => $request['username'],
                'old_password' => $request['old_password'],
                'new_password' => $request['new_password']
            ];
            $checkOldPassword =  UserModel::CheckOldPassword($data);
            if(empty($checkOldPassword))
            {
                Log::info('End Change Password '.$request['username'] );

                    $message = isset($request->language) ? config('message.' . $request->language . '.20') : config('message.en.20');
                return response()->json([
                    'status'    =>  401,
                    'success'   =>  false,
                    'message'   =>  $message,
                    'data'      =>  []
                ], 401);
            }
            else
            {
                $checkOldPasswordnew =  UserModel::CheckOldPasswordNew($data);
                if(isset($checkOldPasswordnew))
                {
                    Log::info('End Change Password '.$request['username'] );
                    $message = isset($request->language) ? config('message.' . $request->language . '.21') : config('message.en.21');
                    return response()->json([
                        'status'    =>  401,
                        'success'   =>  false,
                        'message'   =>  $message,
                        'data'      =>  []
                    ], 401);
                }
                else
                {
                    if(!preg_match('/^[\w-]+$/', $request->new_password))
                    {
                        Log::info('End Change Password '.$request['username'] );
                        $message = isset($request->language) ? config('message.' . $request->language . '.18') : config('message.en.18');
                        return response()->json([
                            'status'    =>  401,
                            'success'   =>  false,
                            'message'   =>  $message,
                            'data'      =>  []
                        ], 401);
                    }

                    UserModel::resetPassword($data);
                    Log::info('End Change Password '.$request['username'] );
                    $message = isset($request->language) ? config('message.' . $request->language . '.22') : config('message.en.22');
                    return response()->json([
                        'status'    =>  200,
                        'success'   =>  true,
                        'message'   =>  $message,
                        'data'      =>  []
                    ], 200);
                }
            }
        }

    }

    public function session(Request $request)
    {
        Log::info('Begin Session Check');
        $data = [
            'username' => $request['username'],
            'session_token' => $request['session_token']
        ];
        if(empty($request['username']) || empty($request['session_token']))
        {        
            Log::error('Param Not Avaliable - End Session Check');
            $message = isset($request->language) ? config('message.' . $request->language . '.1') : config('message.en.1');
            return response()->json([
                    'status'    =>  401,
                    'success'   =>  false,
                    'message'   =>  $message,
                    'data'      =>  []
                ], 401);
  
        }
        else
        {      
             $data_device = [
                'ios_id' => isset($request['ios_id']) ? $request['ios_id'] : null,
                'android_id' => isset($request['android_id']) ? $request['android_id'] : null,
                'device_name' => isset($request['device_name']) ? $request['device_name'] : null,
                'os_version' => isset($request['os_version']) ? $request['os_version'] : null,
                'ram' => isset($request['ram']) ? $request['ram'] : null,
                'storage' => isset($request['storage']) ? $request['storage'] : null
            ];          
            
            $username = $request['username'];           
            if ($data_device['android_id'] !== null && $data_device['device_name'] !== null) {                   
                $this->device($data_device, $username);
            }
           
            $data = [
                'username' => $request['username'],
                'session_token' => $request['session_token']
            ];
            $GetSession =  UserModel::GetSession($data);         
            if(!$GetSession){   
                    $message = isset($request->language) ? config('message.' . $request->language . '.23') : config('message.en.23');
                    return response()->json(
                    [   'status'       =>  403,
                        'success' => false,
                        'message' => $message,
                        'data' => []
                    ], 403);
            }
            $until_date = strtotime($GetSession->until_date);
            $now = time();


            $model = new SessionToken;
            if($GetSession->is_login == false)
            {
                $message = isset($request->language) ? config('message.' . $request->language . '.23') : config('message.en.23');
                    return response()->json(
                    [   'status'       =>  403,
                        'success' => false,
                        'message' => $message,
                        'data' => []
                    ], 403);

            }
            elseif($until_date < $now)
            {
                $model->where('session_token', $request->session_token)->update( ['is_login' => false]);
                $message = isset($request->language) ? config('message.' . $request->language . '.23') : config('message.en.23');
                return response()->json(
                [
                    'status'       =>  403,
                    'success' => false,
                    'message' => $message,
                    'data' => []
                ], 403);
            }
            else
            {
                if(isset($request->device_id))
                {
                    $checkDeviceId = DB::connection('sso')->table('auth.auth_users')
                        ->where('username', $request->username)
                        ->where('device_id', $request->device_id)
                        ->first();
                
                    if(empty($checkDeviceId))
                    {
                        $message = isset($request->language) ? config('message.' . $request->language . '.8') : config('message.en.8');
                        return response()->json([
                            'status'  => 403,
                            'success' => false,
                            'message' => $message,
                            'data'    => []
                        ], 403);
                    }
                }

                try 
                {  
                    if(isset($request->app_version))
                    {
                     $model->where('session_token', $request->session_token)->update(['app_version' => $request->app_version]);
                    }

                
                    $session_interval = env('SESSION_INTERVAL', '7 days'); 
                    $new_until_date = strtotime(date("Y-m-d H:i:s") . ' +' . $session_interval);
                    $new_until_date_formatted = date("Y-m-d H:i:s", $new_until_date); 


                     $model->where('session_token', $request->session_token)->update( ['until_date' => $new_until_date_formatted]);
                     $data = [
                        'username' => $request['username'],
                        'session_token' => $request['session_token']
                    ];
                     $GetSession =  UserModel::GetSession($data);
                } 
                catch (\Exception $e) 
                {
                    Log::channel('slack')->critical($e);
                    $message = isset($request->language) ? config('message.' . $request->language . '.7') : config('message.en.7');
                    return response()->json(
                        [   'status'       =>  400,
                            'success'   =>  false,
                            'message'   =>  $message,
                            'data'      =>  [$e]
                        ], 400);
                }    
            }
                Log::info('End Session Check '.$request['username']);
                $message = isset($request->language) ? config('message.' . $request->language . '.24') : config('message.en.24');
                $moduleUser =  UserModel::GetModuleUser($request->username);
                return response()->json([
                    'status'    =>  200,
                    'success'   =>  true,
                    'message'   =>  $message,
                    'data'      => [ 'session_data' =>$GetSession , 'module' => $moduleUser]
                ], 200);
        }
    }

    public function UserConfirm(Request $request)
    {
        Log::info('Begin User Confirm');
        if(count($request->param) == 11)
        {

            $CheckUserPersonal = UserModel::CheckUserPersonal($username = $request->username);
          
             if(isset($CheckUserPersonal)) 
             {
                
                if(!empty($request->param['image_upload_base64']))
                {
                    $file_name =  trim($request->param['nik']).'.png';

                    $imagebase64=base64_decode(preg_replace('#^data:image/\w+;base64,#i','','data:image/png;base64,'.$request->param['image_upload_base64']));
 
                    if(env('APP_ENV') == 'development')
                    {
                        $dir_name = '/application/storage/api_sso/image/users/'.$filename;
                    }
                    elseif(env('APP_ENV') == 'production')
                    {
                        $dir_name = '/storage/api_sso/image/users/'.$filename;
                    }
                    else
                    {
                        $dir_name = '/application/storage/api_sso/image/users/'.$filename;  // local device
                    }
                    file_put_contents($dir_name,$imagebase64);
                    $imageUrl = env('APP_URL').'/api/user-image-profile/'.$request->param['nik'];
                }
                else
                {
                    $file_name = null;
                    $imageUrl = null;
                }

                $dataUpdate = [ 
                    'nik' => strtolower(trim($request->param['nik'])),
                    'fullname' => strtolower(trim($request->param['fullname'])),
                    'address' => strtolower(trim($request->param['address'])),
                    'phone' => $request->param['phone'],
                    'wa_number' => $request->param['wa_number'],
                    'auth_type_id' => $request->param['type_id'],
                    'auth_entity_id' => $request->param['entity_id'],
                    'updated_by' => $request->param['nik'],
                    'updated_date' => date("Y-m-d H:i:s"),
                    'image_url' => $imageUrl,
                    'file_name' => $file_name,
                    'auth_mst_division_id' => $request->param['division_id'],
                    'auth_mst_department_id' => $request->param['department_id'],
                    'auth_mst_position_id' => $request->param['position_id']                    
                ];

                UserModel::UpdateUserPersonal($dataUpdate, $email = $CheckUserPersonal->email);       



             }
             else
             {
              
                if(!empty($request->param['image_upload_base64']))
                {
                    $file_name =  trim($request->param['nik']).'.png';
                    $imagebase64=base64_decode(preg_replace('#^data:image/\w+;base64,#i','','data:image/png;base64,'.$request->param['image_upload_base64']));
                    $dir_name= storage_path('image/users/').$file_name;
                    file_put_contents($dir_name,$imagebase64);
                    $imageUrl = env('APP_URL').'/api/user-image-profile/'.$request->param['nik'];
                }
                else
                {
                    $file_name = null;
                    $imageUrl = null;
                }

                $dataInsert = [ 
                    'nik' => strtolower(trim($request->param['nik'])),
                    'fullname' => strtolower(trim($request->param['fullname'])),
                    'address' => strtolower(trim($request->param['address'])),
                    'phone' => $request->param['phone'],
                    'wa_number' => $request->param['wa_number'],
                    'auth_type_id' => $request->param['type_id'],
                    'auth_entity_id' => $request->param['entity_id'],
                    'created_by' =>$request->param['nik'],
                    'image_url' => $imageUrl,
                    'file_name' => $file_name,
                    'auth_mst_division_id' => $request->param['division_id'],
                    'auth_mst_department_id' => $request->param['department_id'],
                    'auth_mst_position_id' => $request->param['position_id']                     
                ];
                UserModel::InserUserPersonal($dataInsert);          
                UserModel::EmailUpdatePersonal($username = $request->username, $nik = strtolower(trim($request->param['nik'])));

             }  



            Log::info('End  User Confirm');
 
            $message = isset($request->language) ? config('message.' . $request->language . '.25') : config('message.en.25');

            return response()->json([
                'status'    =>  200,
                'success'   =>  true,
                'message'   =>  $message,
                'data'      =>  []
            ], 200);

        }
        else
        {
          Log::error('Param Not Avaliable - End  User Confirm');

            $message = isset($request->language) ? config('message.' . $request->language . '.1') : config('message.en.1');

          return response()->json([
                'status'    =>  401,
                'success'   =>  false,
                'message'   =>  $message,
                'data'      =>  []
            ], 401);

        }


        Log::info('End User Confirm');

    }


    public function version_app(Request $request)
    {
        if($request->filled(['version_app', 'app_name'])) {
            $version = $request->input('version_app');
            $name_app = $request->input('app_name');
            $data = DB::connection('sso')
                    ->table('auth.auth_version_apk')
                    ->where('version', 'ilike', '%' . $version . '%')
                    ->where('name_app', 'ilike', '%' . $name_app . '%')
                    ->where('is_active', true)
                    ->count();
    
            if ($data == 0) {
                $message = isset($request->language) ? config('message.' . $request->language . '.4') : config('message.en.4');
                return response()->json([
                    'status'    => 400,
                    'success'   => false,
                    'message'   => $message,
                    'data'      => []
                ], 400);
            } else {
                $message = isset($request->language) ? config('message.' . $request->language . '.3') : config('message.en.3');
                return response()->json([
                    'status'    => 200,
                    'success'   => true,
                    'message'   => $message,
                    'data'      => []
                ], 200);
            }
        } else {
            Log::info('End Version');
            $message = isset($request->language) ? config('message.' . $request->language . '.1') : config('message.en.1');
            return response()->json([
                'status'    => 403,
                'success'   => false,
                'message'   => $message,
                'data'      => []
            ], 200);
        }
    }
    

    public function module_user(Request $request)
    {

        Log::info('Begin Module USer Check');
        $data = [
            'username' => $request['username'],
            'module' => $request['module']
        ];
        if(empty($request['username']) || empty($request['module']))
        {        
            Log::error('Param Not Avaliable - End Module User Check');
            $message = isset($request->language) ? config('message.' . $request->language . '.1') : config('message.en.1');
            return response()->json([
                'status'    =>  401,
                'success'   =>  false,
                'message'   =>  $message,
                'data'      =>  []
            ], 401);  
        }
        else
        {      
            $data = [
                'username' => $request['username'],
                'module' => $request['module']
            ];
            $GetCheckModuleUSer =  UserModel::GetCheckModuleUSer($data);
            if(!$GetCheckModuleUSer){
                $message = isset($request->language) ? config('message.' . $request->language . '.28') : config('message.en.28');
                return response()->json([
                    'status'    =>  401,
                    'success'   =>  false,
                    'message'   =>  $message,
                    'data'      =>  []
                ], 401);
            }

            Log::info('End module user Check '.$request['username']);
            $message = isset($request->language) ? config('message.' . $request->language . '.27') : config('message.en.27');
            return response()->json([
                'status'    =>  200,
                'success'   =>  true,
                'message'   =>  $message,
                'data'      =>  []
            ], 200);
        }

    }

   
}
