<?php

namespace App\Http\Controllers;

use App\Models\CandidateSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'state_id' => ['nullable', 'exists:states,id'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'reservation_category' => ['nullable', 'string', 'max:50'],
        ]);

        $token = Str::random(32);

        $subscription = CandidateSubscription::updateOrCreate(
            [
                'email' => strtolower($validated['email']),
                'state_id' => $validated['state_id'] ?? null,
                'reservation_category' => ! empty($validated['reservation_category']) ? strtoupper($validated['reservation_category']) : null,
            ],
            [
                'institution_id' => $validated['institution_id'] ?? null,
                'is_verified' => true,
                'verification_token' => $token,
            ]
        );

        return back()->with('success', 'You have successfully subscribed to verified recruitment notifications!');
    }

    public function destroy(string $token): RedirectResponse
    {
        $subscription = CandidateSubscription::where('verification_token', $token)->first();

        if ($subscription) {
            $subscription->delete();

            return redirect('/')->with('success', 'You have been unsubscribed from recruitment alerts.');
        }

        return redirect('/')->with('error', 'Invalid or expired subscription link.');
    }
}
