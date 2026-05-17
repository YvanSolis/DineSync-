<?php

namespace App\Http\Controllers\ServiceStaff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;

class TableMonitoringController extends Controller
{
    public function index()
    {
        $tables = User::where('role', 'table_customer')
            ->orderByRaw('CAST(table_number AS UNSIGNED) ASC')
            ->get()
            ->map(function ($table) {
                $lastSeen = $table->last_seen_at ? Carbon::parse($table->last_seen_at) : null;
                $diffMinutes = $lastSeen ? $lastSeen->diffInMinutes(now()) : null;

                if (! $table->is_online) {
                    $displayStatus = 'offline';
                } elseif ($diffMinutes !== null && $diffMinutes > 2) {
                    $displayStatus = 'inactive';
                } else {
                    $displayStatus = 'online';
                }

                $table->display_status = $displayStatus;
                $table->last_seen_text = $lastSeen ? $lastSeen->diffForHumans() : 'Never';

                return $table;
            });

        $stats = [
            'total' => $tables->count(),
            'online' => $tables->where('display_status', 'online')->count(),
            'inactive' => $tables->where('display_status', 'inactive')->count(),
            'offline' => $tables->where('display_status', 'offline')->count(),
        ];

        return view('service.table-monitoring', compact('tables', 'stats'));
    }
}