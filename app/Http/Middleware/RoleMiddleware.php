<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $roleAliases = [
            'staff' => 'service_staff',
            'service' => 'service_staff',
            'service_staff' => 'service_staff',

            'kitchen' => 'kitchen_staff',
            'kitchen_staff' => 'kitchen_staff',

            'table' => 'table_customer',
            'tablet' => 'table_customer',
            'table_customer' => 'table_customer',

            'admin' => 'admin',
            'customer' => 'customer',
        ];

        $userRole = auth()->user()->role;
        $normalizedUserRole = $roleAliases[$userRole] ?? $userRole;

        $allowedRoles = collect($roles)
            ->map(function ($role) use ($roleAliases) {
                return $roleAliases[$role] ?? $role;
            })
            ->toArray();

        if (!in_array($normalizedUserRole, $allowedRoles, true)) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}