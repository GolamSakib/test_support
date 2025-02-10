<?php

namespace App\Models;

use App\Models\ClientUser;
use Illuminate\Support\Facades\DB;
use App\Models\SoftwareSupportPerson;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $table='addusers_customer';
    public $timestamps = false;



public function softwares()
{
        // return $this->hasMany(CustomerSoftware::class,'customer_id','customer_id');
        return $this->hasMany(CustomerSoftware::class,'client_id','id');
}

// public function supportPersons()
// {
//     return $this->hasMany(SoftwareSupportPerson::class, 'client_id', 'id')
//         ->where('client_id', DB::raw('id::text'));
// }

// public function users()
// {
//     return $this->hasMany(ClientUser::class, 'client_id', 'id')
//         ->where('client_id', DB::raw('id::text'));
// }


}
