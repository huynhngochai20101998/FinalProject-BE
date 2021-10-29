<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'title', 'content', 'user_id', 'topic_id'];

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
