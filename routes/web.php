<?php

/** @var \Laravel\Lumen\Routing\Router $router */
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Log;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return redirect('https://www.ts3.co.id/');
});

// $router->get('/kirim-email', function () {

    
//     $recipient = 'ibnu.khoirin03@gmail.com';
//     $userName = 'ibnu khoirin';

//     Mail::to($recipient)->send(new WelcomeEmail($userName));
//     return 'Email terkirim!';
// });

// $router->get('/tes', function () {

 

//     // Log::critical('This is a critical message');
//     Log::error('This is an error message');
// });