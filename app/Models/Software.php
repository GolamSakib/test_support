<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table='client_support_admin_softwarelistall';
    public $timestamps=false;

    public function softwaretypes()
    {
        return $this->hasOne(SoftwareType::class, 'id', 'software_type_id');
    }
}
