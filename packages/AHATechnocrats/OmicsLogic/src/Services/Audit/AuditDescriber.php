<?php

namespace AHATechnocrats\OmicsLogic\Services\Audit;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns a structured audit change into a short, human-readable sentence.
 */
class AuditDescriber
{
    /**
     * Maximum number of changed fields listed inline before truncating.
     */
    protected const MAX_FIELDS = 6;

    /**
     * @param  array{before: array<string, mixed>, after: array<string, mixed>}  $changes
     */
    public function describe(
        string $entityKey,
        string $entityLabel,
        string $operation,
        ?int $id,
        ?string $name,
        array $changes = ['before' => [], 'after' => []],
    ): string {
        $title = $this->title($id, $name);

        return match ($operation) {
            'created' => trans('omicslogic::app.audit.messages.created', [
                'entity' => $entityLabel,
                'title' => $title,
            ]),
            'deleted' => trans('omicslogic::app.audit.messages.deleted', [
                'entity' => $entityLabel,
                'title' => $title,
            ]),
            default => $this->describeUpdate($entityKey, $entityLabel, $title, $changes),
        };
    }

    /**
     * @param  array{before: array<string, mixed>, after: array<string, mixed>}  $changes
     */
    protected function describeUpdate(string $entityKey, string $entityLabel, string $title, array $changes): string
    {
        $after = $changes['after'] ?? [];

        if ($entityKey === 'lead' && array_key_exists('lead_pipeline_stage_id', $after)) {
            return trans('omicslogic::app.audit.messages.stage-moved', [
                'entity' => $entityLabel,
                'title' => $title,
                'stage' => $this->stageName($after['lead_pipeline_stage_id']),
            ]);
        }

        $fields = array_keys($after);

        if (empty($fields)) {
            return trans('omicslogic::app.audit.messages.updated', [
                'entity' => $entityLabel,
                'title' => $title,
            ]);
        }

        return trans('omicslogic::app.audit.messages.updated-fields', [
            'entity' => $entityLabel,
            'title' => $title,
            'fields' => $this->fieldList($fields),
        ]);
    }

    protected function title(?int $id, ?string $name): string
    {
        $parts = [];

        if ($id !== null) {
            $parts[] = '#'.$id;
        }

        if (! empty($name)) {
            $parts[] = Str::limit((string) $name, 60);
        }

        return $parts ? implode(' · ', $parts) : '';
    }

    /**
     * @param  array<int, string>  $fields
     */
    protected function fieldList(array $fields): string
    {
        $labels = array_map([$this, 'humanize'], $fields);

        if (count($labels) > self::MAX_FIELDS) {
            $extra = count($labels) - self::MAX_FIELDS;
            $labels = array_slice($labels, 0, self::MAX_FIELDS);
            $labels[] = trans('omicslogic::app.audit.messages.and-more', ['count' => $extra]);
        }

        return implode(', ', $labels);
    }

    protected function humanize(string $field): string
    {
        $field = preg_replace('/_id$/', '', $field) ?: $field;

        return Str::of($field)->replace('_', ' ')->title()->toString();
    }

    protected function stageName(mixed $stageId): string
    {
        if (empty($stageId)) {
            return (string) trans('omicslogic::app.audit.messages.unknown-stage');
        }

        try {
            $name = DB::table('lead_pipeline_stages')->where('id', $stageId)->value('name');
        } catch (\Throwable $e) {
            $name = null;
        }

        return $name ?: (string) $stageId;
    }
}
