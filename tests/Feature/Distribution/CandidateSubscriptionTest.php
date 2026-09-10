<?php

namespace Tests\Feature\Distribution;

use App\Actions\Moderation\ApproveNoticeAction;
use App\Enums\NoticeStatus;
use App\Enums\QuotaType;
use App\Enums\ReservationCategory;
use App\Mail\NoticeAlertMail;
use App\Models\CandidateSubscription;
use App\Models\Institution;
use App\Models\Notice;
use App\Models\Position;
use App\Models\PositionReservation;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CandidateSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_allows_candidates_to_subscribe_with_email_state_and_category(): void
    {
        $state = State::factory()->create(['name' => 'Haryana', 'is_active' => true]);

        $response = $this->post('/subscriptions', [
            'email' => 'aspirant@example.com',
            'state_id' => $state->id,
            'reservation_category' => 'obc_ncl',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('candidate_subscriptions', [
            'email' => 'aspirant@example.com',
            'state_id' => $state->id,
            'reservation_category' => 'OBC_NCL',
            'is_verified' => true,
        ]);
    }

    #[Test]
    public function it_allows_candidates_to_unsubscribe_via_token(): void
    {
        $subscription = CandidateSubscription::factory()->create([
            'email' => 'candidate@example.com',
            'verification_token' => 'sample-test-token-12345',
        ]);

        $response = $this->get('/subscriptions/sample-test-token-12345');

        $response->assertRedirect('/');
        $this->assertDatabaseMissing('candidate_subscriptions', [
            'id' => $subscription->id,
        ]);
    }

    #[Test]
    public function it_dispatches_email_alerts_to_matching_candidates_on_notice_publication(): void
    {
        Mail::fake();
        Http::fake();

        $state = State::factory()->create();
        $institution = Institution::factory()->create(['state_id' => $state->id]);
        $moderator = User::factory()->create();

        // Subscriptions
        $matchingSub = CandidateSubscription::factory()->create([
            'email' => 'matching@example.com',
            'state_id' => $state->id,
            'reservation_category' => 'UR',
            'is_verified' => true,
        ]);

        $unrelatedSub = CandidateSubscription::factory()->create([
            'email' => 'unrelated@example.com',
            'state_id' => State::factory()->create()->id,
            'reservation_category' => 'SC',
            'is_verified' => true,
        ]);

        $notice = Notice::factory()->create([
            'institution_id' => $institution->id,
            'status' => NoticeStatus::PendingReview,
            'title' => 'Haryana Police Sub Inspector Recruitment 2026',
            'total_vacancies' => 450,
        ]);

        $pos = Position::factory()->create(['notice_id' => $notice->id]);
        PositionReservation::factory()->create([
            'position_id' => $pos->id,
            'category' => ReservationCategory::UR,
            'quota_type' => QuotaType::Vertical,
            'vacancies' => 200,
        ]);

        // Approve and publish
        $action = app(ApproveNoticeAction::class);
        $action->execute($notice, $moderator, 'Approved and published', true);

        // Verify email sent to matching subscriber
        Mail::assertSent(NoticeAlertMail::class, function ($mail) {
            return $mail->hasTo('matching@example.com')
                && str_contains($mail->envelope()->subject, 'Haryana Police Sub Inspector');
        });

        // Verify NOT sent to unrelated subscriber
        Mail::assertNotSent(NoticeAlertMail::class, function ($mail) {
            return $mail->hasTo('unrelated@example.com');
        });
    }
}
