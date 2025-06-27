<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $test_id
 * @property int $correct_count
 * @property int $total_questions
 * @property float $score
 * @property string $time_spent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Test $test
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|History newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|History newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|History query()
 * @method static \Illuminate\Database\Eloquent\Builder|History whereCorrectCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereTimeSpent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereTotalQuestions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|History whereUserId($value)
 * @mixin \Eloquent
 */
class History extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'test_id',
        'correct_count',
        'total_questions',
        'score',
        'time_spent'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
