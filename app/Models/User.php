<?php

namespace App\Models;

use App\Models\CustomerSoftware;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasApiTokens, HasRoles;

    protected $table = 'supportadmin_support_user';
    // protected $table = 'all_users';
    protected $guarded = [];

    public function sendEmailVerificationNotification()
    {
        $this->notify(new EmailVerificationNotification());
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'name', 'email', 'password', 'address', 'first_name', 'last_name', 'phone_no', 'designation', 'photo',
    //     'email_verified_at', 'verification_code',  'user_type', 'password_confirmation', 'customer_id',
    //     'is_status_active', 'username','role_type'
    // ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    public function clients()
    {
        return $this->hasMany(Customer::class, 'customer_id', 'customer_id');
    }

    public function software()
    {
        return $this->hasMany(softwareSupportPerson::class, 'user_id', 'id');
    }

    public function softwareList()
    {
        return $this->hasMany(CustomerSoftware::class, 'user_id', 'id');
    }

    public function customerCountforSupportPerson()
    {
        return $this->hasMany(SoftwareSupportPerson::class, 'support_person_id', 'id');
    }

    public function customers()
    {
        return $this->hasMany(SoftwareSupportPerson::class, 'support_person_id')->with('software');
    }

    public function totalsupportsforSupportPerson()
    {
        return $this->hasMany(Support::class, 'accepted_support_id', 'id');
    }

    public function supportsGivenToUniqueClient()
    {
        return $this->hasMany(Support::class, 'accepted_support_id', 'id');
    }

    public function supportsGivenToUniqueSoftware()
    {
        return $this->hasMany(Support::class, 'accepted_support_id', 'id');
    }

    public function acceptedSupports()
    {
        return $this->hasMany(Support::class, 'accepted_support_id');
    }

    public function shop()
    {
        return $this->hasOne(shop::class, 'user_id', 'id');
    }
    public function customer_software()
    {
        return $this->belongsTo(CustomerSoftware::class, 'id', 'sale_by');
    }
    public function softwareSupportPerson()
    {
        return $this->hasMany(SoftwareSupportPerson::class);
    }

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

    // public function getStatusAttribute()
    // {
    //     return $this->attributes['isactive'];
    // }

    // Define a mutator for the 'user_id' alias.
    // public function setStatusAttribute($value)
    // {
    //     $this->attributes['isactive'] = $value;
    // }

}
