<?php

namespace App\Models;

use App\Models\Software;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Complain extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = "clientlogin_complain";
    public $timestamps = false;

    public function software()
    {
        return $this->belongsTo(Software::class, 'software_id');
    }
}
