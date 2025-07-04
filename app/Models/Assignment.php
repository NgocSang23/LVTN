<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'classroom_id',
        'flashcard_set_id',
    ];

    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function flashcardSet()
    {
        return $this->belongsTo(FlashcardSet::class);
    }
}
