<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $content
 * @property string $level
 * @property string $type
 * @property int $card_id
 * @property int $topic_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnswerUser> $AnswerUser
 * @property-read int|null $answer_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Answer> $Answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\Card $Card
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DifficultCard> $DifficultCards
 * @property-read int|null $difficult_cards_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Image> $Images
 * @property-read int|null $images_count
 * @property-read \App\Models\Topic $Topic
 * @method static \Illuminate\Database\Eloquent\Builder|Question newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Question newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Question query()
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereTopicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

    public function answerUser()
    {
        return $this->hasMany(AnswerUser::class, 'question_id');
    }

    public function card()
    {
        return $this->belongsTo(Card::class, 'card_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function difficultCards()
    {
        return $this->hasMany(DifficultCard::class);
    }
}
