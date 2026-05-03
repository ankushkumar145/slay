<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LoveGuruApiTest extends TestCase
{
    public function test_love_guru_chat_returns_structured_advice(): void
    {
        config([
            'services.groq.key' => 'test-key',
            'services.groq.base_url' => 'https://api.groq.com/openai/v1',
            'services.groq.model' => 'openai/gpt-oss-20b',
        ]);

        Http::fake([
            'api.groq.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'answer' => 'Reply warmly, but ask for a clear plan instead of guessing.',
                                'suggested_texts' => [
                                    'I liked talking to you. Want to pick a day this week?',
                                ],
                                'watch_out' => 'Do not chase vague energy for too long.',
                                'next_step' => 'Send one clear message and watch for follow-through.',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->postJson('/api/guru/chat', [
            'message' => 'They said they want to meet but never pick a day. What should I text?',
        ])
            ->assertOk()
            ->assertJsonPath('data.answer', 'Reply warmly, but ask for a clear plan instead of guessing.')
            ->assertJsonStructure([
                'data' => [
                    'answer',
                    'suggested_texts',
                    'watch_out',
                    'next_step',
                ],
            ]);

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer test-key')
            && $request['response_format']['type'] === 'json_schema');
    }
}
