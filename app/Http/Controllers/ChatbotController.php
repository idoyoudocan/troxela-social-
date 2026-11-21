<?php

namespace App\Http\Controllers;

use App\Support\Chatbot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        return response()->json([
            'reply' => Chatbot::reply($validated['message']),
        ]);
    }
}
