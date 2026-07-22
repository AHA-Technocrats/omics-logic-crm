<?php

namespace AHATechnocrats\Admin\DataGrids\OmicsLogic;

use AHATechnocrats\DataGrid\DataGrid;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ConnectorSyncRunDataGrid extends DataGrid
{
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('omics_connector_sync_runs')
            ->leftJoin('omics_connectors', 'omics_connector_sync_runs.connector_id', '=', 'omics_connectors.id')
            ->select(
                'omics_connector_sync_runs.id',
                'omics_connector_sync_runs.started_at',
                'omics_connector_sync_runs.status',
                'omics_connector_sync_runs.rows_total',
                'omics_connector_sync_runs.rows_new',
                'omics_connector_sync_runs.rows_merged',
                'omics_connector_sync_runs.rows_review',
                'omics_connectors.name as connector_name',
                'omics_connectors.type as connector_type'
            );

        $this->addFilter('id', 'omics_connector_sync_runs.id');
        $this->addFilter('connector_name', 'omics_connectors.name');
        $this->addFilter('connector_type', 'omics_connectors.type');
        $this->addFilter('rows_total', 'omics_connector_sync_runs.rows_total');
        $this->addFilter('rows_new', 'omics_connector_sync_runs.rows_new');
        $this->addFilter('rows_merged', 'omics_connector_sync_runs.rows_merged');
        $this->addFilter('rows_review', 'omics_connector_sync_runs.rows_review');
        $this->addFilter('status', 'omics_connector_sync_runs.status');
        $this->addFilter('started_at', 'omics_connector_sync_runs.started_at');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'connector_name',
            'label' => trans('omicslogic::app.connectors.run-columns.source'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                return $row->connector_name ?? '—';
            },
        ]);

        $this->addColumn([
            'index' => 'connector_type',
            'label' => trans('omicslogic::app.connectors.run-columns.type'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                return str_replace('_', ' ', $row->connector_type ?? '—');
            },
        ]);

        $this->addColumn([
            'index' => 'rows_total',
            'label' => trans('omicslogic::app.connectors.run-columns.rows'),
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'rows_new',
            'label' => trans('omicslogic::app.connectors.run-columns.new'),
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'rows_merged',
            'label' => trans('omicslogic::app.connectors.run-columns.merged'),
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'rows_review',
            'label' => trans('omicslogic::app.connectors.run-columns.review'),
            'type' => 'integer',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('omicslogic::app.connectors.run-columns.status'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                if ($row->status == 'success') {
                    return '<span class="rounded px-2 py-0.5 text-xs bg-green-100 text-green-700">' . ucfirst($row->status) . '</span>';
                }
                return '<span class="rounded px-2 py-0.5 text-xs bg-red-100 text-red-700">' . ucfirst($row->status) . '</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'started_at',
            'label' => trans('omicslogic::app.connectors.run-columns.when'),
            'type' => 'datetime',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                return $row->started_at ? core()->formatDate($row->started_at, 'd M Y H:i') : '—';
            },
        ]);
    }
}
