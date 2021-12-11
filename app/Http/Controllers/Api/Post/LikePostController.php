<?php

namespace App\Http\Controllers\Api\Post;

use App\Http\Controllers\Controller;
use App\Models\LikePost;
use App\Models\Post;
use Illuminate\Http\Request;

class LikePostController extends Controller
{
    public function likeHandler(Request $request, $id)
    {
        $post = Post::where('id', $id)->first();
        $user = $request->user();
        $likePost = LikePost::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'like' => true,
            'dislike' => false
        ]);

        return $this->sendResponse($likePost, 'like post successfully');
    }

    public function dislikeHandler(Request $request, $id)
    {
        $dislikePost = LikePost::where('post_id', $id)->first();
        $user = $request->user();
        if ($dislikePost->like && $dislikePost->user_id == $user->id) {
            $dislikePost->update([
                'post_id' => $id,
                'user_id' => $user->id,
                'like' => false,
                'dislike' => true
            ]);
            return $this->sendResponse($dislikePost, 'dislike post successfully');
        } else if ($dislikePost->dislike && $dislikePost->user_id == $user->id) {
            $dislikePost->update([
                'post_id' => $id,
                'user_id' => $user->id,
                'like' => true,
                'dislike' => false
            ]);
            return $this->sendResponse($dislikePost, 'like post successfully');
        }
    }
}
