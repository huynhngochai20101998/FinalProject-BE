<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Image;

class ProfileController extends Controller
{
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors(), 403);
        }

        $user = $request->user();

        if (Hash::check($request->old_password, $user->password)) {
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return $this->sendResponse('Successfully', 'Password has been updated');
        } else {
            return $this->sendError('Error', 'Old password does not matched', 400);
        }
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'birthday' => 'nullable|date',
            'description' => 'nullable',
            'interests' => 'nullable',
            'gender' => 'nullable',
            'school' => 'nullable',
            'avatar' => 'nullable|image|mimes:jpg,png,bmp'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Error', $validator->errors(), 403);
        }

        $user = $request->user();

        $image = $request->file('avatar');
        $img = Image::make($image->path());
        $file_path = public_path('/uploads/avatar/');

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                $old_path = $file_path . $user->avatar;
                if (File::exists($old_path)) {
                    File::delete($old_path);
                }
            }
            $avatar = 'avatar-' . time() . '.' . $image->extension();
            $img->resize(400, 400, function ($const) {
                $const->aspectRatio();
            })->save($file_path . $avatar);
        } else {
            $avatar = $user->avatar;
        }

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'birthday' => $request->birthday,
            'description' => $request->description,
            'interests' => $request->interests,
            'gender' => $request->gender,
            'school' => $request->school,
            'avatar' => $avatar
        ]);

        return $this->sendResponse('Successfully', 'Profile has been updated');
    }

    public function show_profile($id)
    {
        $user = User::where('id', $id)->first();
        $user->load('posts');
        $user->post_registered = Post::select('*')
            ->whereJsonContains('registered_members', [['user_id' => $user->id]])
            ->orderBy('created_at', 'DESC')->get();
        // dd($user->post_registered['user_id']);
        return $this->sendResponse($user, 'Successfully');
    }
}
