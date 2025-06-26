<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FlashcardSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'question_ids',
        'is_public',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Lấy danh sách câu hỏi theo ID (chuỗi "1,2,3")
    public function questions()
    {
        $ids = explode(',', $this->question_ids);
        return Question::whereIn('id', $ids)->get();
    }

    // Tùy chọn: đếm số câu hỏi
    public function questionCount()
    {
        return count(explode(',', $this->question_ids));
    }

    protected static function booted()
    {
        static::creating(function ($set) {
            if (empty($set->slug)) {
                $set->slug = Str::slug($set->title . '-' . uniqid());
            }
        });
    }
}
