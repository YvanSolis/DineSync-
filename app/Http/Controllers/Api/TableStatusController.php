<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TableStatusController extends Controller
{
    public function online(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'table_customer') {
            return response()->json([
                'message' => 'Only table customer accounts can mark table online.',
            ], 403);
        }

        $user->update([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'message' => 'Table marked as online.',
            'table' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'table_number' => $user->table_number,
                'is_online' => $user->is_online,
                'last_seen_at' => $user->last_seen_at,
            ],
        ]);
    }

    public function offline(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'table_customer') {
            return response()->json([
                'message' => 'Only table customer accounts can mark table offline.',
            ], 403);
        }

        $user->update([
            'is_online' => false,
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'message' => 'Table marked as offline.',
            'table' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'table_number' => $user->table_number,
                'is_online' => $user->is_online,
                'last_seen_at' => $user->last_seen_at,
            ],
        ]);
    }

    public function heartbeat(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'table_customer') {
            return response()->json([
                'message' => 'Only table customer accounts can send heartbeat.',
            ], 403);
        }

        $user->update([
            'is_online' => true,
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'message' => 'Table heartbeat updated.',
            'table' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'table_number' => $user->table_number,
                'is_online' => $user->is_online,
                'last_seen_at' => $user->last_seen_at,
            ],
        ]);
    }
}