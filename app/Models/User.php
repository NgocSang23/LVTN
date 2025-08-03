<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
// use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Notification; // BẮT BUỘC phải có

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $username
 * @property string|null $password
 * @property string|null $roles
 * @property string|null $platform_id
 * @property string|null $image
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnswerUser> $AnswerUsers
 * @property-read int|null $answer_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Card> $Cards
 * @property-read int|null $cards_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DifficultCard> $DifficultCards
 * @property-read int|null $difficult_cards_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FlashcardSet> $FlashcardSets
 * @property-read int|null $flashcard_sets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\History> $Histories
 * @property-read int|null $histories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Test> $Tests
 * @property-read int|null $tests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ClassRoom> $joinedClassrooms
 * @property-read int|null $joined_classrooms_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Notification> $customNotifications
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePlatformId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRoles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUsername($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'roles',
        'platform_id',
        'image'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function Cards()
    {
        return $this->hasMany(Card::class);
    }

    public function AnswerUsers()
    {
        return $this->hasMany(AnswerUser::class);
    }

    public function Tests()
    {
        return $this->hasMany(Test::class);
    }

    public function Histories()
    {
        return $this->hasMany(History::class);
    }

    public function DifficultCards()
    {
        return $this->hasMany(DifficultCard::class);
    }

    public function FlashcardSets()
    {
        return $this->hasMany(FlashcardSet::class);
    }

    public function joinedClassrooms()
    {
        return $this->belongsToMany(ClassRoom::class, 'classroom_users', 'user_id', 'classroom_id')->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function getRoleAttribute()
    {
        return $this->attributes['roles'];
    }

    public function classrooms()
    {
        return $this->belongsToMany(ClassRoom::class, 'classroom_users', 'user_id', 'classroom_id')
            ->withTimestamps();
    }
}
