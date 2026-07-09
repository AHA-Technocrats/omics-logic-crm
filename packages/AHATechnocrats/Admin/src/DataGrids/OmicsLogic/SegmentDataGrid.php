<?php

namespace AHATechnocrats\Admin\DataGrids\OmicsLogic;

use AHATechnocrats\DataGrid\DataGrid;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SegmentDataGrid extends DataGrid
{
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('omics_segments')
            ->leftJoin('users', 'omics_segments.owner_id', '=', 'users.id')
            ->select(
                'omics_segments.id',
                'omics_segments.name',
                'omics_segments.description',
                'omics_segments.contact_count_cached',
                'omics_segments.refresh_schedule',
                'omics_segments.is_shared',
                'users.name as owner_name',
            );

        $this->addFilter('id', 'omics_segments.id');
        $this->addFilter('name', 'omics_segments.name');
        $this->addFilter('refresh_schedule', 'omics_segments.refresh_schedule');
        $this->addFilter('owner_name', 'users.name');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'name',
            'label' => trans('omicslogic::app.segments.name'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                if (! bouncer()->hasPermission('segments.view')) {
                    return e($row->name);
                }

                $url = route('admin.omics.segments.view', $row->id);

                return '<a href="'.$url.'" class="font-medium text-brandColor hover:underline dark:text-blue-400">'.e($row->name).'</a>';
            },
        ]);

        $this->addColumn([
            'index' => 'contact_count_cached',
            'label' => trans('omicslogic::app.segments.contacts'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => fn ($row) => number_format((int) ($row->contact_count_cached ?? 0)),
        ]);

        $this->addColumn([
            'index' => 'owner_name',
            'label' => trans('omicslogic::app.fields.owner'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'closure' => fn ($row) => $row->owner_name ?: trans('omicslogic::app.fields.unassigned'),
        ]);

        $this->addColumn([
            'index' => 'refresh_schedule',
            'label' => trans('omicslogic::app.segments.refresh'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'closure' => fn ($row) => ucfirst($row->refresh_schedule ?? 'manual'),
        ]);
    }

    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('segments.view')) {
            $this->addAction([
                'icon' => 'icon-eye',
                'title' => trans('admin::app.acl.view'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.omics.segments.view', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('segments.edit')) {
            $this->addAction([
                'icon' => 'icon-edit',
                'title' => trans('admin::app.acl.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.omics.segments.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('segments.delete')) {
            $this->addAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.acl.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.omics.segments.delete', $row->id),
            ]);
        }
    }

    public function prepareMassActions(): void
    {
        if (bouncer()->hasPermission('segments.delete')) {
            $this->addMassAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.acl.delete'),
                'method' => 'POST',
                'url' => route('admin.omics.segments.mass_delete'),
            ]);
        }
    }
}
