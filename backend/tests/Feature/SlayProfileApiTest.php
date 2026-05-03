<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SlayProfileApiTest extends TestCase
{
    public function test_questions_endpoint_returns_the_mvp_question_set(): void
    {
        $this->getJson('/api/questions')
            ->assertOk()
            ->assertJsonCount(25, 'data')
            ->assertJsonPath('data.0.id', 'social_battery');
    }

    public function test_analyze_endpoint_returns_profile_intelligence(): void
    {
        $questions = $this->getJson('/api/questions')->json('data');

        $answers = collect($questions)->map(fn ($question) => [
            'id' => $question['id'],
            'value' => $question['type'] === 'slider'
                ? ($question['default'] ?? 6)
                : $question['options'][0]['value'],
        ])->values()->all();

        $this->postJson('/api/analyze', [
            'profile' => [
                'name' => 'Alex',
                'age' => 24,
                'goal' => 'better_matches',
                'current_bio' => 'I like food, music and deep conversations.',
                'target_match' => 'serious_relationship',
                'export_style' => 'hinge',
            ],
            'answers' => $answers,
        ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'archetype',
                    'headline',
                    'perception',
                    'match_fit',
                    'scores' => [
                        'attractiveness',
                        'approachability',
                        'emotional_depth',
                        'profile_clarity',
                        'conversation_spark',
                    ],
                    'before_profile',
                    'optimized_bio',
                    'bio_variants',
                    'profile_diagnosis',
                    'target_strategy' => [
                        'target',
                        'lead_signal',
                        'avoid',
                    ],
                    'platform_exports',
                    'prompt_answers',
                    'personality_map',
                    'photo_direction',
                    'suggestions',
                    'share_line',
                    'export_text',
                ],
            ]);
    }

    public function test_analyze_endpoint_uses_groq_when_configured(): void
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
                            'content' => json_encode($this->aiPayload()),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $questions = $this->getJson('/api/questions')->json('data');

        $answers = collect($questions)->map(fn ($question) => [
            'id' => $question['id'],
            'value' => $question['type'] === 'slider'
                ? ($question['default'] ?? 6)
                : $question['options'][0]['value'],
        ])->values()->all();

        $this->postJson('/api/analyze', [
            'profile' => [
                'name' => 'Alex',
                'age' => 24,
                'goal' => 'better_matches',
                'current_bio' => 'Food, music, and deep talks.',
                'target_match' => 'soft_romantic',
                'export_style' => 'bumble',
            ],
            'answers' => $answers,
        ])
            ->assertOk()
            ->assertJsonPath('data.ai_generated', true)
            ->assertJsonPath('data.optimized_bio', 'AI rewritten bio that feels specific and warm.');

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer test-key')
            && $request['model'] === 'openai/gpt-oss-20b'
            && $request['response_format']['type'] === 'json_schema');
    }

    private function aiPayload(): array
    {
        return [
            'archetype' => 'Soft Launch Romantic',
            'headline' => 'Warm, witty, and easy to choose.',
            'perception' => 'You read as emotionally available with a playful edge.',
            'match_fit' => 'Best fit: someone steady, warm, and curious.',
            'optimized_bio' => 'AI rewritten bio that feels specific and warm.',
            'bio_variants' => [
                ['style' => 'Soft', 'text' => 'Soft variant.'],
                ['style' => 'Funny', 'text' => 'Funny variant.'],
                ['style' => 'Bold', 'text' => 'Bold variant.'],
            ],
            'profile_diagnosis' => [
                ['label' => 'Current signal', 'value' => 'Too broad.'],
            ],
            'target_strategy' => [
                'target' => 'soft romantic people',
                'lead_signal' => 'Lead with warmth.',
                'avoid' => 'Avoid generic claims.',
            ],
            'platform_exports' => [
                [
                    'platform' => 'Bumble',
                    'recommended' => true,
                    'bio' => 'Bumble bio.',
                    'prompt' => 'Bumble prompt.',
                ],
            ],
            'prompt_answers' => [
                ['prompt' => 'Dating me is like...', 'answer' => 'A soft plot twist.'],
            ],
            'personality_map' => [
                ['label' => 'First impression', 'value' => 'Warm.'],
            ],
            'photo_direction' => [
                'Use one relaxed clear face photo.',
            ],
            'suggestions' => [
                'Add one specific detail.',
            ],
            'share_line' => 'SLAY read: Soft Launch Romantic.',
            'export_text' => 'Full AI export.',
        ];
    }
}
