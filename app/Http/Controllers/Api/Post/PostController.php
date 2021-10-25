<?php

namespace App\Http\Controllers\Api\Post;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
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
            return $this->success($posts);
        } catch (\Exception $e) {
            return $this->error($e);
        }
    }

    public function show($slug)
    {
        try {
            $post = Post::where('slug', $slug)->first();
            return $this->success($post);
        } catch (\Exception $e) {
            return $this->error($e);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

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
            $request['slug'] = Str::slug($request['title'], '-');
            $post = Post::create($request->all());
            return $this->success($post);
        } catch (\Exception $e) {
            return $this->error($e);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug)
    {
        try {
            $post = Post::where('slug', $slug)->first();
            $post->update($request->all());
            return $this->success($post);
        } catch (\Exception $e) {
            return $this->error($e);
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
            return $this->success($post);
        } catch (\Exception $e) {
            return $this->error($e);
        }

    }
}
