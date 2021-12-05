<?php

namespace App\Http\Controllers\Api\Schedule;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Models\Post;
use App\Models\Schedule;
use Illuminate\Http\Request;
use stdClass;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Schedule::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreScheduleRequest $request)
    {
        try {
            if ($request->validator->fails()) {
                return $this->sendError('Validation error.', $request->validator->messages(), 403);
            }

            $user = $request->user();

            $schedule = Schedule::create([
                'post_id' => (int) $request['post_id'],
                'day_id' => $request['day_id'],
                'time_id' => $request['time_id'],
                'user_id' => $user->id,
                'value' => $request['value']
            ]);
            $post = Post::where('id', $schedule->post_id)->first();
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
                    // check if total member registered less than or equal total members require in post
                    if (count($post->registered_members) <= $post->members) {
                        $post->registered_members = $new_arr;
                    }
                } else {
                    $post->registered_members = [];
                }
                $post->save();
            }

            return $this->sendResponse($schedule, 'Successfully.');
        } catch (\Throwable $th) {
            return $this->sendError('Error.', $th->getMessage(), 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function checkSchedule(Request $request)
    {
        try {
            $user = $request->user();
            $schedule = Schedule::where([
                'user_id' => $user->id,
                'post_id' => $request['post_id'],
                'time_id' => $request['time_id'],
                'day_id' => $request['day_id']
            ])->get();
            if (!$schedule->isEmpty()) {
                return $this->sendResponse($schedule, 'Successfully.');
            }
            return $this->sendError('Error', 'Not Found', 200);
        } catch (\Throwable $th) {
            return $this->sendError('Error.', $th->getMessage(), 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $schedule = Schedule::where('id', $id)->first();
            if (auth()->user()->id == $schedule->user_id) {
                $schedule->delete();
                return $this->sendResponse($schedule, 'Successfully');
            }
            return $this->sendError('Error', 'Unauthorized', 401);
        } catch (\Throwable $th) {
            return $this->sendError('Error.', $th->getMessage(), 404);
        }
    }
}
