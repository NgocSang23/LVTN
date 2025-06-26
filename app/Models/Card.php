<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function Question()
    {
        return $this->hasOne(Question::class, 'card_id');
    }

    public function scopeIsRandomOrder($query)
    {
        return $query->inRandomOrder(); // hoặc bất kỳ logic random nào bạn muốn
    }

    public function flashcardSet()
    {
        return $this->hasOne(FlashcardSet::class, 'question_ids')
            ->whereRaw('FIND_IN_SET(?, question_ids)', [$this->id]);
    }
}
