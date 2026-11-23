<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_preferences_can_be_saved(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('preferences.update'), [
            'target_roles' => 'Software developer, IT executive',
            'preferred_locations' => 'Kuching',
            'willing_to_relocate' => '1',
            'digest_enabled' => '1',
            'digest_min_score' => 60,
        ])->assertRedirect();

        $user->refresh();
        $this->assertSame('Software developer, IT executive', $user->target_roles);
        $this->assertSame('Kuching', $user->preferred_locations);
        $this->assertTrue($user->willing_to_relocate);
        $this->assertTrue($user->digest_enabled);
        $this->assertSame(60, $user->digest_min_score);
    }

    public function test_unticked_checkboxes_turn_the_options_off(): void
    {
        $user = User::factory()->create(['willing_to_relocate' => true, 'digest_enabled' => true]);

        $this->actingAs($user)->patch(route('preferences.update'), ['digest_min_score' => 50]);

        $user->refresh();
        $this->assertFalse($user->willing_to_relocate);
        $this->assertFalse($user->digest_enabled);
    }

    public function test_the_minimum_score_must_be_between_0_and_100(): void
    {
        $this->actingAs(User::factory()->create())
            ->patch(route('preferences.update'), ['digest_min_score' => 150])
            ->assertSessionHasErrors('digest_min_score');
    }

    public function test_the_page_renders(): void
    {
        $this->actingAs(User::factory()->create())->get(route('preferences.edit'))->assertOk();
    }
}
