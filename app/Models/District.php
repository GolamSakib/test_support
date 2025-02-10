<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;
    protected $table = "addusers_district";
    protected $guarded=[];

    public function areas_under_district(){
        return $this->hasMany(Area::class,'district_id');
    }

    public function division(){
        return $this->belongsTo(Division::class);
    }
}
