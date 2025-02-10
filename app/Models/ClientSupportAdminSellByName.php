<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSupportAdminSellByName extends Model
{
    use HasFactory;
    protected $guarded=[];
    public $timestamps=false;
    protected $table='client_support_admin_sellbyname';
}
