<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSessionPair extends Model
{
    protected $fillable = ['user_id', 'login_session_id', 'logout_session_id'];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    
       public function login()
    {
        return $this->belongsTo(UserSession::class, 'login_session_id');
    }

    public function logout()
    {
        return $this->belongsTo(UserSession::class, 'logout_session_id');
    }
}
