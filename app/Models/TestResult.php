<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Thiagoprz\CompositeKey\HasCompositeKey;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property string $answer
 * @property int $option_id
 * @property int $multiplequestion_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\MultipleQuestion $MultipleQuestion
 * @property-read \App\Models\Option $Option
 * @method static \Illuminate\Database\Eloquent\Builder|TestResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TestResult query()
 * @method static \Illuminate\Database\Eloquent\Builder|TestResult whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestResult whereMultiplequestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestResult whereOptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TestResult whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TestResult extends Model
{
    use HasFactory, HasCompositeKey;

    protected $fillable = ['answer', 'option_id', 'multiplequestion_id'];
    protected $primaryKey = ['option_id', 'multiplequestion_id'];

    public $incrementing = false;

    public function Option() {
        return $this->belongsTo(Option::class, 'option_id');
    }

    public function MultipleQuestion() {
        return $this->belongsTo(MultipleQuestion::class, 'multiplequestion_id');
    }
}
