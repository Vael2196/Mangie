<?php

namespace App\Http\Controllers;

use App\Jobs\SendTaskWatchEmail;
use App\Models\TaskWatch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        return view('settings.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'notification_frequency' => ['required', Rule::in(array_keys(config('notifications.frequencies')))],
            'invisible' => ['required', 'boolean'],
        ]);
        $request->user()->forceFill($data)->save();
        if ($data['notification_frequency'] === 'never') {
            TaskWatch::where('user_id', $request->user()->id)->update([
                'pending_since' => null, 'change_count' => 0, 'actor_id' => null,
            ]);
        } elseif ($data['notification_frequency'] === 'instantly') {
            TaskWatch::where('user_id', $request->user()->id)->whereNotNull('pending_since')
                ->pluck('id')->each(fn ($id) => SendTaskWatchEmail::dispatch($id));
        }

        return back()->with('status', 'settings-saved');
    }
}
