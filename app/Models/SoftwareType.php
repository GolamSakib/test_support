<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwareType extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function software(){
        return $this->hasMany(Software::class, 'software_type_id', 'id');
    }
}
