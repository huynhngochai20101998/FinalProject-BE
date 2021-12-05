<?php

namespace App\Http\Controllers\Api\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use stdClass;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        try {
            $q = $request->input('q');
            $topic = $request->input('topic');
            if ($q && $topic) {
                $posts = Post::where(function ($query) use ($topic, $q) {
                    return $query->where([
                        ["title", "like", "%{$q}%"],
                        ["topic_id", $topic]
                    ]);
                })->orderBy('created_at', 'DESC')->paginate(5);
            } else if ($q) {
                $posts = Post::where("title", "like", "%{$q}%")->orderBy('created_at', 'DESC')->paginate(5);
            } else if ($topic) {
                $posts = Post::where('topic_id', $topic)->orderBy('created_at', 'DESC')->paginate(5);
            } else {
                $posts = Post::orderBy('created_at', 'DESC')->paginate(5);
            }
            $posts->appends(array('q' => $q, 'topic' => $topic));
            return $this->sendResponse($posts, 'Successfully');
        } catch (\Throwable $th) {
            return $this->sendError('Error', $th->getMessage(), 200);
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
        try {
            if ($request->validator->fails()) {
                return $this->sendError('Validation error.', $request->validator->messages(), 403);
            }

            $user = $request->user();

            $post = Post::create([
                'slug' => Str::slug($request['title']),
                'title' => $request['title'],
                'content' => $request['content'],
                'user_id' => $user->id,
                'topic_id' => (int)$request['topic_id'],
                'members' => (int)$request->members,
                'number_of_lessons' => $request['number_of_lessons'],
                'number_of_weeks' => $request['number_of_weeks'],
            ]);
            $post->registered_members = Schedule::select('user_id')
                ->where('post_id', $post->id)->distinct()->get();
            $post->schedules = Schedule::where('post_id', $post->id)->get();
            return $this->sendResponse($post, 'Post created successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Error.', $th->getMessage(), 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $post = Post::where('id', $id)->first();
            $user = User::where('id', $post->user_id)->first();
            // query schedules by host
            $post->load(['schedules' => function ($query) use ($post) {
                $query->where('user_id', $post->user_id);
            }]);
            // select all member registered and owner in post
            $post->registered_members = Schedule::select('user_id')
                ->where('post_id', $post->id)->whereNotIn('user_id', [$post->user_id])->get();
            $checkS = $post->registered_members;
            $new_checkS = array_count_values(array_column($checkS, 'user_id'));
            $new_arr = [];
            // check total schedules of user enough number of lessons compare to owner required
            foreach ($new_checkS as $key => $member) {
                if ($member == $post->number_of_lessons) {
                    $obj = new stdClass();
                    $obj->user_id = $key;
                    $new_arr[] = $obj;
                    $post->registered_members = $new_arr;
                    // check if total member registered less than or equal total members require in post
                    if (count($post->registered_members) <= $post->members) {
                        $post->save();
                    }
                }
            }

            $post->first_name = $user->first_name;
            $post->last_name = $user->last_name;
            $post->avatar = $user->avatar;
            $post->profile_image_url = $user->profile_image_url;
            $post->makeHidden('user');
            return $this->sendResponse($post, 'Post retrieved successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Post not found', $th->getMessage(), 200);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePostRequest $request, $id)
    {
        try {
            if ($request->validator->fails()) {
                return $this->sendError('Validation error.', $request->validator->messages(), 403);
            }
            $post = Post::where('id', $id)->first();
            $user = $request->user();
            if ($post->user_id == $user->id) {
                $post->update([
                    'slug' => Str::slug($request['title']),
                    'title' => $request['title'],
                    'content' => $request['content'],
                    'user_id' => $user->id,
                    'topic_id' => (int)$request->topic_id,
                    'members' => (int)$request->members,
                    'number_of_lessons' => $request['number_of_lessons'],
                    'number_of_weeks' => $request['number_of_weeks'],
                ]);
            } else {
                return $this->sendError('Error', 'Access Denied', 403);
            }

            return $this->sendResponse($post, 'Post updated successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Error.', $th->getMessage(), 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $post = Post::where('id', $id)->first();
            if (auth()->user()->id == $post->user_id) {
                $post->delete();
                return $this->sendResponse($post, 'Post deleted successfully');
            }
            return $this->sendError('Error', 'Unauthorized', 401);
        } catch (\Throwable $th) {
            return $this->sendError('Error.', $th->getMessage(), 404);
        }
    }

    public function removePostMember(Request $request, $id)
    {
        try {
            $memberId = $request['member_id'];
            $post = Post::where('id', $id)->first();
            if ($post->user_id == auth()->user()->id) {
                foreach ($post->registered_members as $member) {
                    if ($memberId == $member['user_id']) {
                        $schedule = Schedule::where([
                            ['post_id', $post->id],
                            ['user_id', $member['user_id']]
                        ])->delete();
                    }
                }
            } else {
                return $this->sendError('error', 'access denied', 403);
            }
            return $this->sendResponse($schedule, 'remove member successfully');
        } catch (\Throwable $th) {
            return $this->sendError('error', $th->getMessage());
        }
    }
}
