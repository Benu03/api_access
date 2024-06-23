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
use App\Models\SSOModel;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\UserModel;
use Jenssegers\Agent\Agent;
use App\Events\RetentionActivityEvent;

class SSOController extends Controller
{
    public function home(Request $request)
    {
        $message = isset($request->language) ? config('message.' . $request->language . '.3') : config('message.en.3');
        return response()->json(
            [   'status'       =>  200,
                'success'   =>  true,
                'message'   =>  $message,
                'data'      =>  $request->username
            ], 200);
        
    
    }

    public function UserListModules(Request $request)
    {
        log::info('Begin User List Module '.$request->username);
        if(isset($request->param['module']))
        {
            $UserData =  SSOModel::GetUserListModule($module = $request->param['module']);
            log::info('End User List Module '.$request->username);
            $message = isset($request->language) ? config('message.' . $request->language . '.3') : config('message.en.3');
            return response()->json(
                [   'status'       =>  200,
                    'success'   =>  true,
                    'message'   =>  $message,
                    'data'      =>   ['module' => $request->param['module'],
                                       'user_list' => $UserData
                                    ]
                ], 200);

        }
        else
        {

            log::info('End User List Module '.$request->username);
            $message = isset($request->language) ? config('message.' . $request->language . '.1') : config('message.en.1');
            return response()->json(
                [   'status'       =>  401,
                    'success'   =>  false,
                    'message'   =>  $message,
                    'data'      =>  []
                ], 401);


        }

    }

    public function UserListModuleRole(Request $request)
    {
        log::info('Begin User List Module Role '.$request->username);
        if(isset($request->param['module']))
        {
            $UserData =  SSOModel::GetUserListModuleRole($module = $request->param['module'],$role = $request->param['role']);
            log::info('End User List Module Role'.$request->username);
            $message = isset($request->language) ? config('message.' . $request->language . '.3') : config('message.en.3');
            return response()->json(
                [   'status'       =>  200,
                    'success'   =>  true,
                    'message'   =>  $message,
                    'data'      =>   ['module' => $request->param['module'],
                                      'role' => $request->param['role'],
                                       'user_list' => $UserData
                                    ]
                ], 200);

        }
        else
        {

            log::info('End User List Module '.$request->username);
            $message = isset($request->language) ? config('message.' . $request->language . '.1') : config('message.en.1');
            return response()->json(
                [   'status'       =>  401,
                    'success'   =>  false,
                    'message'   =>  $message,
                    'data'      =>  []
                ], 401);


        }

    }

    public function RoleListModules(Request $request)
    {
        Log::info('Begin Role List By Module '.$request->username);

        $RoleList =  SSOModel::GetRoleListModule($module = $request->param['module']);

        log::info('End Role List By Module '.$request->username);
        $message = isset($request->language) ? config('message.' . $request->language . '.3') : config('message.en.3');
        return response()->json(
            [   'status'       =>  200,
                'success'   =>  true,
                'message'   =>  $message,
                'data'      =>   ['module' => $request->param['module'],
                'role_list' => $RoleList
             ]
            ], 200);


    }

    public function ModuleList(Request $request)
    {
        log::info('Begin Module List '.$request->username);


        $Module =  SSOModel::GetModuleList();

        log::info('End Module List '.$request->username);
      
        return response()->json(
            [   'status'       =>  200,
                'success'   =>  true,
                'message'   =>  'Request Success',
                'data'      =>   $Module 
            ], 200);
        
    
    }
    
