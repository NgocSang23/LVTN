<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 *
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string $time
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\History> $Histories
 * @property-read int|null $histories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MultipleQuestion> $MultipleQuestions
 * @property-read int|null $multiple_questions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuestionNumber> $QuestionNumbers
 * @property-read int|null $question_numbers_count
 * @property-read \App\Models\User $User
 * @method static \Illuminate\Database\Eloquent\Builder|Test newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Test newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Test query()
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test whereUserId($value)
 * @mixin \Eloquent
 */
class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'time',
        'user_id'
    ];

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    public function QuestionNumbers()
    {
        return $this->hasMany(QuestionNumber::class);
    }

    public function MultipleQuestions()
    {
        return $this->belongsToMany(MultipleQuestion::class, 'test__multiple_questions', 'test_id', 'multiplequestion_id');
    }

    public function Histories()
    {
        return $this->hasMany(History::class);
    }

    public function classrooms()
    {
        return $this->belongsToMany(ClassRoom::class, 'classroom_tests', 'test_id', 'classroom_id')->withTimestamps();
    }
}
