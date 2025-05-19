<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Thiagoprz\CompositeKey\HasCompositeKey;
use Illuminate\Database\Eloquent\Model;

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
