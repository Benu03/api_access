<?php


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Concerns\FrowView;
use Excel;
use Illuminate\Support\Facades\Storage;
use App\Models\NotifappModel;




class NotifAppController extends Controller
{

  
    public function NotifList(Request $request)
    {  
        
        log::info('Begin Notif List '.$request->username);

        $getUserName =  NotifAppModel::GetUserName($request->username);
        ($getUserName);
        if(isset($request->param['notif_category']) && isset($request->param['count']))
        {
            
            if($request->param['count'] == true)
            {
    
                                    log::info('Begin Notif List '.$request->getUserName);
                
                                    if($request->param['notif_category'] == 'all')
                                    {

                                        $module_req = isset($request->param['module']) ? $request->param['module'] : null;
                                        $Noread = NotifAppModel::NotifListNoread($username = $getUserName->username, $module_req);
                                    }
                                    else
                                    {
                                        $module_req = isset($request->param['module']) ? $request->param['module'] : null;
                                        $Noread = NotifAppModel::NotifListCategoryNoread($username = $getUserName->username,$notif_category = $request->param['notif_category'],$module_req );
                                    }
                
                                    log::info('End Notif List '.$getUserName->username);
                                    return response()->json(
                                        [   'status'       =>  200,
                                            'success'   =>  true,
                                            'message'   =>  'Request Success',
                                            'data'      =>   ['no_read' => $Noread,
                                                               'notif_list' => []
                                                            ]
                                        ], 200);
                                    
                   
            }
            else
            {

                if($request->param['notif_category'] == 'all')
                {
                    $module_req = isset($request->param['module']) ? $request->param['module'] : null;
                    $DataList = NotifAppModel::NotifList($username = $getUserName->username,$module_req);   

                  
                    foreach ($DataList as $notification) {
                        $dataContentArray = json_decode(json_encode($notification), true);
                    
                        $DataListAdjstloop[] = [
                           'notif_id' => $dataContentArray['notif_id'],
                           'title' => $dataContentArray['title'],
                           'created_date' => $dataContentArray['created_date'],
                           'category' => $dataContentArray['category'],
                           'module' => $dataContentArray['module'],
                           'is_read' => $dataContentArray['is_read'],                         
                           'data_content' => json_decode($dataContentArray['data_content'], true),
                        ];
                       
                    }
                    $DataListAdjst = isset($DataListAdjstloop) ? $DataListAdjstloop : [];

                    $Noread = NotifAppModel::NotifListNoread($username = $getUserName->username, $module_req);
    
                }
                else
                {
                    $module_req = isset($request->param['module']) ? $request->param['module'] : null;
                    $DataList = NotifAppModel::NotifListCategory($username = $getUserName->username,$notif_category = $request->param['notif_category'],$module_req );
                    foreach ($DataList as $notification) {
                        $dataContentArray = json_decode(json_encode($notification), true);
                 
                        $DataListAdjstloop[] = [
                           'notif_id' => $dataContentArray['notif_id'],
                           'title' => $dataContentArray['title'],
                           'created_date' => $dataContentArray['created_date'],
                           'category' => $dataContentArray['category'],
                           'module' => $dataContentArray['module'],
                           'is_read' => $dataContentArray['is_read'],
                           'data_content' => json_decode($dataContentArray['data_content'], true),
                        ];
                       
                    }

                   $DataListAdjst = isset($DataListAdjstloop) ? $DataListAdjstloop : [];


                    $Noread = NotifAppModel::NotifListCategoryNoread($username = $getUserName->username,$notif_category = $request->param['notif_category'],$module_req );
                }

              
                log::info('End Notif List '.$getUserName->username);
                return response()->json(
                    [   'status'       =>  200,
                        'success'   =>  true,
                        'message'   =>  'Request Success',
                        'data'      =>   ['no_read' => $Noread,
                                          'notif_list' => $DataListAdjst
                                        ]
                    ], 200);

            }       

        }
        else
        {
            log::info('End Notif List '.$request->username);
            return response()->json(
                [   'status'       =>  401,
                    'success'   =>  false,
                    'message'   =>  'Your Param Not Match',
                    'data'      =>  []
                ], 401);
        }

    }

    public function NotifDetail(Request $request)
    {  
      
      log::info('Begin Notif Detail '.$request->username);

      $getUserName =  NotifAppModel::GetUserName($request->username);
      
      $DataDetail = NotifAppModel::NotifDetail($notif_id = $request->param['notif_id'], $username = $getUserName->username);

        $dataContentArray = json_decode(json_encode($DataDetail), true);
 
        $DataDetailAdjst = [
           'notif_id' => $dataContentArray['notif_id'],
           'title' => $dataContentArray['title'],
           'message' => $dataContentArray['message'],
           'created_date' => $dataContentArray['created_date'],
           'category' => $dataContentArray['category'],
           'module' => $dataContentArray['module'],
           'is_read' => $dataContentArray['is_read'],
           'data_content' => json_decode($dataContentArray['data_content'], true),
        ];
       
      log::info('End Notif Detail '.$getUserName->username);
      return response()->json(
          [   'status'       =>  200,
              'success'   =>  true,
              'message'   =>  'Request Success',
              'data'      =>  $DataDetailAdjst
          ], 200);
    }

