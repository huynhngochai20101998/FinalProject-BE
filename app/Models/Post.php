<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $fillable = [
        'slug',
        'title',
        'content',
        'user_id',
        'topic_id',
        'members',
        'number_of_lessons',
        'number_of_weeks',
        'registered_members'
    ];

    protected $hidden = ['user'];

    protected $appends = [
        'topic_name',
        'first_name',
        'last_name',
        'profile_image_url',
        'avatar',
        'active'
    ];

    protected $casts = [
        'registered_members' => 'array',
    ];

    /**
     * A post have many schedule
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFirstNameAttribute()
    {
        return $this->user->first_name;
    }

    public function getLastNameAttribute()
    {
        return $this->user->last_name;
    }

    public function getAvatarAttribute()
    {
        return $this->user->avatar;
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    public function getTopicNameAttribute()
    {
        return $this->topic()->name;
    }

    public function getProfileImageUrlAttribute()
    {
        if ($this->avatar) {
            return asset('/uploads/avatar/' . $this->avatar);
        } else {
            return 'https://ui-avatars.com/api/?background=random&name=' . urlencode($this->first_name . ' ' . $this->last_name);
        }
    }

    public function group()
    {
        return $this->hasOne(Group::class);
    }

    public function getActiveAttribute()
    {
        if (!$this->schedules->isEmpty() && !($this->group()->exists())) {
            return true;
        }
        return false;
    }
}
