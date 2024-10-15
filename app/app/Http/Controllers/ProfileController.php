<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Logout the user
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/login');
    }

    /**
     * show add users page (only admin)
     */
    public function showAddUsers(): View
    {
        $users = User::all();
        return view('profile.add-user', compact('users'));
    }

    /**
     * Add user to the project
     */
    public function addUser(ProfileUpdateRequest $request): RedirectResponse
    {
        $password = bcrypt('admin123');

        User::create([
            'email' => $request->email,
            'name' => $request->name,
            'password' => $password,
        ]);

        return Redirect::route('profile.add-user')->with('status', 'user-added');
    }

    /**
     * Update user details
     */
    public function updateUser(Request $request): RedirectResponse
    {
        Log::info('entering update usersssssss');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $request->user_id,
        ]);

        // Find the user
        $user = User::findOrFail($request->user_id);

        Log::info('User Data',$request->all());

        // Update the user details
        $user->name = $request->name;
        $user->email = $request->email;

        $user->save();


        return Redirect::route('profile.add-user')->with('status', 'user-updated');
    }

    /**
     * Remove user
     */
    public function removeUser($id): RedirectResponse
    {
        User::where('id', $id)->firstOrFail()->delete();

        return Redirect::route('profile.add-user')->with('status', 'user-removed');
    }
}
