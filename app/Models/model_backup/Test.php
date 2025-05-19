<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'time',
        'user_id'
    ];

    public function User() {
        return $this->belongsTo(User::class);
    }

    public function QuestionNumbers() {
        return $this->hasMany(QuestionNumber::class);
    }

    public function MultipleQuestions() {
        return $this->belongsToMany(MultipleQuestion::class, 'test__multiple_questions', 'test_id', 'multiplequestion_id');
    }
}
