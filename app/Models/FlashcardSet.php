<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $question_ids
 * @property int $is_public
 * @property string|null $slug
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|FlashcardSet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FlashcardSet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FlashcardSet query()
 * @method static \Illuminate\Database\Eloquent\Builder|FlashcardSet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlashcardSet whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlashcardSet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlashcardSet whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlashcardSet whereQuestionIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlashcardSet whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlashcardSet whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlashcardSet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlashcardSet whereUserId($value)
 * @mixin \Eloquent
 */
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
