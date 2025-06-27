<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property int $subject_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array|null $embedding
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MultipleQuestion> $MultipleQuestion
 * @property-read int|null $multiple_question_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\QuestionNumber> $QuestionNumbers
 * @property-read int|null $question_numbers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Question> $Questions
 * @property-read int|null $questions_count
 * @property-read \App\Models\Subject $Subject
 * @method static \Illuminate\Database\Eloquent\Builder|Topic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Topic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Topic query()
 * @method static \Illuminate\Database\Eloquent\Builder|Topic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Topic whereEmbedding($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Topic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Topic whereSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Topic whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Topic whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