    public function NotifPosting(Request $request)
    {  
      
        log::info('Begin Notif Posting ');
        if(isset($request->data_content))
        {
            $dataContentArray = json_encode($request->input('data_content'), true);
        }
        else
        {
            $dataContentArray = null;
        }
        


        //begin general notif
        if(!isset($request->username))
        {
                  if($request->fcm == true)
                  {
                    $FcmController = new FcmController();
                    $FcmToken = NotifAppModel::GetFcmTokenByModule($module = $request->module);

                    foreach($FcmToken as $x => $val) {

                      $token = json_decode(json_encode($val), true);

                      $data = [
                          'token' => $token['token'],
                          'title' => $request->title,
                          'message' => $request->message,
                          'data'  => $request->data_content,
                      ];
                      $resultfcm = $FcmController->SendMessage($data);  
                    }

                    $data = [
                      'title' => $request->title,
                      'detail' => $request->message,
                      'username' => null,
                      'ntf_category_id' => 2,
                      'module' => $request->module,
                      'data_content' => $dataContentArray
                  ];
                  NotifAppModel::NotifPosting($data); 
              
                  log::info('End Notif Posting ');
                  return response()->json(
                      [   'status'       =>  200,
                          'success'   =>  true,
                          'message'   =>  'Request Success',
                          'data'      =>  []
                      ], 200);
           
                  }
                  elseif($request->fcm == "only")
                  {
                    
                    $FcmController = new FcmController();
                    $FcmToken = NotifAppModel::GetFcmTokenByModule($module = $request->module);
                   
                    foreach($FcmToken as $x => $val) {

                      $token = json_decode(json_encode($val), true);

                      $data = [
                          'token' => $token['token'],
                          'title' => $request->title,
                          'message' => $request->message
                      ];
                      $resultfcm = $FcmController->SendMessage($data);  
                    }

                    log::info('End Notif Posting ');
                    return response()->json(
                        [   'status'       =>  200,
                            'success'   =>  true,
                            'message'   =>  'Request Success',
                            'data'      =>  []
                        ], 200);
             

                  }
                  else
                  {
                        $data = [
                          'title' => $request->title,
                          'detail' => $request->message,
                          'username' => null,
                          'ntf_category_id' => 2,
                          'module' => $request->module,
                          'data_content' => $dataContentArray
                      ];
                      NotifAppModel::NotifPosting($data); 

                  
                      log::info('End Notif Posting ');
                      return response()->json(
                          [   'status'       =>  200,
                              'success'   =>  true,
                              'message'   =>  'Request Success',
                              'data'      =>  []
                          ], 200);


                    }
        }  //end general notif
        else  //begin info notif
        {
            
            $getUserName =  NotifAppModel::GetUserName($request->username);
            
       
            if($request->fcm == true)
            {
                  try 
                  {
                      $FcmController = new FcmController();
                      $FcmToken = NotifAppModel::GetFcmToken($username = $getUserName->username);
                      $token = json_decode(json_encode($FcmToken), true);
                      $data = [
                          'username' => $request->username,
                          'token' => $token['token'],
                          'title' => $request->title,
                          'message' => $request->message,
                          'data_content'  => $request->data_content,
                          'module' => $request->module,
                      ];
                      $resultfcm = $FcmController->SendMessage($data);    

                      $data = [
                        'title' => $request->title,
                        'detail' => $request->message,
                        'username' => $request->username,
                        'ntf_category_id' => 1,
                        'module' => $request->module,
                        'data_content' => $dataContentArray
                    ];
                    NotifAppModel::NotifPosting($data); 
            
                
                    log::info('End Notif Posting ');
                    return response()->json(
                        [   'status'       =>  200,
                            'success'   =>  true,
                            'message'   =>  'Request Success',
                            'data'      =>  []
                        ], 200);
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
            }
            elseif($request->fcm == false)
            {

                    $data = [
                        'title' => $request->title,
                        'detail' => $request->message,
                        'username' => $getUserName->username,
                        'ntf_category_id' => 1,
                        'module' => $request->module,
                        'data_content' => $dataContentArray
                    ];
                    NotifAppModel::NotifPosting($data); 
            
                
                    log::info('End Notif Posting ');
                    return response()->json(
                        [   'status'       =>  200,
                            'success'   =>  true,
                            'message'   =>  'Request Success',
                            'data'      =>  []
                        ], 200);           
            }
            elseif($request->fcm == "only")
            {
                $FcmController = new FcmController();
                $FcmToken = NotifAppModel::GetFcmToken($username = $getUserName->username);
                foreach($FcmToken as $x => $val) {

                  $token = json_decode(json_encode($val), true);

                  $data = [
                      'token' => $token['token'],
                      'title' => $request->title,
                      'message' => $request->message
                  ];
                  $resultfcm = $FcmController->SendMessage($data);  
                }

                log::info('End Notif Posting ');
                return response()->json(
                    [   'status'       =>  200,
                        'success'   =>  true,
                        'message'   =>  'Request Success',
                        'data'      =>  []
                    ], 200);
            }
            else
            {

                return response()->json(
                    [   'status'       =>  400,
                        'success'   =>  false,
                        'message'   =>  'Your Param Not Avaliable',
                        'data'      =>  []
                    ], 400);
            }

        } //end info notif
    } 


  
  


}
