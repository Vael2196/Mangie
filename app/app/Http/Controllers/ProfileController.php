<?php

namespace App\Http\Controllers;

use App\Events\UserProfileUpdated;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Board;
use App\Models\User;
use App\Support\Realtime\RealtimePayload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

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
        $user = $request->user();
        $validated = $request->validated();
        $oldAvatarPath = $user->avatar_path;
        $newAvatarPath = null;

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if (!empty($validated['avatar_data'])) {
            $newAvatarPath = $this->storeAvatar(
                $validated['avatar_data']
            );
            $user->avatar_path = $newAvatarPath;
        } elseif ($validated['remove_avatar'] ?? false) {
            $user->avatar_path = null;
        }

        try {
            $user->save();
        } catch (\Throwable $exception) {
            if ($newAvatarPath) {
                Storage::disk('public')->delete($newAvatarPath);
            }

            throw $exception;
        }

        if (
            $oldAvatarPath
            && $oldAvatarPath !== $user->avatar_path
        ) {
            Storage::disk('public')->delete($oldAvatarPath);
        }

        $boards = Board::query()
            ->accessibleTo($user)
            ->get(['id', 'project_id']);

        if ($boards->isNotEmpty()) {
            broadcast(new UserProfileUpdated(
                RealtimePayload::user($user, $user),
                $boards->pluck('id')->all(),
                $boards->pluck('project_id')->unique()->all()
            ))->toOthers();
        }

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

        if ($user->avatar_path) {
            Storage::disk('public')->delete(
                $user->avatar_path
            );
        }

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

    private function storeAvatar(string $data): string
    {
        if (!preg_match(
            '/^data:image\/jpeg;base64,(.+)$/s',
            $data,
            $matches
        )) {
            throw ValidationException::withMessages([
                'avatar_data' =>
                    'The cropped profile photo is invalid.',
            ]);
        }

        $contents = base64_decode($matches[1], true);

        if (
            $contents === false
            || strlen($contents) > 2 * 1024 * 1024
            || !str_starts_with($contents, "\xFF\xD8\xFF")
        ) {
            throw ValidationException::withMessages([
                'avatar_data' =>
                    'The cropped profile photo is invalid or too large.',
            ]);
        }

        $path = 'avatars/' . Str::uuid() . '.jpg';

        if (!Storage::disk('public')->put($path, $contents)) {
            throw ValidationException::withMessages([
                'avatar_data' =>
                    'The profile photo could not be saved.',
            ]);
        }

        return $path;
    }
}
