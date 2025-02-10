<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = "clientlogin_payment";
    public $timestamps = false;

    public function software()
    {
        return $this->belongsTo(Software::class, 'software_id');
    }
}
