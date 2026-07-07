<?php

namespace AHATechnocrats\Admin\DataGrids\Product;

use AHATechnocrats\DataGrid\DataGrid;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ProductDataGrid extends DataGrid
{
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('products')
            ->leftJoin('omics_product_aliases', 'products.id', '=', 'omics_product_aliases.product_id')
            ->select(
                'products.id',
                'products.name',
                'products.category',
                'products.mapping_status',
                'products.is_active',
            )
            ->selectRaw('COUNT(DISTINCT omics_product_aliases.id) as alias_count')
            ->selectRaw('(SELECT COUNT(*) FROM persons WHERE persons.primary_product_id = products.id) as leads_count')
            ->selectRaw("(SELECT COUNT(*) FROM persons WHERE persons.primary_product_id = products.id AND persons.lifecycle_stage = 'customer') as customers_count")
            ->groupBy('products.id', 'products.name', 'products.category', 'products.mapping_status', 'products.is_active');

        $this->addFilter('id', 'products.id');
        $this->addFilter('name', 'products.name');
        $this->addFilter('category', 'products.category');
        $this->addFilter('mapping_status', 'products.mapping_status');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'name',
            'label' => trans('omicslogic::app.datagrid.canonical-campaign'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'category',
            'label' => trans('omicslogic::app.datagrid.category'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'searchable' => true,
            'closure' => fn ($row) => $row->category ?: '—',
        ]);

        $this->addColumn([
            'index' => 'alias_count',
            'label' => trans('omicslogic::app.datagrid.aliases'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => fn ($row) => (int) ($row->alias_count ?? 0),
        ]);

        $this->addColumn([
            'index' => 'leads_count',
            'label' => trans('omicslogic::app.datagrid.leads'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => fn ($row) => (int) ($row->leads_count ?? 0),
        ]);

        $this->addColumn([
            'index' => 'customers_count',
            'label' => trans('omicslogic::app.datagrid.customers'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => fn ($row) => (int) ($row->customers_count ?? 0),
        ]);

        $this->addColumn([
            'index' => 'conversion_rate',
            'label' => trans('omicslogic::app.datagrid.conversion'),
            'type' => 'string',
            'closure' => function ($row) {
                $leads = (int) ($row->leads_count ?? 0);
                $customers = (int) ($row->customers_count ?? 0);

                if ($leads === 0) {
                    return '0%';
                }

                return round(($customers / $leads) * 100).'%';
            },
        ]);

        $this->addColumn([
            'index' => 'mapping_status',
            'label' => trans('omicslogic::app.datagrid.status'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'closure' => function ($row) {
                if ($row->mapping_status === 'mapped') {
                    return trans('omicslogic::app.fields.status-mapped');
                }

                if ($row->mapping_status === 'review') {
                    return trans('omicslogic::app.fields.status-review');
                }

                return $row->is_active
                    ? trans('omicslogic::app.fields.status-active')
                    : trans('omicslogic::app.fields.status-inactive');
            },
        ]);
    }

    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('campaigns.view')) {
            $this->addAction([
                'index' => 'view',
                'icon' => 'icon-eye',
                'title' => trans('admin::app.campaigns.index.datagrid.view'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.campaigns.view', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('campaigns.edit')) {
            $this->addAction([
                'index' => 'edit',
                'icon' => 'icon-edit',
                'title' => trans('admin::app.campaigns.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.campaigns.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('campaigns.delete')) {
            $this->addAction([
                'index' => 'delete',
                'icon' => 'icon-delete',
                'title' => trans('admin::app.campaigns.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.campaigns.delete', $row->id),
            ]);
        }
    }

    public function prepareMassActions(): void
    {
        $this->addMassAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.campaigns.index.datagrid.delete'),
            'method' => 'POST',
            'url' => route('admin.campaigns.mass_delete'),
        ]);
    }
}
