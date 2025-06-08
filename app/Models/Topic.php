<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'embedding' => 'array',
        'subject_id '
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    public function Subject() {
        return $this->belongsTo(Subject::class);
    }

    public function Questions() {
        return $this->hasMany(Question::class);
    }

    public function MultipleQuestion() {
        return $this->hasMany(MultipleQuestion::class);
    }

    public function QuestionNumbers() {
        return $this->hasMany(QuestionNumber::class);
    }
}
