<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $is_resolved
 * @property int $user_id
 * @property int $question_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|DifficultCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DifficultCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DifficultCard query()
 * @method static \Illuminate\Database\Eloquent\Builder|DifficultCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DifficultCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DifficultCard whereIsResolved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DifficultCard whereQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DifficultCard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DifficultCard whereUserId($value)
 * @mixin \Eloquent
 */
class DifficultCard extends Model
{
    use HasFactory;
    protected $fillable = [
        'is_resolved',
        'user_id',
        'question_id',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
