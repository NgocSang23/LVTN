<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultipleQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'topic_id'
    ];

    public function Topic() {
        return $this->belongsTo(Topic::class);
    }

    public function Tests() {
        return $this->belongsToMany(Test::class, 'test__multiple_questions', 'multiplequestion_id', 'test_id');
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function TestResults() {
        return $this->hasMany(TestResult::class, 'multiplequestion_id');
    }
}
