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
            'closure' => fn ($row) => '<span style="font-weight:600;" class="text-gray-800 dark:text-white">'.e($row->name).'</span>',
        ]);

        $this->addColumn([
            'index' => 'category',
            'label' => trans('omicslogic::app.datagrid.category'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'searchable' => true,
            'closure' => fn ($row) => $row->category
                ? '<span  class="text-gray-500 dark:text-white">'.e($row->category).'</span>'
                : '—',
        ]);

        $this->addColumn([
            'index' => 'alias_count',
            'label' => trans('omicslogic::app.datagrid.aliases'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => fn ($row) => '<span style="display:inline-flex;align-items:center;gap:6px;" class="text-gray-500 dark:text-white">'
                .'<i class="fa fa-tag" style="font-size:13px;"></i>'
                .'<span>'.(int) ($row->alias_count ?? 0).'</span>'
                .'</span>',

        ]);

        $this->addColumn([
            'index' => 'leads_count',
            'label' => trans('omicslogic::app.datagrid.leads'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => fn ($row) => '<span class="font-bold">'.(int) ($row->leads_count ?? 0).'</span>',
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
            'closure' => fn ($row) => $this->statusBadge($row),
        ]);
    }

    protected function statusBadge(object $row): string
    {
        if ($row->mapping_status === 'mapped') {
            return $this->statusPill(
                trans('omicslogic::app.fields.status-mapped'),
                'fa fa-check',
                '#dcfce7',
                '#15803d',
            );
        }

        if ($row->mapping_status === 'review') {
            $pill = $this->statusPill(
                trans('omicslogic::app.fields.status-review'),
                'fa fa-exclamation-triangle',
                '#fef3c7',
                '#b45309',
            );

            if (! bouncer()->hasPermission('campaigns.edit')) {
                return $pill;
            }

            $url = e(route('admin.campaigns.edit', $row->id));

            return '<a href="'.$url.'" style="display:inline-flex;text-decoration:none;cursor:pointer;">'
                .$pill
                .'</a>';
        }

        if ($row->is_active) {
            return $this->statusPill(
                trans('omicslogic::app.fields.status-active'),
                'fa fa-check',
                '#dcfce7',
                '#15803d',
            );
        }

        return $this->statusPill(
            trans('omicslogic::app.fields.status-inactive'),
            'fa fa-ban',
            '#f3f4f6',
            '#6b7280',
        );
    }

    protected function statusPill(string $label, string $icon, string $background, string $color): string
    {
        return '<span style="display:inline-flex;align-items:center;gap:6px;background-color:'.$background.';color:'.$color.';border-radius:9999px;padding:4px 12px;font-size:12px;font-weight:600;">'
            .'<i class="'.$icon.'" style="font-size:11px;"></i>'
            .'<span>'.e($label).'</span>'
            .'</span>';
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
