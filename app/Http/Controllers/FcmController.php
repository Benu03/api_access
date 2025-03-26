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
use Illuminate\Support\Facades\Http;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Exception\Messaging\NotFound;
use App\Models\FcmModel;


class FcmController extends Controller
{


    public function ValidationFcm($username,$fcm_token)
    {
        Log::info('Begin Validation Fcm Token '.$username);

        $fcmData  = FcmModel::CheckFcm($username,$fcm_token);

                if(!isset($fcmData))
                {
                    $DatainsertFcm = [
                        'token' =>  $fcm_token,
                        'username' => $username
                    ];
                    $InsertData = FcmModel::InsertFcmToken($DatainsertFcm);

                }
                else
                {   
                    $updateData = FcmModel::UpdateFcmToken($username,$fcm_token);

                }

        Log::info('End Validation Fcm Token '.$username);
        return 'done';
    }




    public function SendMessage($data)
    {
        $url = env('URL_FCM_MSG');
        $apiAccessKey = env('API_ACCESS_KEY');
    
        $notification = [
            'to' => $data['token'],
            'notification' => [
                'title' => $data['title'],
                'body' => $data['message'],
                'icon' => 'ic_notification',
                'sound' => 'default',
            ],
            'data' => [
                'custom_key' => 'custom_value',
            ],
            'priority' => 'high',
            'time_to_live' => 3600,
            'ttl' => 3600,
        ];
    
        $headers = [
            'Authorization' => 'key=' . $apiAccessKey,
            'Content-Type' => 'application/json',
        ];
    
        $response = Http::withHeaders($headers)
            ->withOptions([
                'verify' => false,
            ])
            ->post($url, $notification);
    
        return $response->body();
    }

   


}