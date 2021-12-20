<?php

namespace App\Http\Controllers\Api\Group;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Post;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // trả về các group theo post_id mà người dùng đk
        try {
            // khởi tạo mảng để lưu group
            $arr = [];
            //tìm mấy cái tkb chứa lz auth
            $schedules = Schedule::where('user_id', auth()->user()->id)->get();
            foreach ($schedules as $schedule) {
                // tìm lz group theo lz schedule xong bỏ bô mảng
                $group = Group::where('post_id', $schedule->post_id)->first();
                //kiểm tra mảng có group chưa
                if ($group) {
                    if (!in_array($group, $arr)) {
                        array_push($arr, $group);
                    }
                }
            }

            return $this->sendResponse($arr, 'fetch all groups data by user successfully');
        } catch (\Throwable $th) {
            return $this->sendError([], $th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function createGroup(Request $request, $id)
    {
        try {
            $post = Post::where('id', $id)->first();
            $group = Group::create([
                'name' => $request['name'],
                'wb_id' => Str::uuid(),
                'post_id' => $post->id,
                'user_id' => auth()->user()->id
            ]);

            if ($group) {
                $group->group_users = GroupUser::create([
                    'group_id' => $group->id,
                    'group_members' => $post->registered_members
                ]);
            }

            return $this->sendResponse($group, 'create group successfully');
        } catch (\Throwable $th) {
            return $this->sendError([], $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            // Substitute your Twilio Account SID and API Key details
            $accountSid = (string) env('TWILIO_ACCOUNT_SID');
            $apiKeySid = (string) env('TWILIO_API_KEY');
            $apiKeySecret = (string) env('TWILIO_API_SECRET');

            $group = Group::where('id', $id)->first();
            $group_user = GroupUser::where('group_id', $group->id)->first();
            $owner = User::where('id', $group->user_id)->first();
            $owner_username = $owner->first_name . " " . $owner->last_name;

            $user = $request->user();
            $identity = $user->last_name . $user->id;
            $user_name = $user->first_name . ' ' .  $user->last_name;
            $group_name = $group->name;

            // Create an Access Token
            $token = new AccessToken(
                $accountSid,
                $apiKeySid,
                $apiKeySecret,
                3600,
                $identity,
            );

            // Grant access to Video
            $grant = new VideoGrant();
            $grant->setRoom($group_name);
            $token->addGrant($grant);

            // Serialize the token as a JWT
            $result = [
                "owner" => [
                    "id" => $group->user_id,
                    "username" => $owner_username,
                ],
                "member_id" => $user->id,
                "user_name" => $user_name,
                "group_id" => $group->id,
                "count_members" => (count($group_user->group_members) + 1),
                "post_id" => $group->post_id,
                "group_name" => $group_name,
                'wb_id' => $group->wb_id,
                "token" => $token->toJWT()
            ];
            return $this->sendResponse($result, 'show group successfully');
        } catch (\Throwable $th) {
            return $this->sendError([], $th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGroupRequest $request, $id)
    {
        try {
            if ($request->validator->fails()) {
                return $this->sendError('Validation error.', $request->validator->messages(), 412);
            }

            $group = Group::where([
                ['id', $id],
                ['user_id', auth()->user()->id]
            ])->first();
            // check access permission
            if ($group) {
                $group->update([
                    'name' => $request['name'],
                ]);
                return $this->sendResponse($group, 'update group successfully.');
            } else {
                return $this->sendError([], 'update group failed.', 403);
            }
        } catch (\Throwable $th) {
            return $this->sendError([], $th->getMessage());
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
            $group = Group::where([
                ['id', $id],
                ['user_id', auth()->user()->id]
            ])->first();
            if ($group) {
                $group->delete();
                Post::where('id', $group->post_id)->delete();
                return $this->sendResponse(true, 'delete group successfully.');
            } else {
                return $this->sendError([], 'delete group failed', 403);
            }
        } catch (\Throwable $th) {
            return $this->sendError([], $th->getMessage());
        }
    }



    public function removeMemberFromGroup(Request $request, $id)
    {
        try {
            $memberId = $request['member_id'];

            $group_user = GroupUser::where('group_id', $id)->first();
            $arr = $group_user->group_members;

            $group = Group::where('id', $group_user->group_id)->first();
            if ($group->user_id == auth()->user()->id) {
                for ($i = 0; $i < count($arr); $i++) {
                    if ($memberId == $arr[$i]['user_id']) {
                        unset($arr[$i]);
                        Schedule::where('user_id', $memberId)->delete();
                        $group_user->group_members = $arr;
                        $group_user->save();
                    } else {
                        return $this->sendError('error', 'member not found', 404);
                    }
                }
                return $this->sendResponse(true, 'remove members from group successfully');
            } else {
                return $this->sendError('error', 'access denied', 401);
            }
        } catch (\Throwable $th) {
            return $this->sendError([], $th->getMessage());
        }
    }
}
