<?php

namespace App\Http\Controllers\Api\Post;

use App\Http\Controllers\Controller;
use App\Models\LikePost;
use Illuminate\Http\Request;

class LikePostController extends Controller
{
    public function likeHandler(Request $request, $id)
    {
        try {
            $dislikePost = LikePost::where('post_id', $id)->first();
            $user = $request->user();
            if ($dislikePost) {
                if ($dislikePost->like && $dislikePost->user_id == $user->id) {
                    $dislikePost->update([
                        'post_id' => $id,
                        'user_id' => $user->id,
                        'like' => false,
                        'dislike' => true
                    ]);
                    return $this->sendResponse($dislikePost, 'dislike post successfully');
                } else if ($dislikePost->dislike && $dislikePost->user_id == $user->id) {
                    $dislikePost = LikePost::updateOrCreate(
                        ['post_id' => $id, 'user_id' => $user->id],
                        ['like' => true, 'dislike' => false],
                    );
                    return $this->sendResponse($dislikePost, 'like post successfully');
                }
            } else {
                $dislikePost = LikePost::create([
                    'post_id' => $id,
                    'user_id' => $user->id,
                    'like' => true,
                    'dislike' => false
                ]);
                return $this->sendResponse($dislikePost, 'like post successfully');
            }
        } catch (\Throwable $th) {
            return $this->sendError('error', $th->getMessage(), 404);
        }
    }
}
