<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class FcmModel extends Model
{
 
  protected $connection = 'sso';

  public static function CheckFcm($username,$fcm_token)
  {
    $result = DB::table('auth.auth_fcm_token')
              ->where('username',$username)
              ->first();
    return $result;   
  }
  


  public static function InsertFcmToken($DatainsertFcm)
  {
    try 
    {
      $result =  DB::table('auth.auth_fcm_token')->insert($DatainsertFcm);
      
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
 
  public static function UpdateFcmToken($username,$fcm_token)
  {
    try 
    {
      $result =  DB::table('auth.auth_fcm_token')->where('username',$username)->update( ['token' => $fcm_token,'updated_date'=>date('Y-m-d H:i:s')]);  

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



}