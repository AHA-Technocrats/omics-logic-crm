<?php

namespace AHATechnocrats\Admin\DataGrids\OmicsLogic;

use AHATechnocrats\DataGrid\DataGrid;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditLogDataGrid extends DataGrid
{
    /**
     * Actions that appear in the audit trail beyond the automatic CRUD verbs.
     *
     * @var array<int, string>
     */
    protected array $knownActions = [
        'created',
        'updated',
        'deleted',
        'merge_contacts',
        'keep_contacts_separate',
        'dismiss_merge_match',
        'connector_sync',
        'connector_configure',
        'undo',
    ];

    /**
     * Entity types recorded outside the config-driven registry.
     *
     * @var array<string, string>
     */
    protected array $manualEntities = [
        'connector' => 'Connector',
        'merge_review_pair' => 'Merge pair',
    ];

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('omics_audit_logs')
            ->leftJoin('users', function ($join) {
                $join->on('omics_audit_logs.actor_id', '=', 'users.id')
                    ->where('omics_audit_logs.actor_type', '=', 'user');
            })
            ->select(
                'omics_audit_logs.id',
                'omics_audit_logs.created_at',
                'omics_audit_logs.action',
                'omics_audit_logs.event',
                'omics_audit_logs.description',
                'omics_audit_logs.entity_type',
                'omics_audit_logs.entity_id',
                'omics_audit_logs.before',
                'omics_audit_logs.after',
                'omics_audit_logs.is_reversible',
                'omics_audit_logs.reversed_at',
                'omics_audit_logs.ip_address',
                'users.name as actor_name',
                'omics_audit_logs.actor_type',
            );

        $this->addFilter('id', 'omics_audit_logs.id');
        $this->addFilter('action', 'omics_audit_logs.action');
        $this->addFilter('entity_type', 'omics_audit_logs.entity_type');
        $this->addFilter('actor_name', 'users.name');
        $this->addFilter('description', 'omics_audit_logs.description');
        $this->addFilter('created_at', 'omics_audit_logs.created_at');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('omicslogic::app.audit.when'),
            'type' => 'date',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'closure' => fn ($row) => core()->formatDate($row->created_at, 'd M Y H:i'),
        ]);

        $this->addColumn([
            'index' => 'actor_name',
            'label' => trans('omicslogic::app.audit.actor'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'closure' => function ($row) {
                if ($row->actor_name) {
                    return e($row->actor_name);
                }

                return '<span class="text-gray-400">'.e($row->actor_type === 'system'
                    ? trans('omicslogic::app.audit.system')
                    : trans('omicslogic::app.fields.unassigned')).'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'action',
            'label' => trans('omicslogic::app.audit.action'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->actionOptions(),
            'closure' => fn ($row) => $this->actionBadge($row->action),
        ]);

        $this->addColumn([
            'index' => 'entity_type',
            'label' => trans('omicslogic::app.audit.entity'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->entityOptions(),
            'closure' => function ($row) {
                if (! $row->entity_type) {
                    return '—';
                }

                $label = e($this->entityLabel($row->entity_type));
                $id = $row->entity_id ? '<span class="text-gray-400"> #'.e($row->entity_id).'</span>' : '';

                return $label.$id;
            },
        ]);

        $this->addColumn([
            'index' => 'description',
            'label' => trans('omicslogic::app.audit.details'),
            'type' => 'string',
            'searchable' => true,
            'closure' => function ($row) {
                if (! empty($row->description)) {
                    return e($row->description);
                }

                return $this->fallbackDescription($row);
            },
        ]);
    }

    public function prepareActions(): void
    {
        $this->addAction([
            'index' => 'view',
            'icon' => 'icon-eye',
            'title' => trans('omicslogic::app.audit.view'),
            'method' => 'GET',
            'url' => fn ($row) => route('admin.omics.audit.view', $row->id),
        ]);

        if (bouncer()->hasPermission('audit_log.undo')) {
            $this->addAction([
                'index' => 'undo',
                'icon' => 'icon-edit',
                'title' => trans('omicslogic::app.audit.undo'),
                'method' => 'POST',
                'url' => fn ($row) => route('admin.omics.audit.undo', $row->id),
                'condition' => fn ($row) => (bool) $row->is_reversible && empty($row->reversed_at),
            ]);
        }
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    protected function actionOptions(): array
    {
        return collect($this->knownActions)
            ->map(fn (string $action) => [
                'label' => $this->actionLabel($action),
                'value' => $action,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    protected function entityOptions(): array
    {
        $entities = collect(config('audit.entities', []))
            ->map(fn ($config, $key) => [
                'label' => $config['label'] ?? Str::headline($key),
                'value' => $key,
            ]);

        $manual = collect($this->manualEntities)
            ->map(fn ($label, $key) => ['label' => $label, 'value' => $key]);

        return $entities->merge($manual)
            ->sortBy('label')
            ->values()
            ->all();
    }

    protected function entityLabel(string $type): string
    {
        return config("audit.entities.$type.label")
            ?? $this->manualEntities[$type]
            ?? Str::headline($type);
    }

    protected function actionLabel(string $action): string
    {
        $key = 'omicslogic::app.audit.actions.'.$action;
        $translated = trans($key);

        return $translated === $key ? Str::headline($action) : $translated;
    }

    protected function actionBadge(string $action): string
    {
        $class = match ($action) {
            'created' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
            'updated', 'connector_configure' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100',
            'deleted' => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-100',
            'merge_contacts' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100',
            'connector_sync' => 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-100',
            'undo' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-100',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200',
        };

        return '<span class="rounded-full px-2 py-0.5 text-xs font-semibold '.$class.'">'
            .e($this->actionLabel($action)).'</span>';
    }

    protected function fallbackDescription($row): string
    {
        $after = json_decode($row->after ?? 'null', true);

        if (is_array($after) && isset($after['name'])) {
            return e($after['name']);
        }

        return e($this->actionLabel($row->action));
    }
}
