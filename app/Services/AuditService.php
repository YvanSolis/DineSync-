<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AuditService
{
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'remember_token',
        'token',
        'access_token',
        'refresh_token',
        'api_key',
        'secret',
        'client_secret',
        'authorization',
        'card_number',
        'cvv',
        'cvc',
    ];

    public static function record(
        string $module,
        string $action,
        string $description,
        Model|string|null $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        ?Request $request = null
    ): ?AuditLog {
        try {
            $request ??= request();

            /*
            |--------------------------------------------------------------------------
            | Resolve the actual logged-in actor
            |--------------------------------------------------------------------------
            | request()->user() works for normal authenticated web requests.
            | The web guard fallback handles authenticated AJAX/API-style requests
            | coming from the Laravel admin and service staff interfaces.
            */
            $user = $request?->user()
                ?? Auth::guard('web')->user()
                ?? Auth::user();

            [$auditableType, $auditableId] = self::resolveAuditable($auditable);

            return AuditLog::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? 'System',
                'user_role' => $user?->role ?? 'system',
                'module' => trim($module),
                'action' => trim($action),
                'description' => trim($description),
                'auditable_type' => $auditableType,
                'auditable_id' => $auditableId,
                'old_values' => self::sanitize($oldValues),
                'new_values' => self::sanitize($newValues),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            // Audit logging must never stop the original business action.
            return null;
        }
    }

    private static function resolveAuditable(
        Model|string|null $auditable
    ): array {
        if ($auditable instanceof Model) {
            return [
                $auditable::class,
                $auditable->getKey(),
            ];
        }

        if (is_string($auditable) && trim($auditable) !== '') {
            return [
                trim($auditable),
                null,
            ];
        }

        return [
            null,
            null,
        ];
    }

    private static function sanitize(array $values): array
    {
        $sanitized = Arr::except(
            $values,
            self::SENSITIVE_KEYS
        );

        array_walk_recursive(
            $sanitized,
            function (&$value, $key) {
                if (
                    in_array(
                        strtolower((string) $key),
                        self::SENSITIVE_KEYS,
                        true
                    )
                ) {
                    $value = '[REDACTED]';
                }
            }
        );

        return $sanitized;
    }
}
