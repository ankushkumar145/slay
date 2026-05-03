<?php

namespace App\Http\Controllers;

use App\Services\LoveGuruService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoveGuruController extends Controller
{
    public function chat(Request $request, LoveGuruService $guru): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:3', 'max:1200'],
            'context' => ['nullable', 'array', 'max:8'],
            'context.*.role' => ['required_with:context', 'string', 'in:user,assistant'],
            'context.*.content' => ['required_with:context', 'string', 'max:1200'],
        ]);

        $fallback = [
            'answer' => 'Give the situation a little space, then respond clearly and warmly. Aim for directness over guessing games.',
            'suggested_texts' => [
                'I liked talking to you and I would be up for seeing where this goes. Want to plan something this week?',
                'I am interested, but I also like clear energy. What are you looking for here?',
            ],
            'watch_out' => 'Do not over-invest in mixed signals. Consistency matters more than intensity.',
            'next_step' => 'Send one calm message, then watch whether their actions match their words.',
        ];

        return response()->json([
            'data' => $guru->chat([
                'message' => $validated['message'],
                'context' => $validated['context'] ?? [],
                'user_profile' => $request->user()?->profile,
            ], $fallback),
        ]);
    }
}
