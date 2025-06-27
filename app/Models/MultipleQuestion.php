<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property string $content
 * @property int $topic_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TestResult> $TestResults
 * @property-read int|null $test_results_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Test> $Tests
 * @property-read int|null $tests_count
 * @property-read \App\Models\Topic $Topic
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Option> $options
 * @property-read int|null $options_count
 * @method static \Illuminate\Database\Eloquent\Builder|MultipleQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MultipleQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MultipleQuestion query()
 * @method static \Illuminate\Database\Eloquent\Builder|MultipleQuestion whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MultipleQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MultipleQuestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MultipleQuestion whereTopicId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MultipleQuestion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MultipleQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'topic_id'
    ];

    public function Topic() {
        return $this->belongsTo(Topic::class);
    }

    public function Tests() {
        return $this->belongsToMany(Test::class, 'test__multiple_questions', 'multiplequestion_id', 'test_id');
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function TestResults() {
        return $this->hasMany(TestResult::class, 'multiplequestion_id');
    }
}
