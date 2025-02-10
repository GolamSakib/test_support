<?php

namespace App\Models;

use App\Models\Area;
use App\Models\District;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Division extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function districts(){
        return $this->hasMany(District::class,'division_id');
    }

}
