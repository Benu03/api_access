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


class AuthControllerGoogle extends Controller
{
   
    public function loginWgoogle(Request $request)
    {  
        log::info('Begin loginWgoogle User '.$request->username);


        log::info('End loginWgoogle User '.$request->username);


    }

    
   
}