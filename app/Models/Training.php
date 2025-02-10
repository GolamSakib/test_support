<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;
    protected $table = "clientlogin_training";
    protected $guarded=[];
    public $timestamps = false;


    public function customer()
    {
        // return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
        return $this->belongsTo(Customer::class, 'client_id', 'id');
    }

    public function shop()
    {
        return $this->belongsTo(shop::class, 'shop_id', 'id');
    }

    public function assigned()
    {
        // return $this->belongsTo(User::class, 'assigned_to', 'id');
        return $this->belongsTo(User::class, 'assigned_person', 'username');
    }
    public function software()
    {
        // return $this->belongsTo(Software::class, 'software_id', 'id');
        return $this->belongsTo(Software::class, 'soft_id', 'id');
    }



}
