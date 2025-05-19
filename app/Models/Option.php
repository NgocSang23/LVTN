<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Psy\TabCompletion\Matcher\FunctionDefaultParametersMatcher;

class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'content'
    ];

    public function TestResults() {
        return $this->hasMany(TestResult::class, 'option_id');
    }
}
