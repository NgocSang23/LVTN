<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Thiagoprz\CompositeKey\HasCompositeKey;
use Illuminate\Database\Eloquent\Model;

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
