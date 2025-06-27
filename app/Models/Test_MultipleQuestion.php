<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $test_id
 * @property int $multiplequestion_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Test_MultipleQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Test_MultipleQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Test_MultipleQuestion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Test_MultipleQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test_MultipleQuestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test_MultipleQuestion whereMultiplequestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test_MultipleQuestion whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Test_MultipleQuestion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Test_MultipleQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'multiplequestion_id'
    ];
}
