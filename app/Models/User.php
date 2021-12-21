<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'birthday',
        'description',
        'interests',
        'gender',
        'school',
        'avatar'
    ];

    protected $appends = [
        'profile_image_url'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $attributes = [
        'role' => 'user',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendPasswordResetNotification($token)
    {
        $url = 'https://tsg-final-dtu.herokuapp.com/reset-password?token=' . $token;

        $this->notify(new ResetPasswordNotification($url));
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function getProfileImageUrlAttribute()
    {
        if ($this->avatar) {
            return asset('/uploads/avatar/' . $this->avatar);
        } else {
            return 'https://ui-avatars.com/api/?background=random&name=' . urlencode($this->first_name . ' ' . $this->last_name);
        }
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
