<?php

namespace App\Jobs;

use App\Mail\NoticeAlertMail;
use App\Models\CandidateSubscription;
use App\Models\Notice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifySubscribersOfNoticeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Notice $notice
    ) {}

    public function handle(): void
    {
        $this->notice->loadMissing(['institution.state', 'positions.reservations', 'post']);

        $stateId = $this->notice->institution?->state_id;
        $institutionId = $this->notice->institution_id;

        // Categories with vacancies in this notice
        $activeCategories = [];
        foreach ($this->notice->positions as $pos) {
            foreach ($pos->reservations as $res) {
                if ($res->vacancies > 0) {
                    $activeCategories[] = $res->category->value;
                }
            }
        }
        $activeCategories = array_unique($activeCategories);

        $query = CandidateSubscription::where('is_verified', true)
            ->where(function ($q) use ($stateId) {
                $q->whereNull('state_id');
                if ($stateId) {
                    $q->orWhere('state_id', $stateId);
                }
            })
            ->where(function ($q) use ($institutionId) {
                $q->whereNull('institution_id')
                    ->orWhere('institution_id', $institutionId);
            })
            ->where(function ($q) use ($activeCategories) {
                $q->whereNull('reservation_category');
                if (! empty($activeCategories)) {
                    $q->orWhereIn('reservation_category', $activeCategories);
                }
            });

        $subscriptions = $query->get();

        foreach ($subscriptions as $sub) {
            try {
                Mail::to($sub->email)->send(new NoticeAlertMail($this->notice, $sub));
                $sub->update(['last_notified_at' => now()]);
            } catch (\Throwable $e) {
                Log::warning("Failed sending notice email alert to {$sub->email}: {$e->getMessage()}");
            }
        }
    }
}
