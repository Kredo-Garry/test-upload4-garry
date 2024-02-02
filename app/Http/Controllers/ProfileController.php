<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    private $user;

    public function __construct(User $user){
        $this->user = $user;
    }

    # This edit method is going to retrieve all
    # the details of the user
    public function show($id){
        $user = $this->user->findOrFail($id);
               //SELECT * FROM users WHERE id = $id

        return view('users.profile.show')
            ->with('user', $user);
    }

    public function edit(){
        $user = $this->user->findOrFail(Auth::user()->id);
        return view('users.profile.edit')
            ->with('user', $user);
    }

    public function update(Request $request){
        $request->validate([
            'name'          => 'required|min:1|max:50',
            'email'         => 'required|email|max:50|unique:users,email,' . Auth::user()->id,
            'avatar'        => 'mimes:jpeg,jpg,png,gif|max:1048',
            'introduction'  => 'max:100'
        ]);

        $user           = $this->user->findOrFail(Auth::user()->id);
        $user->name     = $request->name;
        $user->email    = $request->email;
        $user->introduction = $request->introduction;

        if ($request->avatar) {
            $user->avatar = 'data:image/' . $request->avatar->extension() .
                            ';base64,' . base64_encode(file_get_contents($request->avatar));
        }

        $user->save();

        return redirect()->route('profile.show', Auth::user()->id);
    }

    public function followers($id)
    {
        $user = $this->user->findOrFail($id); //$id is the id of the user that we want to view
        return view('users.profile.followers')->with('user', $user);
    }

    public function following($id){
        $user = $this->user->findOrFail($id);
        return view('users.profile.following')->with('user', $user);
    }

    public function updatePassword(Request $request){
        //Check the current password ( Auth::user()->password ) is the logged in user
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return redirect()->back()->with('current_password_error', 'That\'s not your current password. Try again.')->with('error_password', 'Unable to change your password');
        }

        //check the new password if it is equal to the new password
        if ($request->current_password === $request->new_password) {
            return redirect()->back()->with('new_password_error', 'New password cannot be the same as your current password. Try again.')->with('error_password', 'Unable to change your password');
        }

        # Validata the data
        $request->validate([
            // 'new_password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()]
            'new_password' => ['required', 'confirmed', 'min:8', 'alpha_num']
        ]);

        $user = $this->user->findOrFail(Auth::user()->id);
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success_password', 'Password Changed Successfully.');

    }
}
