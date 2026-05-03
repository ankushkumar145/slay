<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_update_profile_and_read_session(): void
    {
        $auth = $this->postJson('/api/auth/register', [
            'name' => 'Alex',
            'email' => 'alex@example.com',
            'password' => 'password123',
        ])
            ->assertCreated()
            ->assertJsonPath('data.user.name', 'Alex')
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'profile',
                    ],
                ],
            ])
            ->json('data');

        $this->withToken($auth['token'])->putJson('/api/profile', [
            'age' => 24,
            'goal' => 'better_matches',
            'current_bio' => 'Coffee walks and good food.',
            'target_match' => 'serious_relationship',
            'export_style' => 'hinge',
        ])
            ->assertOk()
            ->assertJsonPath('data.age', 24)
            ->assertJsonPath('data.export_style', 'hinge');

        $this->withToken($auth['token'])->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.profile.age', 24);
    }
}
