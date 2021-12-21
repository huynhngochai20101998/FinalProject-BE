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

    public function showSchedulesByUser(Request $request, $id)
    {
        try {
            $schedules = Schedule::where([
                ['user_id', $request->user()->id],
                ['post_id', $id]
            ])->get();

            if ($schedules) {
                return $this->sendResponse($schedules, 'successfully');
            }
        } catch (\Throwable $th) {
            return $this->sendError('error', $th, 403);
        }
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

            $schedule = new Schedule();
            $schedule->post_id = (int) $request['post_id'];
            $schedule->day_id = $request['day_id'];
            $schedule->time_id = $request['time_id'];
            $schedule->user_id = $user->id;
            $schedule->value = $request['value'];

            $post = Post::where('id', $schedule->post_id)->first();
            $checkLimitSchedules = Schedule::where([
                ['user_id', $user->id],
                ['post_id', $post->id]
            ])->get();

            if (count($checkLimitSchedules) == $post->number_of_lessons) {
                return $this->sendError('error', 'out of range', 200);
            }
            $schedule->save();
            // select all member registered and owner in post
            $post->registered_members = Schedule::select('user_id')
                ->where('post_id', $post->id)->whereNotIn('user_id', [$post->user_id])->get();
            $checkS = $post->registered_members;
            $new_checkS = array_count_values(array_column($checkS, 'user_id'));
            $new_arr = [];
            foreach ($new_checkS as $member => $value) {
                if ($value == $post->number_of_lessons) {
                    $new_arr[] = (object) ['user_id' => $member];
                }
            }
            $post->registered_members = $new_arr;
            $post->save();
            return $this->sendResponse($schedule, 'add schedule successfully');
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
    public function destroySchedule(Request $request)
    {
        try {
            $schedule = Schedule::where([
                'post_id' => $request['post_id'],
                'day_id' => $request['day_id'],
                'time_id' => $request['time_id'],
                'user_id' => auth()->user()->id
            ])->first();

            if (auth()->user()->id == $schedule->user_id) {
                $schedule->delete();
                return $this->sendResponse($schedule, 'successfully');
            }
            return $this->sendError('error', 'fail delete');
        } catch (\Throwable $th) {
            return $this->sendError('error', $th->getMessage(), 403);
        }
    }
}
