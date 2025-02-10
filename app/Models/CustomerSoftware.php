<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSoftware extends Model
{
    use HasFactory;

    protected $table = "addusers_softwarelist";
    protected $guarded = [];
    public $timestamps = false;

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'client_id');
    }

    public function leadBy()
    {
        return $this->belongsTo(User::class, 'lead_by_id', 'id');
    }

    public function saleBy()
    {
        return $this->belongsTo(User::class, 'sell_by_id', 'id');
    }
    public function supports()
    {
        return $this->hasMany(SoftwareSupportPerson::class,'customer_software_id','id');
    }
}
