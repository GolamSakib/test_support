<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwareSupportPerson extends Model
{
    use HasFactory;
    protected $table = "addusers_supportperson";
    protected $guarded=[];
    public $timestamps=false;


    public function support_person(){
        return $this->hasOne(user::class,'id','support_person_id');
    }

    public function support_generated_by_client(){
        return $this->hasMany(Support::class,'client_id','client_id');

    }

    public function software(){
       return $this->hasMany(CustomerSoftware::class,'client_id','client_id');
    }

    public function customer(){
        // return $this->hasOne(Customer::class,'customer_id','customer_id');
        return $this->belongsTo(Customer::class,'client_id', 'id');
    }
}
