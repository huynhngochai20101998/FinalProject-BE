<?php

namespace App\Http\Controllers\Api\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $posts = Post::paginate(5);
            return $this->sendResponse($posts, 'Successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Error', $th, 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePostRequest $request)
    {
//        try {
            if ($request->validator->fails()) {
                return $this->sendError('Validation error.', $request->validator->messages(), 403);
            }
            $post = Post::create([
                'slug' => Str::slug($request['title']),
                'title' => $request['title'],
                'content' => $request['content'],
                'user_id' => auth()->user()->id,
                'topic_id' => (int)$request['topic_id'],
                'members' => (int)$request->members,
                'schedule'=>$request->schedule,
            ]);

            return $this->sendResponse($post, 'Post created successfully.');
//        } catch (\Throwable $th) {
//            return $this->sendError($th, 'Validation error.', 403);
//        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        try {
            $post = new PostResource(Post::where('slug', $slug)->first());
            return $this->sendResponse($post, 'Post retrieved successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Post not found.', $th, 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePostRequest $request, $slug)
    {
        try {
            if ($request->validator->fails()) {
                return $this->sendError('Validation error.', $request->validator->messages(), 403);
            }
            $post = Post::where('slug', $slug)->first();
            $newSlug = Str::slug($request['title']);

            if ($slug != $newSlug) {
                $post->update([
                    'title' => $request->title,
                    'slug' => $newSlug,
                    'content' => $request['content'],
                    'user_id' => auth()->user()->id,
                    'topic_id' => (int)$request->topic_id,
                    'members' => (int)$request->members,
                    'schedule'=>$request->schedule
                ]);
            } else {
                $post->update([
                    'title' => $request->title,
                    'content' => $request['content'],
                    'user_id' => auth()->user()->id,
                    'topic_id' => (int)$request->topic_id,
                    'members' => (int)$request->members,
                    'schedule'=>$request->schedule
                ]);
            }

            return $this->sendResponse($post, 'Post updated successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Invalid validation', $th, 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        try {
            $post = Post::where('slug', $slug)->first();
            $post->delete();
            return $this->sendResponse($post, 'Post deleted successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Error.', $th, 403);
        }
    }
}
