<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $table = 'topics';

    protected $fillable = [
        'slug',
        'name',
        'description',
    ];

    protected $appends = [
        'post_count'
    ];

    public function posts()
    {
        return $this->hasMany(Post::class, 'topic_id');
    }

    public function getPostCountAttribute()
    {
        return $this->posts->where('active', true)->count();
    }
}
