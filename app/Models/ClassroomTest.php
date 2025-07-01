<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassroomTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'classroom_id',
        'test_id',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
