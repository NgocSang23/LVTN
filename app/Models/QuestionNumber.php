<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Thiagoprz\CompositeKey\HasCompositeKey;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $question_number
 * @property int $test_id
 * @property int $topic_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Test $Test
 * @property-read \App\Models\Topic $Topic
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionNumber newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionNumber newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionNumber query()
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionNumber whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionNumber whereQuestionNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionNumber whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionNumber whereTopicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QuestionNumber whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class QuestionNumber extends Model
{
    use HasFactory, HasCompositeKey;

    protected $fillable = ['question_number', 'test_id', 'topic_id'];
    protected $primaryKey = ['test_id', 'topic_id'];

    public $incrementing = false;

    public function Test() {
        return $this->belongsTo(Test::class);
    }

    public function Topic() {
        return $this->belongsTo(Topic::class);
    }
}
