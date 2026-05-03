<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DatingKitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->datingKits()->latest()->get(),
        ]);
    }

    public function latest(Request $request): JsonResponse
    {
        $kit = $request->user()->datingKits()->latest()->first();

        if (!$kit) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => $kit,
        ]);
    }
}
