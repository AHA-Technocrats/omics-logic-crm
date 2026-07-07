<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Person;
use AHATechnocrats\OmicsLogic\Models\AuditLog;
use AHATechnocrats\OmicsLogic\Models\MergeReviewPair;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogger
{
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $before = null,
        ?array $after = null,
        bool $reversible = false,
        ?string $actorType = null,
        ?int $actorId = null,
        ?string $event = null,
        ?string $description = null,
    ): AuditLog {
        $user = Auth::guard('user')->user();

        return AuditLog::query()->create(array_merge([
            'actor_type' => $actorType ?? ($user ? 'user' : 'system'),
            'actor_id' => $actorId ?? $user?->id,
            'action' => $action,
            'event' => $event,
            'description' => $description,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before' => $before,
            'after' => $after,
            'is_reversible' => $reversible,
        ], self::context()));
    }

    /**
     * Capture the request context (route, IP, user agent) when available.
     *
     * @return array<string, string|null>
     */
    protected static function context(): array
    {
        $request = request();

        if (! $request || app()->runningInConsole()) {
            return [];
        }

        return [
            'route' => optional($request->route())->getName() ?? $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ];
    }

    public static function undo(AuditLog $log): bool
    {
        if (! $log->is_reversible || $log->reversed_at) {
            return false;
        }

        if ($log->action === 'merge_contacts' && $log->entity_type === 'merge_review_pair') {
            $after = $log->after ?? [];
            $duplicateId = $after['duplicate_person_id'] ?? null;

            if ($duplicateId) {
                Person::query()
                    ->where('id', $duplicateId)
                    ->update(['merged_into_id' => null]);
            }

            if (! empty($after['pair_id'])) {
                MergeReviewPair::query()
                    ->where('id', $after['pair_id'])
                    ->update(['status' => 'pending', 'resolved_by' => null, 'resolved_at' => null]);
            }
        }

        $log->reversed_at = now();
        $log->save();

        self::log('undo', $log->entity_type, $log->entity_id, null, [
            'undone_action' => $log->action,
            'audit_log_id' => $log->id,
        ]);

        return true;
    }
}
