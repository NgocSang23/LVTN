<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $classroom_id
 * @property int $flashcard_set_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ClassRoom $classroom
 * @property-read \App\Models\FlashcardSet $flashcardSet
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomFlashcard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomFlashcard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomFlashcard query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomFlashcard whereClassroomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomFlashcard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomFlashcard whereFlashcardSetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomFlashcard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomFlashcard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomFlashcard whereUserId($value)
 * @mixin \Eloquent
 */
class ClassroomFlashcard extends Model
{
    use HasFactory;

    protected $fillable = ['flashcard_set_id', 'classroom_id', 'user_id'];

    // --- RELATIONSHIPS ---

    public function flashcardSet()
    {
        return $this->belongsTo(FlashcardSet::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
