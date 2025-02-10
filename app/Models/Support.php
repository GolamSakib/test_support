<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    use HasFactory;
    protected $table = "supportadmin_problems";
    protected $guarded = [];
    public $timestamps = false;

    // protected $cast=[
    //     'soft_id' => 'integer',
    // ];


    public function customer()
    {
        // return $this->belongsTo(User::class, 'customer_id');
        return $this->belongsTo(Customer::class, 'client_id');
    }

    



    public function software()
    {
        return $this->belongsTo(Software::class, 'soft_id');
    }

    public function refused()
    {
        return $this->hasOne(User::class, 'id','refused_by')->withDefault();
    }

    public function helped()
    {
        return $this->hasOne(User::class, 'id','helped_by')->withDefault();
    }
    public function accepted_support()
    {
        return $this->hasOne(User::class, 'id','accepted_support_id')->withDefault();
    }

    // public function shop()
    // {
    //     return $this->hasOne(shop::class, 'id','shop_id')->withDefault();
    // }

    public function user()
    {
        return $this->hasOne(User::class, 'id','user_id')->withDefault();
    }

    public function problemType()
    {
        return $this->belongsTo(ProblemType::class);
    }
    public function softwareSupportPerson()
    {
        return $this->hasMany(
            SoftwareSupportPerson::class,
            'customer_software_id',
            'customer_id'
        );
    }
}
