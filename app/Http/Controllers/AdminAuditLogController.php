<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminAuditLogController extends Controller
{
    private array $staffRoles = [
        'staff',
        'service_staff',
        'kitchen',
        'kitchen_staff',
    ];

    private array $allowedModules = [
        'Orders',
        'Payments',
        'Reservations',
        'Tables',
        'Refills',
        'Kitchen',
    ];

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'role' => ['nullable', 'string', 'max:50'],
            'module' => ['nullable', 'string', 'max:80'],
            'action' => ['nullable', 'string', 'max:80'],
            'date_from' => ['nullable', 'date'],
            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ]);

        $baseQuery = AuditLog::query()
            ->whereIn('user_role', $this->staffRoles)
            ->whereIn('module', $this->allowedModules);

        $query = (clone $baseQuery)
            ->select([
                'id',
                'user_id',
                'user_name',
                'user_role',
                'module',
                'action',
                'description',
                'auditable_type',
                'auditable_id',
                'old_values',
                'new_values',
                'ip_address',
                'user_agent',
                'created_at',
            ])
            ->latest('created_at');

        if (!empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");

                if (ctype_digit($search)) {
                    $builder->orWhere('auditable_id', (int) $search);
                }
            });
        }

        if (!empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        if (!empty($validated['role'])) {
            $query->where('user_role', $validated['role']);
        }

        if (!empty($validated['module'])) {
            $query->where('module', $validated['module']);
        }

        if (!empty($validated['action'])) {
            $query->where('action', $validated['action']);
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        // Fewer cards per page keeps the audit screen responsive as logs grow.
        $logs = $query
            ->paginate(10)
            ->withQueryString();

        $todayCount = (clone $baseQuery)
            ->whereDate('created_at', now('Asia/Manila')->toDateString())
            ->count();

        $lastActivity = (clone $baseQuery)
            ->select(['user_name', 'created_at'])
            ->latest('created_at')
            ->first();

        return view('admin.audit-trail', compact(
            'logs',
            'todayCount',
            'lastActivity'
        ));
    }
}
