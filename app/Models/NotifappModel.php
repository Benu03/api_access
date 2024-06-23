<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class NotifappModel extends Model
{


  public static function NotifList($username,$module_req)
  {
    if($module_req != null) 
    {
      $result = DB::connection('sso')->table('ntf.v_ntf_notification_list')->selectRaw("id as notif_id,title,to_char(created_date,'YYYY-MM-DD HH24:MI:SS') as created_date ,category_name as category,module,is_read,data_content")
      ->where(function ($query) use ($username) {
        $query->whereRaw("username ILIKE '".$username."'")
            ->orWhereRaw("username isnull"); 
          })
          ->where('module',$module_req)->get();
    }
    else
    {
        $userModule = DB::connection('sso')->table('auth.v_auth_user_module')->select('module')->where('username',$username)->distinct()->get();
        foreach($userModule as $x => $val) 
        {
          $resultArray = json_decode(json_encode($val), true);       
            $module[] = $resultArray['module'];
        } 
        
        $result = DB::connection('sso')->table('ntf.v_ntf_notification_list')->selectRaw("id as notif_id,title,to_char(created_date,'YYYY-MM-DD HH24:MI:SS') as created_date ,category_name as category,module,is_read,data_content")
                  ->where(function ($query) use ($username) {
                    $query->whereRaw("username ILIKE '".$username."'")
                        ->orWhereRaw("username isnull"); 
                      })
                      ->wherein('module',$module)->get();

    }


    return $result;   
  }

  public static function NotifListNoread($username, $module_req)
  {
    
    
     if($module_req != null) 
     {
      $result = DB::connection('sso')->table('ntf.v_ntf_notification_list')->selectRaw("id as notif_id,title,to_char(created_date,'YYYY-MM-DD HH24:MI:SS') as created_date ,category_name as category,module,is_read,data_content")
      ->where(function ($query) use ($username) {
        $query->whereRaw("username ILIKE '".$username."'")
            ->orWhereRaw("username isnull");
      })
      ->wherenull('is_read')
      ->where('module',$module_req)->count();

     }
     else
     {
      $userModule = DB::connection('sso')->table('auth.v_auth_user_module')->select('module')->where('username',$username)->distinct()->get();

        foreach($userModule as $x => $val) 
        {
          $resultArray = json_decode(json_encode($val), true);       
            $module[] = $resultArray['module'];
        } 

        $result = DB::connection('sso')->table('ntf.v_ntf_notification_list')->selectRaw("id as notif_id,title,to_char(created_date,'YYYY-MM-DD HH24:MI:SS') as created_date ,category_name as category,module,is_read,data_content")
                    ->where(function ($query) use ($username) {
                      $query->whereRaw("username ILIKE '".$username."'")
                          ->orWhereRaw("username isnull");
                    })
              ->wherenull('is_read')->wherein('module',$module)->count();



     }

    return $result;   
  }

  public static function NotifListCategory($username,$notif_category,$module_req)
  {

    if($module_req != null)
    {
      $result = DB::connection('sso')->table('ntf.v_ntf_notification_list')->selectRaw("id as notif_id,title,to_char(created_date,'YYYY-MM-DD HH24:MI:SS') as created_date ,category_name as category,module,is_read,data_content")
      ->where(function ($query) use ($username) {
        $query->whereRaw("username ILIKE '".$username."'")
            ->orWhereRaw("username isnull");
      })
      ->where('category_name',$notif_category)->where('module',$module_req)->get();

    }
    else
    {
      
    $userModule = DB::connection('sso')->table('auth.v_auth_user_module')->select('module')->where('username',$username)->distinct()->get();

    foreach($userModule as $x => $val) 
    {
      $resultArray = json_decode(json_encode($val), true);       
        $module[] = $resultArray['module'];
    } 

    $result = DB::connection('sso')->table('ntf.v_ntf_notification_list')->selectRaw("id as notif_id,title,to_char(created_date,'YYYY-MM-DD HH24:MI:SS') as created_date ,category_name as category,module,is_read,data_content")
                  ->where(function ($query) use ($username) {
                    $query->whereRaw("username ILIKE '".$username."'")
                        ->orWhereRaw("username isnull");
                  })
              ->where('category_name',$notif_category)->wherein('module',$module)->get();

    }
    return $result;   
  }

  public static function NotifListCategoryNoread($username,$notif_category,$module_req)
  {
   
    if($module_req != null)
    {
      $result = DB::connection('sso')->table('ntf.v_ntf_notification_list')->selectRaw("id as notif_id,title,to_char(created_date,'YYYY-MM-DD HH24:MI:SS') as created_date ,category_name as category,module,is_read,data_content")
      ->where(function ($query) use ($username) {
        $query->whereRaw("username ILIKE '".$username."'")
            ->orWhereRaw("username isnull"); 
          })
      ->where('category_name',$notif_category)            
      ->wherenull('is_read')->where('module',$module_req)->count();
    }
    else
    {
        
    $userModule = DB::connection('sso')->table('auth.v_auth_user_module')->select('module')->where('username',$username)->distinct()->get();

    foreach($userModule as $x => $val) 
    {
      $resultArray = json_decode(json_encode($val), true);       
        $module[] = $resultArray['module'];
    } 

    $result = DB::connection('sso')->table('ntf.v_ntf_notification_list')->selectRaw("id as notif_id,title,to_char(created_date,'YYYY-MM-DD HH24:MI:SS') as created_date ,category_name as category,module,is_read,data_content")
              ->where(function ($query) use ($username) {
                $query->whereRaw("username ILIKE '".$username."'")
                    ->orWhereRaw("username isnull"); 
                  })
              ->where('category_name',$notif_category)            
              ->wherenull('is_read')->wherein('module',$module)->count();

    }

    return $result;   
  }

  public static function NotifDetail($notif_id,$username)
  {
    try 
    {
      $result = DB::connection('sso')->table('ntf.v_ntf_notification_list')
                    ->selectRaw("id as notif_id,title,detail as message,to_char(created_date,'YYYY-MM-DD HH24:MI:SS') as created_date ,category_name as category,is_read,module,data_content")
                    ->where('id',$notif_id)->first();

      
      $data = json_decode(json_encode($result), true);
      if($data['is_read'] != true)
      {
          $DatainsertNotifRead = [
                      'is_read' => true,
                      'ntf_notification_id' => $notif_id,
                      'username' => $username
                      ];    
                DB::connection('sso')->table('ntf.ntf_notification_read')->insert($DatainsertNotifRead);
      }

       return $result;   
    } 
    catch (\Exception $e) 
    {
      Log::channel('slack')->critical($e);
      return response()->json(
          [   'status'       =>  400,
              'success'   =>  false,
              'message'   =>  'Request Failed',
              'data'      =>  [$e]
          ], 400);
    }    
    return $result;   

  }


  public static function NotifPosting($data)
  {
    try 
    {
      DB::connection('sso')->table('ntf.ntf_notification')->insert($data);
    } 
    catch (\Exception $e) 
    {
      Log::channel('slack')->critical($e);
      return response()->json(
          [   'status'       =>  400,
              'success'   =>  false,
              'message'   =>  'Request Failed',
              'data'      =>  [$e]
          ], 400);
    }    
    return 'done';   

  }

  public static function GetFcmToken($username)
  {
    $result = DB::connection('sso')->table('auth.auth_fcm_token')->whereRaw("username ILIKE '".$username."'")->first();
    return $result;   
  }

  public static function GetFcmTokenByModule($module)
  {
    $result = DB::connection('sso')->table('auth.v_auth_user_module')->selectRaw("username,token")
      ->where('module',$module)->whereNotNull('token')->distinct()->get();
    return $result;   
  }

  
  public static function GetHseAdmin()
  {
    $result = DB::connection('sso')->table('auth.v_auth_user_module')->where('module','GENBA HSE')->where('role','ADMIN')->first();
    return $result;   
  }


  public static function GetGenbaAdmin()
  {
    $result = DB::connection('sso')->table('auth.v_auth_user_module')->where('module','GENBA HSE')->where('role','ADMIN')->first();
    return $result;   
  }


  public static function DataNotifGenbaHSe()
  {
    $result = DB::connection('hse')->table('gnb.gnb_task_hse as a')
      ->selectRaw('
          a.id,
          a.task_id,
          b.slug_status,
          a.tanggal_plan,
          CONCAT(a.created_by, \'|\', c.user_auditee) as receive')
      ->leftJoin('mst.mst_status_gnb_hse as b', 'a.mst_status_gnb_hse_id', '=', 'b.id')
      ->leftJoin('mst.mst_area_project_x_auditee as c', 'a.auditee', '=', 'c.auditee')
      ->whereIn('a.mst_status_gnb_hse_id', [4, 2])
      ->whereRaw('(a.tanggal_plan::text::date - \'1 days\'::interval) = \'now\'::text::date')
      ->groupBy('a.id', 'a.task_id', 'b.slug_status', 'a.tanggal_plan', 'receive')
      ->get();
    return $result;   
  }


  
  public static function GetUserName($username)
  {
    $result = DB::connection('sso')->table('auth.v_auth_user_personal_show')
                ->where(function ($query) use ($username) {
                  $query->whereRaw("username ILIKE '".$username."'")
                  ->orWhereRaw("email ILIKE '".$username."'")
                  ->orWhereRaw("nik ILIKE '".$username."'");
                })
              ->first();
    return $result;   
  }

  

  


}
