<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->profile()->firstOrCreate([]),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'age' => ['required', 'integer', 'min:18', 'max:80'],
            'goal' => ['required', 'string', 'max:120'],
            'current_bio' => ['nullable', 'string', 'max:600'],
            'target_match' => ['required', 'string', 'max:80'],
            'export_style' => ['required', 'string', 'max:80'],
        ]);

        $profile = $request->user()->profile()->updateOrCreate([], $validated);

        return response()->json([
            'data' => $profile,
        ]);
    }
}
