<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    protected $table = 'login_history';

    protected $fillable = ['user_id', 'ip_address', 'user_agent', 'logged_in_at', 'logged_out_at', 'session_id', 'is_impersonation'];

    protected $casts = [
        'logged_in_at' => 'datetime',
        'logged_out_at' => 'datetime',
        'is_impersonation' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
