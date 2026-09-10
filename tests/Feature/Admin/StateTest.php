<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guests_are_redirected_from_states_index(): void
    {
        $response = $this->get(route('admin.states.index'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function non_privileged_users_are_forbidden_from_states_index(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $response = $this->actingAs($viewer)->get(route('admin.states.index'));

        $response->assertForbidden();
    }

    #[Test]
    public function admin_can_view_states_index(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        State::firstOrCreate(
            ['iso_code' => 'IN-UP'],
            ['name' => 'Uttar Pradesh', 'type' => 'state', 'capital' => 'Lucknow', 'is_active' => true]
        );

        $response = $this->actingAs($admin)->get(route('admin.states.index'));

        $response->assertOk();
    }
}
