<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\GuruProfileChange;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
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
        $data = $request->validated();

        if ($user->role === User::ROLE_GURU) {
            $oldValues = [
                'name' => $user->name,
                'no_hp' => $user->guruBkProfile?->no_hp,
                'nip' => $user->guruBkProfile?->nip,
            ];

            $user->fill([
                'name' => $data['name'],
                'username' => $data['no_hp'],
            ]);

            $user->save();

            $user->guruBkProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'no_hp' => $data['no_hp'],
                    'nip' => $data['nip'],
                ]
            );

            $newValues = [
                'name' => $data['name'],
                'no_hp' => $data['no_hp'],
                'nip' => $data['nip'],
            ];

            if ($oldValues !== $newValues) {
                GuruProfileChange::create([
                    'user_id' => $user->id,
                    'old_values' => $oldValues,
                    'new_values' => $newValues,
                ]);
            }

            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

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
}
