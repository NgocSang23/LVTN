<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property int $teacher_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $members
 * @property-read int|null $members_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ClassroomFlashcard> $sharedFlashcards
 * @property-read int|null $shared_flashcards_count
 * @property-read \App\Models\User $teacher
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|ClassRoom newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassRoom newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassRoom query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassRoom whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassRoom whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassRoom whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassRoom whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassRoom whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassRoom whereTeacherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassRoom whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClassRoom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'teacher_id'];

    // --- RELATIONSHIPS ---

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Lấy danh sách User tham gia lớp học
    public function users()
    {
        return $this->belongsToMany(User::class, 'classroom_users', 'classroom_id', 'user_id');
    }

    public function sharedFlashcards()
    {
        return $this->hasMany(ClassroomFlashcard::class, 'classroom_id');
    }

    // Trong model ClassRoom
    public function members()
    {
        return $this->belongsToMany(User::class, 'classroom_users', 'classroom_id', 'user_id')
            ->withTimestamps(); // để lấy created_at
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id'); // hoặc cột giáo viên
    }

    public function tests()
    {
        return $this->belongsToMany(Test::class, 'classroom_tests', 'classroom_id', 'test_id')->withTimestamps();
    }
}
