<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionToken extends Model
{
    protected $connection = 'sso';
	public $timestamps    = false;
	protected $primaryKey = 'id';
	protected $table      = 'auth.auth_session_token';
	
}