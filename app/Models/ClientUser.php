<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class ClientUser extends Model
{
    use HasFactory, HasApiTokens, HasRoles;

    // use Notifiable, HasApiTokens, HasRoles;

    protected $table = 'addusers_users';
    protected $guarded = [];
    public $timestamps = false;
    protected $guard_name = 'web';

    public function getAllPermissions()
    {
        $permissions = $this->permissions;

        if (method_exists($this, 'roles')) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }
        $permission = $permissions->map(function ($p) {
            return $p->name;
        });

        return $permission;
    }

    public function saleBy()
    {
        return $this->belongsTo(User::class, 'sell_by_id');
    }

    public function software()
    {
        return $this->hasOne(CustomerSoftware::class, 'client_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'client_id', 'id');
    }

}
