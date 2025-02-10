<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    use HasFactory;
    protected $table = 'client_support_admin_login_log';
    protected $guarded=[];
    public $timestamps = false; 

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id', 'id');
    // }
    public function user()
    {
        return $this->belongsTo(User::class, 'client_id', 'id');
    }

}
