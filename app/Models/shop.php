<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class shop extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
    public function district()
    {
        return $this->belongsTo(District::class);
    }
    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
