<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserModel extends Model
{
 
  protected $connection = 'sso';

  public static function LoginCheckValidation($data)
  {
    $result = DB::table('auth.v_auth_user_personal')
              ->where(function ($query) use ($data) {
                $query->whereRaw("username ILIKE '".$data['username']."'")
                    ->orWhereRaw("email ILIKE '".$data['username']."'")
                    ->orWhereRaw("nik ILIKE '".$data['username']."'");
              })
              ->where('password', sha1($data['password']))
              ->where('is_active', true)
    ->first();
    return $result;   
  }

  public static function LoginCheckUser($data)
  {
      $username = $data['username'];
  
      $result = DB::table('auth.v_auth_user_personal')
          ->where(function($query) use ($username) {
              $query->where('username', 'ILIKE', $username)
                    ->orWhere('email', 'ILIKE', $username)
                    ->orWhere('nik', 'ILIKE', $username);
          })
          ->first();
  
      return $result;
  }

  public static function forgotCheckUser($data)
  {
    $result = DB::table('auth.v_auth_user_personal')
              ->where('email',$data['email'])
              ->first();
    return $result;   
  }

  public static function InsertLoginSession($DatainsertSession)
  {
    try 
    {
      $result =  DB::table('auth.auth_session_token')->insert($DatainsertSession);
      
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
    return $result;   
  }

  public static function GetUserData($data)
  {
    $result = DB::table('auth.v_auth_user_personal_show')
                ->whereRaw("username ILIKE '".$data['username']."'")
                ->orWhereRaw("email ILIKE '".$data['username']."'")
                ->orWhereRaw("nik ILIKE '".$data['username']."'")
              ->first();
    return $result;   
  }

  public static function GetUserDataInsert($data)
  {
    $result = DB::table('auth.v_auth_user_personal')
                ->WhereRaw("email ILIKE '".$data['email']."'")
                ->orWhereRaw("nik ILIKE '".$data['nik']."'")
              ->first();
    return $result;   
  }


  public static function GetUserDataSycn($nik)
  {
    $result = DB::table('auth.v_auth_user_personal')
                ->WhereRaw("nik ILIKE '".$nik."'")
              ->first();
    return $result;   
  }

  public static function GetSessionUser($username)
  {    
    $result = DB::table('auth.auth_session_token')->selectRaw("session_token,TO_CHAR(created_date, 'YYYY-MM-DD HH24:MI:SS') as created_date,TO_CHAR(until_date, 'YYYY-MM-DD HH24:MI:SS') as until_date,access")
              ->where('username',$username)->orderby('created_date','Desc')
              ->first();    
    return $result;   
  }

  public static function GetSessionRelogin($username)
  {    
    $result = DB::table('auth.auth_session_token')->selectRaw("session_token,TO_CHAR(created_date, 'YYYY-MM-DD HH24:MI:SS') as created_date,TO_CHAR(until_date, 'YYYY-MM-DD HH24:MI:SS') as until_date,is_login,access")
              ->where('username',$username)->orderby('created_date','Desc')
              ->first();    
    return $result;   
  }
  
  public static function GetCheckModuleUSer($data)
  {  
  
    $result = DB::table('auth.v_auth_user_module')->where('username', $data['username'])->where('module', $data['module'])->first();
    return $result;   
  }

  public static function GetCheckGeneralServiceUSer($data)
  {  
  
    $result = DB::table('auth.v_auth_user_module')->where('username', $data['username'])->where('module', $data['module'])->where('key_module', $data['key_module'])->first();
    return $result;   
  }

  public static function GetModuleUser($username)
  {      

    if( env('APP_ENV') == 'development')
    {
      $result = DB::table('auth.v_auth_user_module')->selectRaw("module,
                          STRING_AGG(role, '|') AS role,
                          key_module,
                          dev_url as url,
                          image_module,platform,id_module,local_url,api_url
                          ")
      ->where('username',$username)
      ->orWhere('email',$username)
      ->groupBy(DB::raw('module,key_module,dev_url,image_module,platform,id_module,local_url,api_url'))
      ->get();    
    }
    else
    {
      $result = DB::table('auth.v_auth_user_module')->selectRaw("module,
            STRING_AGG(role, '|') AS role,
            key_module,
            prod_url as url,
            image_module,platform,id_module,null as local_url,api_url")
            ->where('username',$username)
            ->orWhere('email',$username)
            ->groupBy(DB::raw('module,key_module,prod_url,image_module,platform,id_module,local_url,api_url'))
            ->get();    

    }
    
    return $result;   
  }

  public static function GetModuleUserSingel($data)
  {
      
      $usernameOrEmail = $data['username'] ?? null;
      $module = $data['module'] ?? null;
  
      if (!$usernameOrEmail || !$module) {
          return collect(); 
      }
  

      $urlColumn = env('APP_ENV') == 'development' ? 'dev_url' : 'prod_url';
  
      $result = DB::table('auth.v_auth_user_module')
          ->selectRaw("module,
                       STRING_AGG(role, '|') AS role,
                       key_module,
                       $urlColumn as url,
                       image_module,
                       platform,
                       id_module,
                       local_url,
                       api_url")
          ->where(function($query) use ($usernameOrEmail) {
              $query->where('username', $usernameOrEmail)
                    ->orWhere('email', $usernameOrEmail);
          })
          ->where('module', $module)
          ->groupBy('module', 'key_module', $urlColumn, 'image_module', 'platform', 'id_module', 'local_url', 'api_url')
          ->get();
  
      return $result;
  }




  public static function SessionLogout($data)
  {    
    $result = DB::table('auth.auth_session_token')->where('session_token', $data['session_token'])->update( ['is_login' => false]);  
    return $result;   
  }

  public static function UpdateDeviceIdFirst($username,$deviceId)
  {    
    $result = DB::table('auth.auth_users')->where('username', $username)->update(['device_id' => $deviceId, 'device_update' => true]);  
    return $result;   
  }

  public static function CheckImage($image)
  {    
    $result = DB::table('auth.auth_module')->where('module',$image)
    ->first();    
    return $result;   
  }

  public static function CheckImageUser($username)
  {    
    $result = DB::table('auth.auth_personal')->where('username',$username)
    ->first();    
    return $result;   
  }

  public static function resetPassword($data)
  {    
      $result = DB::table('auth.auth_users')
          ->where(function($query) use ($data) {
              $query->where('username', $data['username'])
                    ->orWhere('email', $data['username']);
          })
          ->update(['password' => sha1($data['new_password'])]);  
      return $result;   
  }
  

  public static function CheckOldPassword($data)
  {   
      $result = DB::table('auth.auth_users')
          ->where(function($query) use ($data) {
              $query->where('username', $data['username'])
                    ->orWhere('email', $data['username']);
          })
          ->where('password', sha1($data['old_password']))
          ->first();                
      return $result;   
  }
  
  public static function CheckOldPasswordNew($data)
  {   
      $result = DB::table('auth.auth_users')
          ->where(function($query) use ($data) {
              $query->where('username', $data['username'])
                    ->orWhere('email', $data['username']);
          })
          ->where('password', sha1($data['new_password'])) 
          ->first();                
      return $result;   
  }

  public static function GetSession($data)
  {   
    $result = DB::table('auth.auth_session_token')
            ->where('session_token',$data['session_token'])
            ->first();                
    return $result;   
  }

  public static function SessionSingelLogin($username, $access)
  {   
    $result = DB::table('auth.auth_session_token')
              ->where('username', $username)->where('access', $access)->where('is_login', true)
              ->whereRaw("created_date >= '".date("Y-m-d")."'")
            ->get();                
    return $result;   
  }

  public static function CheckUserForgot($email)
  {
    $result = DB::table('auth.v_auth_user_personal_show')
                ->whereRaw("email ILIKE '".$email."'")->first();
    return $result;   
  }

  public static function GetUserId($username)
  {
    $result = DB::table('auth.auth_users')
                ->whereRaw("username ILIKE '".$username."'")->first();
    return $result;   
  }

  public static function GetUserDataActivity($username)
  {
    $result = DB::table('auth.v_auth_user_personal')
    ->where(function($query) use ($username) {
        $query->where('username', 'ILIKE', $username)
              ->orWhere('email', 'ILIKE', $username)
              ->orWhere('nik', 'ILIKE', $username);
    })
    ->first();
    return $result;   
  }

  public static function CheckUserPersonal($username)
  {
    $result = DB::table('auth.v_auth_user_personal_show')
                ->whereRaw("username ILIKE '".$username."'")
                ->WhereNotNull('nik')->first();
    return $result;   
  }
  
  public static function InserUserPersonal($dataInsert)
  {
    try 
    {
      $result =  DB::table('auth.auth_personal')->insert($dataInsert);
      
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
    return $result;   

  }
  
  public static function InserAccessModuleRole($dataAcces)
  {
    try 
    {
      $result =  DB::table('auth.auth_user_x_role_module')->insert($dataAcces);
      
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
    return $result;   
  }

  public static function EmailUpdatePersonal($username,$nik )
  {
    try 
    {
      $user  = DB::table('auth.v_auth_user_personal_show')
                ->whereRaw("username ILIKE '".$username."'")->first();

      $confirm = DB::table('auth.auth_users')
                ->where('username',$username)
                ->update( ['is_confirm' => true]);    

      $result = DB::table('auth.auth_personal')
              ->where('nik',$nik)
              ->update( ['email' => $user->email]);       
      
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
    return $result;   
  }

  public static function UpdateUserPersonal($dataUpdate, $email)
  {
    try 
    {
      $result = DB::table('auth.auth_personal')
              ->where('email',$email)
              ->update($dataUpdate);
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
    return $result;   
  }

  public static function GetModuleRoleId($module, $role)
  {   
    $result = DB::table('auth.v_auth_module_role')
                ->where(function ($query) use ($module,$role) {
                  $query->whereRaw("module ILIKE '".$module."'")
                      ->orWhereRaw("role ILIKE '".$role."'");
                })
              ->first();                
    return $result;   
  }

  public static function InserActivity($dataActiity)
  {
    try 
    {
      $result =  DB::table('auth.auth_user_activity')->insert($dataActiity);
      
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
    return $result;   
  }

  public static function ActivityRetention($username)
  {
  
    try 
    {
      $cutOffDate = Carbon::now()->subMonths(6);
      $result = DB::table('auth.auth_user_activity')
    ->where('username', $username)
    ->where('activity_time', '<', $cutOffDate)
    ->delete();
      
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
    return $result;   
  }

  public static function UpdateUserSycn($dataUser, $username)
  {
    try 
    {
      $result = DB::table('auth.auth_users')
              ->where('username',$username)
              ->update($dataUser);       
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
    return $result;   
  }

  public static function UpdateUserPersonalSycn($dataPersonal, $nik)
  {
    try 
    {
      $result = DB::table('auth.auth_personal')
              ->where('nik',$nik)
              ->update($dataPersonal);
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
    return $result;
  }



  public static function UpdateUserIsActive($dataUser)
  {
    
    $result = DB::table('auth.auth_users')
      ->where('username' ,$dataUser['user_isactive'])
      ->update( ['updated_by' => $dataUser['updated_by'],'updated_date' => $dataUser['updated_date'],'is_active' => $dataUser['is_active']]);     

      return $result;


  }


  



  



}



