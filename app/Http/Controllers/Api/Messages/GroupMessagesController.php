<?php

namespace App\Http\Controllers\Api\Messages;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRequest;
use App\Models\Group;
use App\Models\Message;
use App\Models\Schedule;

class GroupMessagesController extends Controller
{
    public function index(MessageRequest $request)
    {
        try {
            $groupId = $request['group_id'];
            if ($this->checkUserInGroup(auth()->user()->id, $groupId)) {
                $messageList = Message::where('group_id', $groupId)->orderBy('id', 'DESC')->get();
                $arr = [];
                foreach ($messageList as $item) {
                    $message = new \stdClass();
                    $message->message = $item;
                    $message->message->user = $item->user;
                    array_push($arr, $message);
                }
                return $this->sendResponse($arr, 'get messages successfully');
            } else {
                return $this->sendError('Error', 'Access denied', 403);
            }
        } catch (\Throwable $th) {
            return $this->sendError([], $th->getMessage());
        }
    }

    public function checkUserInGroup($userId, $groupId)
    {
        $postId = Group::find($groupId)->post_id;
        $users = Schedule::where('post_id', $postId)->get();
        $userIds = [];
        foreach ($users as $user) {
            array_push($userIds, $user->user->id);
        }
        if (in_array($userId, $userIds)) {
            return true;
        } else {
            return false;
        }
    }

    public function store(MessageRequest $request)
    {
        try {
            if ($request->validator->fails()) {
                return $this->sendError('Validation error.', $request->validator->messages(), 412);
            }
            if ($this->checkUserInGroup(auth()->user()->id, $request['group_id'])) {
                $message = Message::create([
                    'from' => auth()->user()->id,
                    'group_id' => (int) $request['group_id'],
                    'content' => $request['content'],
                ]);
                return $this->sendResponse($message, 'create message successfully');
            } else {
                return $this->sendError('Error', 'Access denied', 403);
            }
        } catch (\Throwable $th) {
            return $this->sendError([], $th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $message = Message::find($id);
            $from = (int)$message->from;
            if ($from === auth()->user()->id) {
                $message->delete();
                return $this->sendResponse(true, 'delete successfully');
            } else {
                return $this->sendError(false, 'access denied', 403);
            }
        } catch (\Throwable $th) {
            return $this->sendError([], $th->getMessage());
        }
    }
}
