<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoveGuruService
{
    public function chat(array $input, array $fallback): array
    {
        $apiKey = config('services.groq.key');

        if (! $apiKey) {
            return $fallback;
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(25)
                ->post(rtrim(config('services.groq.base_url'), '/').'/chat/completions', [
                    'model' => config('services.groq.model'),
                    'temperature' => 0.55,
                    'max_completion_tokens' => 1200,
                    'response_format' => [
                        'type' => 'json_schema',
                        'json_schema' => [
                            'name' => 'love_guru_reply',
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
                            'content' => json_encode($input, JSON_UNESCAPED_SLASHES),
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('Love Guru generation failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $fallback;
            }

            $content = $response->json('choices.0.message.content');
            $decoded = is_string($content) ? json_decode($content, true) : null;

            return is_array($decoded) ? array_merge($fallback, array_intersect_key($decoded, $fallback)) : $fallback;
        } catch (\Throwable $exception) {
            Log::warning('Love Guru generation exception.', [
                'message' => $exception->getMessage(),
            ]);

            return $fallback;
        }
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are Love Guru inside SLAY, a dating advice chat feature.
Give practical, warm, emotionally honest advice for texting, dating uncertainty, date prep, boundaries, and profile-related romantic situations.
Do not encourage manipulation, stalking, pressure, jealousy games, or disrespecting consent.
If the situation sounds unsafe, coercive, or abusive, tell the user to prioritize safety and trusted support.
Keep answers concise and useful. Return only valid JSON matching the schema.
PROMPT;
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'answer' => ['type' => 'string'],
                'suggested_texts' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'watch_out' => ['type' => 'string'],
                'next_step' => ['type' => 'string'],
            ],
            'required' => ['answer', 'suggested_texts', 'watch_out', 'next_step'],
        ];
    }
}
