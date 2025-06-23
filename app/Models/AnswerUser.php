<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswerUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'question_id'
    ];

    public function User() {
        return $this->belongsTo(User::class);
    }
    public function Question() {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
