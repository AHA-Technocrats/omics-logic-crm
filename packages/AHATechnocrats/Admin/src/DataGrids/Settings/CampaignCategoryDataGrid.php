<?php

namespace AHATechnocrats\Admin\DataGrids\Settings;

use AHATechnocrats\DataGrid\DataGrid;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class CampaignCategoryDataGrid extends DataGrid
{
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('campaign_categories')
            ->addSelect(
                'campaign_categories.id',
                'campaign_categories.name'
            );

        $this->addFilter('id', 'campaign_categories.id');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.settings.campaign-categories.index.datagrid.id'),
            'type' => 'string',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('admin::app.settings.campaign-categories.index.datagrid.name'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);
    }

    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('settings.other_settings.campaign_categories.edit')) {
            $this->addAction([
                'index' => 'edit',
                'icon' => 'icon-edit',
                'title' => trans('admin::app.settings.campaign-categories.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.settings.campaign_categories.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('settings.other_settings.campaign_categories.delete')) {
            $this->addAction([
                'index' => 'delete',
                'icon' => 'icon-delete',
                'title' => trans('admin::app.settings.campaign-categories.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.settings.campaign_categories.delete', $row->id),
            ]);
        }
    }
}
