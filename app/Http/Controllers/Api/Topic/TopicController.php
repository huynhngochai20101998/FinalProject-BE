<?php

namespace App\Http\Controllers\Api\Topic;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTopicRequest;
use App\Http\Requests\UpdateTopicRequest;
use App\Models\Topic;
use Illuminate\Support\Str;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $topics = Topic::all();
            return $this->sendResponse($topics, 'Successfully.');
        } catch (\Throwable $e) {
            return $this->sendError('Error', $e, 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTopicRequest $request)
    {
        try {
            $request['slug'] = Str::slug($request['name']);
            if ($request->validator->fails()) {
                return $this->sendError('Validation error.', $request->validator->messages(), 403);
            }
            $topic = Topic::create($request->all());
            return $this->sendResponse($topic, 'Topic created successfully.');
        } catch (\Throwable $e) {
            return $this->sendError('Invalid validation.', $e, 403);
        }
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
            $topic = Topic::where('slug', $slug)->first();
            return $this->sendResponse($topic, 'Topic retrieved successfully.');
        } catch (\Throwable $e) {
            return $this->sendError('Topic not found.', $e, 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTopicRequest $request, $slug)
    {
        try {
            if ($request->validator->fails()) {
                return $this->sendError('Validation error.', $request->validator->messages(), 403);
            }
            $newSlug = Str::slug($request['name']);
            $topic = Topic::where('slug', $slug)->first();

            if ($slug != $newSlug) {
                $topic->update([
                    'slug' => $newSlug,
                    'name' => $request->name,
                    'description' => $request->description
                ]);
            } else {
                $topic->update([
                    'name' => $request->name,
                    'description' => $request->description
                ]);
            }

            return $this->sendResponse($topic, 'Topic updated successfully.');
        } catch (\Throwable $e) {
            return $this->sendError('Error.', $e, 403);
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
            $topic = Topic::where('slug', $slug)->first();
            $topic->delete();
            return $this->sendResponse($topic, 'Topic deleted successfully.');
        } catch (\Throwable $e) {
            return $this->sendError('Error.', $e, 401);
        }
    }
}
