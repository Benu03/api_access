<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\SessionToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class KeyServiceCheck
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

        $key_request = $request->header('key-service');
        $timestamp = $request->header('timestamp');

        if(empty($key_request) || empty($timestamp))
        {
            Log::error('Incomplete Parameter');
            return response()->json(
                [
                'status'    =>  401,
                'success' => false,
                'message' => 'Incomplete Parameters.',
                'data' => []
                ], 401);
        }


        $timestampValid = date("Y-m-d H:i:s", strtotime($timestamp));
    
        if ($timestampValid !== $timestamp || strtotime($timestamp) <= strtotime(date("Y-m-d")))  {
            Log::error('Incomplete Parameter');
            return response()->json(
                [
                'status'    =>  401,
                'success' => false,
                'message' => 'Invalid Timestamp data format.',
                'data' => []
                ], 401);
        } 
  
        $Encryp = env('KEY_SSO') . $timestamp;
        $KeyGenerate = hash(env('KEY_HASH'), $Encryp);

        if($key_request != $KeyGenerate){
            if(env('APP_ENV') != 'development')
            {
                $KeyGenerate = null;
            }
            Log::error('Invalid Credentials.');
            return response()->json(
                [
                'status'    =>  401,    
                'success' => false,
                'message' => 'Invalid Credentials.',
                'data' => [$KeyGenerate]
                ], 401);
        }
        
        return $next($request);
    }
}
