<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int $role
 * @property int $classroom_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ClassRoom $classroom
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomUser whereClassroomId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomUser whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClassroomUser whereUserId($value)
 * @mixin \Eloquent
 */
class ClassroomUser extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'classroom_id', 'role'];

    // --- RELATIONSHIPS ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
