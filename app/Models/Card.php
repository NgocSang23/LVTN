<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Question|null $Question
 * @property-read \App\Models\User $User
 * @property-read \App\Models\FlashcardSet|null $flashcardSet
 * @method static \Illuminate\Database\Eloquent\Builder|Card isRandomOrder()
 * @method static \Illuminate\Database\Eloquent\Builder|Card newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Card newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Card query()
 * @method static \Illuminate\Database\Eloquent\Builder|Card whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Card whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Card whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Card whereUserId($value)
 * @mixin \Eloquent
 */
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