    public function ImageModule($image)
    {       
        log::info('Begin image module');

        $filename = $image.'.png';
        if(env('APP_ENV') == 'development')
        {
            $path = '/application/storage/api_sso/image/module/'.$filename;
        }
        elseif(env('APP_ENV') == 'production')
        {
            $path = '/storage/api_sso/image/module/'.$filename;
        }
        else
        {
            $path = '/storage/api_sso/image/module/'.$filename;  // local device
        }
       
    
        if (file_exists($path)) {
            log::info('end image module');
            return response()->make(file_get_contents($path), 200, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'inline; filename= "'.$filename.'"'
            ]);
        }
        else
        {
            $path = '/application/storage/api_sso/image/module/default.png';
            if(env('APP_ENV') == 'development')
            {
                $path = '/application/storage/api_sso/image/module/default.png';
            }
            elseif(env('APP_ENV') == 'production')
            {
                $path = '/storage/api_sso/image/module/default.png';
            }
            else
            {
                $path = '/storage/api_sso/image/module/default.png';  // local device
            }
            log::info('end image module');
            return response()->make(file_get_contents($path), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename= "default.png"'
            ],200);

        }


    }


    public function UserImageProfile($image)
    {
        log::info('Begin user image profile');
        $filename = $image.'.png';

        if(env('APP_ENV') == 'development')
        {
            $path = '/application/storage/api_sso/image/users/'.$filename;
        }
        elseif(env('APP_ENV') == 'production')
        {
            $path = '/storage/api_sso/image/users/'.$filename;
        }
        else
        {
            $path = '/storage/api_sso/image/users/'.$filename;  // local device
        }

        if (file_exists($path)) {
            log::info('end image module');
            return response()->make(file_get_contents($path), 200, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'inline; filename= "'.$filename.'"'
            ]);
        }
        else
        {
         
            if(env('APP_ENV') == 'development')
            {
                $path = '/application/storage/api_sso/image/users/default.png';
            }
            elseif(env('APP_ENV') == 'production')
            {
                $path = '/storage/api_sso/image/users/default.png';
            }
            else
            {
                $path = '/storage/api_sso/image/users/default.png';  // local device
            }

            log::info('end image module');
            return response()->make(file_get_contents($path), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename= "default.png"'
            ],200);

        }

    }


    public function TypeList()
    {
        log::info('Begin Type List ');

        $Type =  SSOModel::GetTypeList();
        log::info('end Type List ');
        return response()->json(
            [   'status'       =>  200,
                'success'   =>  true,
                'message'   =>  'Request Success',
                'data'      =>   $Type
            ], 200);
     

    }

    public function EntityList()
    {
        log::info('Begin Entity List ');

        $Entity =  SSOModel::GetEntityList();
        log::info('end Entity List ');
        return response()->json(
            [   'status'       =>  200,
                'success'   =>  true,
                'message'   =>  'Request Success',
                'data'      =>   $Entity
            ], 200);

    }


    public function UserList()
    {
        log::info('Begin Entity List ');

        $Entity =  SSOModel::GetUserList();
        log::info('end Entity List ');
        return response()->json(
            [   'status'       =>  200,
                'success'   =>  true,
                'message'   =>  'Request Success',
                'data'      =>   $Entity
            ], 200);

    }


    public function divisionList(Request $request)
    {
        log::info('Begin Division List '.$request->username);


        $division =  SSOModel::GetDivisionList();

        log::info('End Division List '.$request->username);
      
        return response()->json(
            [   'status'       =>  200,
                'success'   =>  true,
                'message'   =>  'Request Success',
                'data'      =>   $division 
            ], 200);    
    }


    public function departmentList(Request $request)
    {
        log::info('Begin Department List '.$request->username);


        $department =  SSOModel::GetDepartmentList();

        log::info('End Department List '.$request->username);
      
        return response()->json(
            [   'status'       =>  200,
                'success'   =>  true,
                'message'   =>  'Request Success',
                'data'      =>   $department 
            ], 200);    
    }


    public function positionList(Request $request)
    {
        log::info('Begin Position List '.$request->username);

        $position =  SSOModel::GetPositionList();

        log::info('End Position List '.$request->username);
      
        return response()->json(
            [   'status'       =>  200,
                'success'   =>  true,
                'message'   =>  'Request Success',
                'data'      =>   $position 
            ], 200);    
    }



    public function CompanyPuninarList(Request $request)
    {
        log::info('Begin Company Puninar List '.$request->username);

        $company =  SSOModel::GetCompanyPuninarList();

        log::info('End Company Puninar List '.$request->username);
      
        return response()->json(
            [   'status'       =>  200,
                'success'   =>  true,
                'message'   =>  'Request Success',
                'data'      =>   $company 
            ], 200);    
    }

    public function UserProfile(Request $request)
    {
        log::info('Begin User Profile ');

        if(isset($request['username'])){
            log::info('End User Profile ');
            return response()->json(
                [   'status'    =>  200,
                    'success'   =>  true,
                    'message'   =>  'success',
                    'data'      => [
                                    'session' => UserModel::GetSessionUser($request['username']),
                                    'user_data' => UserModel::GetUserData($data =  [ 'username' => $request['username']]),
                                    'module' => UserModel::GetModuleUser($request['username']),
                                ]
                ], 200);
        }
        else
        {
            log::info('End User Profile');
            return response()->json(
                [   'status'    =>  401,
                    'success'   =>  false,
                    'message'   =>  'Your Request Not Match',
                    'data'      =>  []
                ], 401);

        }

    }

    public function UserInsert(Request $request)
    {
        log::info('Begin User Insert');

        if(isset($request->param['nik']) && $request->param['email'] )
        {
                $data = [
                    'nik' => $request->param['nik'],
                    'email' => $request->param['email'],
                ];
                $checkUser = UserModel::GetUserDataInsert($data);

                if(!isset($checkUser))
                {   
                    $string = strtolower($request->param['fullname']);
                    $words = explode(" ", $string);
                    $firstWord = str_replace("'", "",$words[0]);
        
                    if(count($words) == 1)
                    {
                        $secondword = substr($words[0], 0, 1);
                        $secondmail = $words[0];
                    }
                    else
                    {
                        $secondword = substr($words[1], 0, 1);
                        $secondmail = $words[1];
                    }
                
                    $fullinit = $firstWord.'.'.$secondword.rand(1,999);
                    $username = str_replace(array('-','(', ')', '..'), '',$fullinit);
            
                    try
                    {
                        $dataUser = [
                            'username' => $username,
                            'email' => $request->param['email'],
                            'password' => sha1($request->param['password']),
                            'is_confirm' => true,
                            'device_id' => Str::random(8),
                            'created_by' => $request->username,
                            'is_active' => true
                        ];

                        $userId = DB::connection('sso')->table('auth.auth_users')->insertGetId($dataUser);   

                        $dataInsert = [
                            'nik' => $request->param['nik'],
                            'fullname' => strtolower($request->param['fullname']),
                            'email' => $request->param['email'],
                            'address' => $request->param['address'],
                            'phone' =>  $request->param['phone'],
                            'wa_number' =>  $request->param['wa_number'],
                            'auth_type_id' => $request->param['type_id'],
                            'auth_entity_id' => $request->param['entity_id'],
                            'created_by' => $request->username,
                            'auth_mst_division_id' => $request->param['division_id'],
                            'auth_mst_department_id' => $request->param['department_id'],
                            'auth_mst_position_id' => $request->param['position_id']
                        ];
                        
                        $insertDataPErsonal = UserModel::InserUserPersonal($dataInsert);


                        if(isset($request->module) && $request->param['role'])
                        {
                            $checkUserModule = UserModel::GetModuleRoleId($module = $request->module , $role = $request->param['role']);    
                            
                            $dataAcces = [
                                'auth_users_id' => $userId,
                                'auth_role_module_id' => $checkUserModule->role_module_id,
                                'created_by' => $request->username
                            ];
                            UserModel::InserAccessModuleRole($dataAcces);

                        }

                    } 
                    catch (\Exception $e) 
                    {
                        Log::channel('slack_sso')->critical($e);
                        return response()->json(
                            [   'status'       =>  400,
                                'success'   =>  false,
                                'message'   =>  'Request Failed',
                                'data'      =>  [$e]
                            ], 400);
                    }    

                 


                    log::info('End User Insert');
                    return response()->json(
                        [   'status'       =>  200,
                            'success'   =>  true,
                            'message'   =>  'User Success Insert',
                            'data'      =>   [] 
                        ], 200);    
                }
                else
                {

                    log::info('End User Insert');
                    return response()->json(
                        [   'status'       =>  400,
                            'success'   =>  false,
                            'message'   =>  'User Already at system SSO',
                            'data'      =>   [] 
                        ], 400);    
                
                }
        }
        else
        {
                log::info('End User Insert');
                    return response()->json(
                        [   'status'       =>  400,
                            'success'   =>  false,
                            'message'   =>  'Your Param Not Match',
                            'data'      =>   [] 
                        ], 400);  
        }
   
    }

    public function UserAddModuleRole(Request $request)
    {

        log::info('Begin User add module role');

        if(isset($request->param['username']) && isset($request->param['role_module_id']) )
        {
            try
            {

                $GetUserId =  UserModel::GetUserId($username = $request->param['username']); 
                                
                $dataAcces = [
                    'auth_users_id' => $GetUserId->id,
                    'auth_role_module_id' => $request->param['role_module_id'],
                    'created_by' => $request->username
                ];
                UserModel::InserAccessModuleRole($dataAcces);
                
            } 
            catch (\Exception $e) 
            {
                Log::channel('slack_sso')->critical($e);
                return response()->json(
                    [   'status'       =>  400,
                        'success'   =>  false,
                        'message'   =>  'Request Failed',
                        'data'      =>  [$e]
                    ], 400);
            }    

        }
        else
        {
            log::info('End User add module role');
            return response()->json(
                [   'status'       =>  400,
                    'success'   =>  false,
                    'message'   =>  'Your Param Not Match',
                    'data'      =>   [] 
                ], 400);  
        }

        log::info('End User add module role');
        return response()->json(
            [   'status'       =>  200,
                'success'   =>  true,
                'message'   =>  'User Success add module role',
                'data'      =>   [] 
            ], 200);    


    }


    public function UserPostActivity(Request $request)
    {
        log::info('Begin User post activity');

        if(isset($request->username) && isset($request->module) && isset($request->activity_type))
        {
            try
            {

                $GetUserDataActivity =  UserModel::GetUserDataActivity($username = $request->username); 
                $dataActiity = [
                    'username' => $GetUserDataActivity->username,
                    'ip' => $request->getClientIp(true),
                    'agent' => $request->server('HTTP_USER_AGENT'),
                    'module' => $request->module,
                    'activity_type' => $request->activity_type,
                    'activity_detail' => $request->activity_detail

                ];
                UserModel::InserActivity($dataActiity);
                $username = $GetUserDataActivity->username;
                event(new RetentionActivityEvent($username));
             

            }
            catch (\Exception $e) 
            {
                // Log::channel('slack_sso')->critical($e);
                return response()->json(
                    [   'status'       =>  400,
                        'success'   =>  false,
                        'message'   =>  'Request Failed',
                        'data'      =>  [$e]
                    ], 400);
            }    

            log::info('End User post activity');
            return response()->json(
                [   'status'       =>  200,
                    'success'   =>  true,
                    'message'   =>  'User Success post activity',
                    'data'      =>   [] 
                ], 200);    

        }
        else
        {
            log::info('End User Post Activity');
            return response()->json(
                [   'status'       =>  400,
                    'success'   =>  false,
                    'message'   =>  'Your Param Not Match',
                    'data'      =>   [] 
                ], 400);  
        }

    }

    public function UserListActivity(Request $request)
    {
        log::info('Begin User List activity');

        dd($request);


    }


    public function UserSync(Request $request)
    {
       
       
        log::info('Begin User Sync');
        // $checkUser = UserModel::GetUserDataSycn($nik = $request->param['nik']);
        
        // Log::info($request);
        if(isset($checkUser))
        {   
            
            $dataUser = [
                'updated_by' => 'integration from '.$request->source,
                'updated_date' => date("Y-m-d H:i:s"),
                'is_active' => $request->param['is_active']
            ];
            UserModel::UpdateUserSycn($dataUser,$username = $checkUser->username);

            $dataPersonal = [
                'address' => $request->param['address'],
                'phone' =>  $request->param['phone'],
                'wa_number' =>  $request->param['wa_number'],
                'auth_type_id' => $request->param['type_id'],
                'auth_entity_id' => $request->param['entity_id'],
                'updated_by' => 'integration from '.$request->source,
                'updated_date' => date("Y-m-d H:i:s"),
                'auth_mst_division_id' => $request->param['division_id'],
                'auth_mst_department_id' => $request->param['department_id'],
                'auth_mst_position_id' => $request->param['position_id']
            ];

            UserModel::UpdateUserPersonalSycn($dataPersonal,$nik = $checkUser->nik);

        }
        else
        {
            $string = strtolower($request->param['fullname']);
            $words = explode(" ", $string);
            $firstWord = str_replace("'", "",$words[0]);

            if(count($words) == 1)
            {
                $secondword = substr($words[0], 0, 1);
                $secondmail = $words[0];
            }
            else
            {
                $secondword = substr($words[1], 0, 1);
                $secondmail = $words[1];
            }
        
            $fullinit = $firstWord.'.'.$secondword.rand(1,999);
            $username = str_replace(array('-','(', ')', '..'), '',$fullinit);
    
            try
            {
                $dataUser = [
                    'username' => $username,
                    'email' => $request->param['email'],
                    'password' => sha1('Puninar123'),
                    'is_confirm' => true,
                    'device_id' => Str::random(8),
                    'created_by' => 'integration from '.$request->source,
                    'is_active' => true
                ];

               $userID = DB::connection('sso')->table('auth.auth_users')->insertGetid($dataUser);   

                $dataInsert = [
                    'nik' => $request->param['nik'],
                    'fullname' => strtolower($request->param['fullname']),
                    'email' => $request->param['email'],
                    'address' => $request->param['address'],
                    'phone' =>  $request->param['phone'],
                    'wa_number' =>  $request->param['wa_number'],
                    'auth_type_id' => $request->param['type_id'],
                    'auth_entity_id' => $request->param['entity_id'],
                    'created_by' => 'integration from '.$request->source,
                    'auth_mst_division_id' => $request->param['division_id'],
                    'auth_mst_department_id' => $request->param['department_id'],
                    'auth_mst_position_id' => $request->param['position_id']
                ];
                
                $insertDataPErsonal = UserModel::InserUserPersonal($dataInsert);

                if($request->source == 'DMS')
                {

                    $dataRole = [
                        'auth_users_id' => $userID ,
                        'auth_role_module_id' => 2,
                        'created_by' => 'integration from '.$request->source
                    ];
                    DB::connection('sso')->table('auth.auth_user_x_role_module')->insert($dataRole);
                    
                }


            }
            catch (\Exception $e) 
            {
                    Log::channel('slack_sso')->critical($e);
                    return response()->json(
                        [   'status'       =>  400,
                            'success'   =>  false,
                            'message'   =>  'Request Failed',
                            'data'      =>  [$e]
                        ], 400);
            }    

        }   

        log::info('End User Sync');
        return response()->json(
            [   'status'       =>  200,
                'success'   =>  true,
                'message'   =>  'User Sycn Success',
                'data'      =>   [] 
            ], 200);    

    }

    
    







}
