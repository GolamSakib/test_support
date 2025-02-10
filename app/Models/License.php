<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = "clientlogin_license";
    public $timestamps = false;

    public function customers()
    {
        return $this->hasOne(Customer::class, 'customer_id', 'customer_id');
    }
    public function software()
    {
        return $this->belongsTo(software::class, 'software_id');
    }
}
