<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
//        var_dump(json_decode($request->schedule));
        try {
            $createSchedule = Schedule::create([
                'user_id' => $request->user_id,
                'post_id' => $request->post_id,
                'schedule' => $request->schedule
            ]);
            return $this->sendResponse($createSchedule, 'save schedule successfully');
        } catch (\Exception $e) {
            return $this->sendError($e, 'save schedule failed', 400);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($post_id)
    {
        $hostSchedule = Post::find($post_id);
        $hostScheduleContent = $hostSchedule->schedule;
        $memberSchedules = Schedule::where('post_id', $post_id)->get();
        $suitableUsers = [];
        foreach ($memberSchedules as $schedule) {
            if ($this->checkSchedule($schedule->schedule, $hostScheduleContent)) {
                $userSchedule = new \stdClass();
                $userSchedule->user_id = $schedule->user_id;
                $userSchedule->post_id = $schedule->post_id;
                $userSchedule->schedule = $schedule->schedule;
                array_push($suitableUsers, $userSchedule);
            }
        }

        return $this->sendResponse($suitableUsers, 'get schedule successfully');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function checkSchedule($schedule, $hostSchedule)
    {
        $checkCount = 0;
        $schedule = json_decode($schedule);
        $hostSchedule = json_decode($hostSchedule);
        for ($x = 0; $x <= 6; $x++) {
            $userIndex = $this->getIndexTimeFromSchedule($schedule[$x]);
            $hostIndex = $this->getIndexTimeFromSchedule($hostSchedule[$x]);
            if (!array_diff($hostIndex, $userIndex)) {
                $checkCount++;
            }
        }
        if ($checkCount == 7) {
            return true;
        } else {
            return false;
        }
    }

    public function getIndexTimeFromSchedule($arr)
    {
        $index = [];
        for ($x = 0; $x <= 4; $x++) {
            if ($arr[$x] == 1) {
                array_push($index, $x);
            }
        }
        return $index;
    }
}
