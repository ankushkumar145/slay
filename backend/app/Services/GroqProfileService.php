<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqProfileService
{
    public function generate(array $input, array $fallback): ?array
    {
        $apiKey = config('services.groq.key');

        if (! $apiKey) {
            return null;
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(25)
                ->post(rtrim(config('services.groq.base_url'), '/').'/chat/completions', [
                    'model' => config('services.groq.model'),
                    'temperature' => 0.6,
                    'max_completion_tokens' => 3200,
                    'response_format' => [
                        'type' => 'json_schema',
                        'json_schema' => [
                            'name' => 'slay_profile_intelligence',
                            'strict' => true,
                            'schema' => $this->schema(),
                        ],
                    ],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->systemPrompt(),
                        ],
                        [
                            'role' => 'user',
                            'content' => json_encode([
                                'input' => $input,
                                'fallback_shape' => $fallback,
                            ], JSON_UNESCAPED_SLASHES),
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('Groq profile generation failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $content = $response->json('choices.0.message.content');

            if (! is_string($content)) {
                return null;
            }

            $decoded = json_decode($content, true);

            return is_array($decoded) ? $this->normalize($decoded, $fallback) : null;
        } catch (\Throwable $exception) {
            Log::warning('Groq profile generation exception.', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are SLAY, a Gen Z dating profile intelligence engine.
Create output that feels specific, warm, romantic, useful, and copy-paste ready.
Avoid therapy-speak, cringe pickup lines, negging, manipulation, stereotypes, or overpromising matches.
Use the user's actual answers and current bio. Be honest about weak signals, but keep the tone confidence-building.
Return only valid JSON matching the requested schema.
PROMPT;
    }

    private function normalize(array $ai, array $fallback): array
    {
        $merged = array_merge($fallback, array_intersect_key($ai, $fallback));
        $merged['scores'] = $fallback['scores'];
        $merged['before_profile'] = $fallback['before_profile'];
        $merged['ai_generated'] = true;

        return $merged;
    }

    private function schema(): array
    {
        $textItem = [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'label' => ['type' => 'string'],
                'value' => ['type' => 'string'],
            ],
            'required' => ['label', 'value'],
        ];

        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'archetype' => ['type' => 'string'],
                'headline' => ['type' => 'string'],
                'perception' => ['type' => 'string'],
                'match_fit' => ['type' => 'string'],
                'optimized_bio' => ['type' => 'string'],
                'bio_variants' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'style' => ['type' => 'string'],
                            'text' => ['type' => 'string'],
                        ],
                        'required' => ['style', 'text'],
                    ],
                ],
                'profile_diagnosis' => [
                    'type' => 'array',
                    'items' => $textItem,
                ],
                'target_strategy' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'target' => ['type' => 'string'],
                        'lead_signal' => ['type' => 'string'],
                        'avoid' => ['type' => 'string'],
                    ],
                    'required' => ['target', 'lead_signal', 'avoid'],
                ],
                'platform_exports' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'platform' => ['type' => 'string'],
                            'recommended' => ['type' => 'boolean'],
                            'bio' => ['type' => 'string'],
                            'prompt' => ['type' => 'string'],
                        ],
                        'required' => ['platform', 'recommended', 'bio', 'prompt'],
                    ],
                ],
                'prompt_answers' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'prompt' => ['type' => 'string'],
                            'answer' => ['type' => 'string'],
                        ],
                        'required' => ['prompt', 'answer'],
                    ],
                ],
                'personality_map' => [
                    'type' => 'array',
                    'items' => $textItem,
                ],
                'photo_direction' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'suggestions' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'share_line' => ['type' => 'string'],
                'export_text' => ['type' => 'string'],
            ],
            'required' => [
                'archetype',
                'headline',
                'perception',
                'match_fit',
                'optimized_bio',
                'bio_variants',
                'profile_diagnosis',
                'target_strategy',
                'platform_exports',
                'prompt_answers',
                'personality_map',
                'photo_direction',
                'suggestions',
                'share_line',
                'export_text',
            ],
        ];
    }
}
