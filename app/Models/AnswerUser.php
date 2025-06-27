<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string|null $content
 * @property int $user_id
 * @property int $question_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Question $Question
 * @property-read \App\Models\User $User
 * @method static \Illuminate\Database\Eloquent\Builder|AnswerUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AnswerUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AnswerUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|AnswerUser whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnswerUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnswerUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnswerUser whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnswerUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AnswerUser whereUserId($value)
 * @mixin \Eloquent
 */
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
