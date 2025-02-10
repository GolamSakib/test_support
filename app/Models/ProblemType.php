<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProblemType extends Model
{
    use HasFactory;
    protected $table = "supportadmin_problemtype";
    protected $guarded = ['id'];
    public $timestamps = false;
    // protected $fillable = [
    //     'title',
    // ];
    public function subproblem(){
        return $this->hasMany(ProblemSubType::class,'problem_type_id','id');
    }
}
