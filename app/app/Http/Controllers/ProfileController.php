<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
    public function showAddUsers($id): View
    {

    //     $request->validate([
    //         'email' => ['required', 'email', 'unique:users'],
    //         'name' => ['required', 'string'],
    //         'password' => ['required', 'string', 'min:8'],
    //     ]);

    //     User::create([
    //         'email' => $request->email,
    //         'name' => $request->name,
    //         'password' => Hash::make($request->password),
    //     ]);

    //     return Redirect::route('profile.edit')->with('status', 'user-added');
        $user = User::findorFail($id);
        return view('profile.add-user', compact('user'));
    }

    /**
     * Add user to the project
     */
    public function addUser(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users'],
            'name' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'email' => $request->email,
            'name' => $request->name,
            'password' => "admin123",
        ]);

        return Redirect::route('profile.add-user')->with('status', 'user-added');
    }
}
