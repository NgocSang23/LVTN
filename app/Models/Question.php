<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'level',
        'card_id',
        'topic_id',
        'type'
    ];

    public function AnswerUser() {
        return $this->hasMany(AnswerUser::class, 'question_id');
    }

    public function Card() {
        return $this->belongsTo(Card::class, 'card_id');
    }

    public function Images() {
        return $this->hasMany(Image::class);
    }

    public function Answers() {
        return $this->hasMany(Answer::class);
    }

    public function Topic() {
        return $this->belongsTo(Topic::class);
    }
}
