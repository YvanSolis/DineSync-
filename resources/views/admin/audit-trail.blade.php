@extends('layouts.admin')

@section('content')
<style>
    .audit-panel {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(226, 232, 240, 0.95);
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.055);
        backdrop-filter: blur(14px);
    }

    .audit-log-card {
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease,
            border-color 0.2s ease;
    }

    .audit-log-card:hover {
        transform: translateY(-1px);
        border-color: rgba(251, 146, 60, 0.35);
        box-shadow: 0 16px 34px rgba(15, 23, 42, 0.07);
    }

    .audit-change-row + .audit-change-row {
        border-top: 1px solid rgb(241 245 249);
    }
</style>

<div class="space-y-5 sm:space-y-6">

    {{-- Header --}}
    <section class="audit-panel overflow-hidden rounded-[28px]">
        <div class="relative px-5 py-6 sm:px-7">
            <div class="absolute inset-0 bg-gradient-to-r from-orange-50 via-white to-amber-50"></div>
            <div class="absolute -right-16 -top-20 h-52 w-52 rounded-full bg-orange-100/70"></div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Staff Activity Logs
                    </span>

                    <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                        Audit Trail
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        View the actions performed by service staff and kitchen staff.
                    </p>
                </div>

                <div class="rounded-2xl border border-orange-100 bg-white/90 px-4 py-3 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">
                        Total Logs
                    </p>

                    <p class="mt-1 text-xl font-black text-slate-950">
                        {{ number_format($logs->total()) }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Simple Summary --}}
    <section class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="audit-panel rounded-[22px] p-4 sm:p-5">
            <p class="text-[10px] font-black uppercase tracking-[0.13em] text-slate-400">
                Activities Today
            </p>

            <p class="mt-2 text-2xl font-black text-orange-600 sm:text-3xl">
                {{ number_format($todayCount) }}
            </p>

            <p class="mt-1 text-xs text-slate-500">
                Staff actions recorded today
            </p>
        </div>

        <div class="audit-panel rounded-[22px] p-4 sm:p-5">
            <p class="text-[10px] font-black uppercase tracking-[0.13em] text-slate-400">
                Latest Activity
            </p>

            <p class="mt-2 text-sm font-black text-emerald-600 sm:text-base">
                {{ $lastActivity?->created_at?->timezone('Asia/Manila')->diffForHumans() ?? 'No activity yet' }}
            </p>

            <p class="mt-1 truncate text-xs text-slate-500">
                {{ $lastActivity?->user_name ?? 'No staff activity yet' }}
            </p>
        </div>
    </section>

    {{-- Logs --}}
    <section class="audit-panel overflow-hidden rounded-[26px]">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-6">
            <div>
                <h2 class="text-lg font-black text-slate-950">
                    Staff Activity
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Newest activity appears first.
                </p>
            </div>

            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                {{ number_format($logs->total()) }} logs
            </span>
        </div>

        <div class="space-y-3 p-4 sm:p-5">
            @forelse ($logs as $log)
                @php
                    $role = strtolower((string) ($log->user_role ?? 'staff'));

                    $roleLabel = match ($role) {
                        'service_staff', 'staff' => 'Service Staff',
                        'kitchen_staff', 'kitchen' => 'Kitchen Staff',
                        default => ucwords(str_replace('_', ' ', $role)),
                    };

                    $roleClass = match ($role) {
                        'service_staff', 'staff' => 'border-orange-200 bg-orange-50 text-orange-700',
                        'kitchen_staff', 'kitchen' => 'border-yellow-200 bg-yellow-50 text-yellow-700',
                        default => 'border-slate-200 bg-slate-50 text-slate-700',
                    };

                    $initials = collect(explode(' ', trim($log->user_name ?? 'Staff')))
                        ->filter()
                        ->take(2)
                        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                        ->implode('');

                    $logPayload = [
                        'id' => $log->id,
                        'user_name' => $log->user_name ?? 'Staff',
                        'user_role' => $roleLabel,
                        'action' => $log->action,
                        'description' => $log->description,
                        'auditable_type' => $log->auditable_type,
                        'auditable_id' => $log->auditable_id,
                        'old_values' => $log->old_values ?? [],
                        'new_values' => $log->new_values ?? [],
                        'ip_address' => $log->ip_address,
                        'user_agent' => $log->user_agent,
                        'created_at' => $log->created_at
                            ? $log->created_at
                                ->timezone('Asia/Manila')
                                ->format('M d, Y h:i A')
                            : null,
                    ];
                @endphp

                <article class="audit-log-card rounded-[22px] border border-slate-200 bg-white p-4 sm:p-5">
                    <div class="flex items-start gap-3 sm:gap-4">

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-orange-100 text-xs font-black text-orange-700">
                            {{ $initials ?: 'ST' }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-black text-slate-950">
                                            {{ $log->user_name ?? 'Staff' }}
                                        </p>

                                        <span class="rounded-full border px-3 py-1 text-[10px] font-black {{ $roleClass }}">
                                            {{ $roleLabel }}
                                        </span>
                                    </div>

                                    <p class="mt-3 text-sm font-bold leading-6 text-slate-800 sm:text-base">
                                        {{ $log->description }}
                                    </p>

                                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                        <span class="font-bold text-slate-700">
                                            {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                        </span>

                                        <span>•</span>

                                        <span>
                                            {{ $log->created_at?->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                                        </span>

                                        @if ($log->auditable_id)
                                            <span>•</span>

                                            <span>
                                                Record #{{ $log->auditable_id }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="audit-details-button inline-flex min-h-[42px] shrink-0 items-center justify-center rounded-2xl border border-orange-200 bg-orange-50 px-4 py-2 text-xs font-black text-orange-600 hover:bg-orange-100"
                                    data-log="{{ e(json_encode($logPayload)) }}"
                                >
                                    View Details
                                </button>

                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-xs font-black text-slate-400 shadow-sm">
                        LOG
                    </div>

                    <h3 class="mt-4 font-black text-slate-950">
                        No staff activity yet
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Staff actions will appear here automatically.
                    </p>
                </div>
            @endforelse
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $logs->links() }}
            </div>
        @endif
    </section>
</div>

{{-- Details Modal --}}
<div
    id="auditDetailsModal"
    class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 p-3 backdrop-blur-sm sm:p-5"
>
    <div class="max-h-[94vh] w-full max-w-4xl overflow-y-auto rounded-[28px] bg-white shadow-2xl">

        <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-100 bg-white px-5 py-5 sm:px-6">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-orange-600">
                    Staff Activity
                </p>

                <h3
                    id="auditModalTitle"
                    class="mt-1 text-lg font-black text-slate-950 sm:text-xl"
                >
                    Activity Details
                </h3>
            </div>

            <button
                type="button"
                onclick="closeAuditModal()"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-xl text-slate-500 hover:bg-slate-50"
            >
                &times;
            </button>
        </div>

        <div
            id="auditModalBody"
            class="space-y-5 p-5 sm:p-6"
        ></div>
    </div>
</div>

<script>
    function escapeAuditText(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function formatAuditValue(value) {
        if (value === null || value === undefined || value === '') {
            return '—';
        }

        if (typeof value === 'boolean') {
            return value ? 'Yes' : 'No';
        }

        if (typeof value === 'object') {
            return JSON.stringify(value);
        }

        return String(value);
    }

    function friendlyFieldName(key) {
        return String(key || '')
            .replaceAll('_', ' ')
            .replace(/\b\w/g, character => character.toUpperCase());
    }

    function buildChangeRows(oldValues, newValues) {
        const oldData = oldValues || {};
        const newData = newValues || {};

        const keys = [
            ...new Set([
                ...Object.keys(oldData),
                ...Object.keys(newData)
            ])
        ];

        if (keys.length === 0) {
            return `
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center">
                    <p class="font-bold text-slate-700">
                        No field changes were recorded.
                    </p>
                </div>
            `;
        }

        return keys.map(key => {
            const oldValue = formatAuditValue(oldData[key]);
            const newValue = formatAuditValue(newData[key]);
            const changed = oldValue !== newValue;

            return `
                <div class="audit-change-row grid grid-cols-1 gap-3 px-4 py-4 md:grid-cols-[170px_1fr_32px_1fr] md:items-center">

                    <div class="text-xs font-black uppercase tracking-wide text-slate-500">
                        ${escapeAuditText(friendlyFieldName(key))}
                    </div>

                    <div class="rounded-xl border ${changed ? 'border-red-100 bg-red-50 text-red-700' : 'border-slate-100 bg-slate-50 text-slate-600'} px-3 py-2 text-xs break-words">
                        ${escapeAuditText(oldValue)}
                    </div>

                    <div class="hidden text-center text-sm font-black text-slate-300 md:block">
                        →
                    </div>

                    <div class="rounded-xl border ${changed ? 'border-emerald-100 bg-emerald-50 text-emerald-700' : 'border-slate-100 bg-slate-50 text-slate-600'} px-3 py-2 text-xs break-words">
                        ${escapeAuditText(newValue)}
                    </div>

                </div>
            `;
        }).join('');
    }

    document
        .querySelectorAll('.audit-details-button')
        .forEach(button => {
            button.addEventListener('click', function () {
                const log = JSON.parse(this.dataset.log);

                document.getElementById('auditModalTitle').textContent =
                    log.description || 'Activity Details';

                document.getElementById('auditModalBody').innerHTML = `
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">
                                Staff
                            </p>

                            <p class="mt-2 font-black text-slate-950">
                                ${escapeAuditText(log.user_name || 'Staff')}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                ${escapeAuditText(log.user_role || '')}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">
                                Action
                            </p>

                            <p class="mt-2 font-black text-slate-950">
                                ${escapeAuditText(friendlyFieldName(log.action))}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                Record #${escapeAuditText(log.auditable_id || '—')}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-[10px] font-black uppercase tracking-wide text-slate-400">
                                Date & Time
                            </p>

                            <p class="mt-2 text-sm font-black text-slate-950">
                                ${escapeAuditText(log.created_at || '—')}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                ${escapeAuditText(log.ip_address || 'IP unavailable')}
                            </p>
                        </div>

                    </div>

                    <div class="overflow-hidden rounded-[22px] border border-slate-200">
                        <div class="grid grid-cols-1 border-b border-slate-100 bg-slate-50 px-4 py-3 md:grid-cols-[170px_1fr_32px_1fr]">
                            <p class="text-xs font-black text-slate-500">Field</p>
                            <p class="hidden text-xs font-black text-red-600 md:block">Previous</p>
                            <span></span>
                            <p class="hidden text-xs font-black text-emerald-600 md:block">New</p>
                        </div>

                        ${buildChangeRows(
                            log.old_values,
                            log.new_values
                        )}
                    </div>
                `;

                const modal =
                    document.getElementById('auditDetailsModal');

                modal.classList.remove('hidden');
                modal.classList.add('flex');

                document.body.classList.add('overflow-hidden');
            });
        });

    function closeAuditModal() {
        const modal =
            document.getElementById('auditDetailsModal');

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        document.body.classList.remove('overflow-hidden');
    }

    document
        .getElementById('auditDetailsModal')
        .addEventListener('click', function (event) {
            if (event.target === this) {
                closeAuditModal();
            }
        });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAuditModal();
        }
    });
</script>
@endsection
