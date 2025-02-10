<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProblemSubType extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'title',
        'problem_type_id'
    ];

    public function problem()
    {
        return $this->belongsTo(ProblemType::class, 'problem_type_id');
    }
}
