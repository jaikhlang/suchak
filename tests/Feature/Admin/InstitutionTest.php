<?php

namespace Tests\Feature\Admin;

use App\Enums\InstitutionType;
use App\Enums\UserRole;
use App\Models\Institution;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstitutionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => UserRole::Admin]);
    }

    #[Test]
    public function guests_are_redirected_from_institutions(): void
    {
        $response = $this->get(route('admin.institutions.index'));

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function admin_can_view_institutions_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.institutions.index'));

        $response->assertOk();
    }

    #[Test]
    public function admin_can_create_an_institution_with_aliases(): void
    {
        $state = State::firstOrCreate(
            ['iso_code' => 'IN-DL'],
            ['name' => 'Delhi', 'type' => 'union_territory', 'capital' => 'New Delhi', 'is_active' => true]
        );

        $payload = [
            'name' => 'National Testing Agency Test Entity',
            'short_name' => 'NTA-TEST',
            'slug' => 'nta-test',
            'institution_type' => InstitutionType::Autonomous->value,
            'state_id' => $state->id,
            'website_url' => 'https://nta.ac.in',
            'official_domain' => 'nta.ac.in',
            'is_verified' => true,
            'is_active' => true,
            'aliases' => [
                ['alias' => 'NTA Test', 'locale' => 'en', 'is_primary' => true],
                ['alias' => 'राष्ट्रीय परीक्षण एजेंसी', 'locale' => 'hi', 'is_primary' => true],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.institutions.store'), $payload);

        $response->assertRedirect(route('admin.institutions.index'));

        $this->assertDatabaseHas('institutions', [
            'slug' => 'nta-test',
            'short_name' => 'NTA-TEST',
            'official_domain' => 'nta.ac.in',
        ]);

        $this->assertDatabaseHas('institution_aliases', [
            'alias' => 'राष्ट्रीय परीक्षण एजेंसी',
            'locale' => 'hi',
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_creating_institution(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.institutions.store'), []);

        $response->assertSessionHasErrors(['name', 'institution_type', 'website_url']);
    }

    #[Test]
    public function admin_can_update_an_institution(): void
    {
        $institution = Institution::create([
            'name' => 'Original Name Commission',
            'slug' => 'original-name',
            'institution_type' => InstitutionType::CentralGov,
            'website_url' => 'https://original.gov.in',
            'official_domain' => 'original.gov.in',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Updated Name Commission',
            'short_name' => 'UNC',
            'slug' => 'updated-name',
            'institution_type' => InstitutionType::CentralGov->value,
            'website_url' => 'https://updated.gov.in',
            'official_domain' => 'updated.gov.in',
            'is_verified' => true,
            'is_active' => true,
            'aliases' => [
                ['alias' => 'UNC Updated', 'locale' => 'en', 'is_primary' => true],
            ],
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.institutions.update', $institution), $payload);

        $response->assertRedirect(route('admin.institutions.index'));

        $this->assertDatabaseHas('institutions', [
            'id' => $institution->id,
            'name' => 'Updated Name Commission',
            'slug' => 'updated-name',
        ]);

        $this->assertDatabaseHas('institution_aliases', [
            'institution_id' => $institution->id,
            'alias' => 'UNC Updated',
        ]);
    }

    #[Test]
    public function admin_can_delete_an_institution(): void
    {
        $institution = Institution::create([
            'name' => 'To Be Deleted Entity',
            'slug' => 'to-be-deleted',
            'institution_type' => InstitutionType::Psu,
            'website_url' => 'https://delete-me.gov.in',
            'official_domain' => 'delete-me.gov.in',
            'is_verified' => false,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.institutions.destroy', $institution));

        $response->assertRedirect(route('admin.institutions.index'));

        $this->assertDatabaseMissing('institutions', [
            'id' => $institution->id,
        ]);
    }
}
