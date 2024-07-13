<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\SessionToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class SessionTokenCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $model = new SessionToken;
      
        if(isset($request->session_token))
        {
            $data = $model->select('session_token','username','created_date','until_date','is_login')
                    ->where('is_login', true)
                    ->where('session_token', $request->session_token)
                    ->first();

            if(empty($data))
            {
                return response()->json(
                [   'status'       =>  403,
                    'success' => false,
                    'message' => 'Invalid Session token',
                    'data' => []
                ], 403);
                    
            }
            else
            {   

                $until_date = strtotime($data['until_date']);
                $now = time();
    
                if($until_date < $now)
                {
                    $model->where('sesion_token', $request->session_token)->update( ['is_login' => false]);
                    return response()->json(
                    [
                        'status'       =>  403,
                        'success' => false,
                        'message' => 'your session expired please login again',
                        'data' => []
                    ], 403);
                }
                else
                {
                    try 
                    {
                    $session_interval = env('SESSION_INTERVAL', '7 days'); 
                    $new_until_date = strtotime(date("Y-m-d H:i:s") . ' +' . $session_interval);
                    $new_until_date_formatted = date("Y-m-d H:i:s", $new_until_date); 
                    $model->where('session_token', $request->session_token)->update( ['unit_date' => $new_until_date_formatted]);
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


            }
        }
        else{
            return response()->json(
                [   'status'       =>  403,
                    'success' => false,
                    'message' => 'Access Forbidden',
                    'data' => []
                ], 403);
        }
        
        return $next($request);
    }
}
