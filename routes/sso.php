<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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


$sso->group(['middleware' => 'key_service'], function () use ($sso) 
{

        $sso->group(['prefix' => 'api/'], function () use ($sso) 
        {
                $sso->post('auth/login', 'AuthController@login');
                $sso->post('auth/logout', 'AuthController@logout');
                $sso->post('auth/forgot', 'AuthController@forgot');
                $sso->post('auth/reset-password', 'AuthController@reset');
                $sso->post('auth/change-password', 'AuthController@change');
                $sso->post('auth/check-session', 'AuthController@session'); 
                $sso->post('auth/check-module-user', 'AuthController@module_user');                    
                $sso->post('user-post-activity', 'SSOController@UserPostActivity');  
                $sso->post('user-list-activity', 'SSOController@UserListActivity');   
                $sso->post('version-app', 'AuthController@version_app');  
                $sso->get('auth/show-image-module/{image}', 'SSOController@ImageModule');
                $sso->get('user-image-profile/{image}', 'SSOController@UserImageProfile');
     

                $sso->group(['middleware' => 'Session'], function () use ($sso) {                    
                    $sso->post('user-list-by-module', 'SSOController@UserListModules');
                    $sso->post('user-list-by-module-role', 'SSOController@UserListModuleRole');
                    $sso->post('user-list', 'SSOController@UserList');
                    $sso->post('user-profile', 'SSOController@UserProfile');
                    $sso->post('user-confirm', 'AuthController@UserConfirm');   
                    $sso->post('user-insert', 'SSOController@UserInsert');  
                    $sso->post('user-add-module-role', 'SSOController@UserAddModuleRole'); 
                    $sso->post('module-list', 'SSOController@ModuleList');
                    $sso->post('role-list-by-module', 'SSOController@RoleListModules');   
                    $sso->post('type-list', 'SSOController@TypeList');   
                    $sso->post('entity-list', 'SSOController@EntityList');  
                    $sso->post('division-list', 'SSOController@divisionList');
                    $sso->post('department-list', 'SSOController@departmentList');
                    $sso->post('position-list', 'SSOController@positionList');
                });
                
                $sso->group(['prefix' => 'notif/'], function () use ($sso) {  
                    $sso->post('notif-list','NotifAppController@NotifList');
                    $sso->post('notif-detail','NotifAppController@NotifDetail');
                    $sso->post('notif-posting','NotifAppController@NotifPosting');      
                  });
       
              
                
        });

});
