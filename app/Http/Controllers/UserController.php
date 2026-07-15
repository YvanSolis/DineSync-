<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private array $allowedRoles = [
        'admin',
        'service_staff',
        'kitchen_staff',
        'customer',
        'table_customer',
    ];

    public function index()
    {
        return response()->json(
            User::select('id', 'name', 'email', 'role', 'created_at')
                ->latest()
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::in($this->allowedRoles)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        AuditService::record(
            module: 'Users',
            action: 'create',
            description: "Created user account {$user->name}.",
            auditable: $user,
            oldValues: [],
            newValues: $user->only(['name', 'email', 'role']),
            request: $request
        );

        return response()->json([
            'message' => 'User added successfully.',
            'user' => $user,
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['sometimes', 'required', 'string', Rule::in($this->allowedRoles)],
        ]);

        $oldValues = $user->only(['name', 'email', 'role']);

        $passwordChanged = false;

        if (array_key_exists('password', $validated)) {
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
                $passwordChanged = true;
            } else {
                unset($validated['password']);
            }
        }

        $user->update($validated);
        $user->refresh();

        $newValues = $user->only(['name', 'email', 'role']);

        if ($passwordChanged) {
            $newValues['password_changed'] = true;
        }

        AuditService::record(
            module: 'Users',
            action: 'update',
            description: "Updated user account {$user->name}.",
            auditable: $user,
            oldValues: $oldValues,
            newValues: $newValues,
            request: $request
        );

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $user,
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        if (auth()->id() === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $oldValues = $user->only(['id', 'name', 'email', 'role']);
        $userName = $user->name;

        $user->delete();

        AuditService::record(
            module: 'Users',
            action: 'delete',
            description: "Deleted user account {$userName}.",
            auditable: User::class,
            oldValues: $oldValues,
            newValues: [],
            request: $request
        );

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
