<?php

namespace AHATechnocrats\Admin\DataGrids\Contact;

use AHATechnocrats\DataGrid\DataGrid;
use AHATechnocrats\OmicsLogic\Services\CountryLabelResolver;
use AHATechnocrats\Product\Repositories\ProductRepository;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class OrganizationDataGrid extends DataGrid
{
    public function __construct(protected CountryLabelResolver $countryLabelResolver) {}

    protected $sortColumn = 'organizations.name';

    protected $sortOrder = 'asc';

    public function prepareQueryBuilder(): Builder
    {
        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('organizations')
            ->leftJoin('users as account_owners', 'organizations.account_owner_id', '=', 'account_owners.id')
            ->leftJoin('persons', 'organizations.id', '=', 'persons.organization_id')
            ->leftJoin('products', 'persons.primary_product_id', '=', 'products.id')
            ->select(
                'organizations.id',
                'organizations.name',
                'organizations.type',
                'organizations.country_code',
                'account_owners.name as account_owner_name',
                DB::raw('GROUP_CONCAT(DISTINCT '.$tablePrefix.'products.name SEPARATOR ", ") as campaign_name'),
            )
            ->selectRaw('(SELECT COUNT(*) FROM persons WHERE persons.organization_id = organizations.id) as contacts_count')
            ->selectRaw("(SELECT COUNT(*) FROM persons WHERE persons.organization_id = organizations.id AND persons.lifecycle_stage = 'engaged') as engaged_count")
            ->selectRaw("(SELECT COUNT(*) FROM persons WHERE persons.organization_id = organizations.id AND persons.lifecycle_stage = 'customer') as customers_count")
            ->groupBy('organizations.id');

        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $queryBuilder->where(function ($query) use ($userIds) {
                $query->whereIn('organizations.account_owner_id', $userIds)
                    ->orWhereIn('organizations.user_id', $userIds);
            });
        }

        $this->addFilter('id', 'organizations.id');
        $this->addFilter('name', 'organizations.name');
        $this->addFilter('country_code', 'organizations.country_code');
        $this->addFilter('campaign_name', 'products.name');
        $this->addFilter('account_owner_name', 'account_owners.name');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'name',
            'label' => trans('omicslogic::app.datagrid.organization'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'country_code',
            'label' => trans('omicslogic::app.datagrid.country'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'searchable' => true,
            'closure' => fn ($row) => $this->countryBadge($row->country_code),
        ]);

        $this->addColumn([
            'index' => 'campaign_name',
            'label' => trans('omicslogic::app.datagrid.program'),
            'type' => 'string',
            'sortable' => false,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => ProductRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
            'closure' => fn ($row) => $row->campaign_name ?? '—',
        ]);

        $this->addColumn([
            'index' => 'contacts_count',
            'label' => trans('omicslogic::app.datagrid.contacts'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => fn ($row) => (int) ($row->contacts_count ?? 0),
        ]);

        $this->addColumn([
            'index' => 'engaged_count',
            'label' => trans('omicslogic::app.datagrid.engaged'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => fn ($row) => (int) ($row->engaged_count ?? 0),
        ]);

        $this->addColumn([
            'index' => 'customers_count',
            'label' => trans('omicslogic::app.datagrid.customers'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => fn ($row) => (int) ($row->customers_count ?? 0),
        ]);

        $this->addColumn([
            'index' => 'account_owner_name',
            'label' => trans('omicslogic::app.datagrid.owner'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'closure' => fn ($row) => $row->account_owner_name ?: trans('omicslogic::app.fields.unassigned'),
        ]);
    }

    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('organizations.view')) {
            $this->addAction([
                'icon' => 'icon-eye',
                'title' => trans('admin::app.contacts.organizations.index.datagrid.view'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.contacts.organizations.view', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('organizations.edit')) {
            $this->addAction([
                'icon' => 'icon-edit',
                'title' => trans('admin::app.contacts.organizations.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.contacts.organizations.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('organizations.delete')) {
            $this->addAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.contacts.organizations.index.datagrid.delete'),
                'method' => 'CASCADE_DELETE',
                'url' => fn ($row) => route('admin.contacts.organizations.delete-preview', $row->id),
            ]);
        }
    }

    public function prepareMassActions(): void
    {
        $this->addMassAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.contacts.organizations.index.datagrid.delete'),
            'method' => 'POST',
            'url' => route('admin.contacts.organizations.mass_delete'),
        ]);
    }

    protected function countryBadge(?string $country): string
    {
        $country = $this->countryLabelResolver->resolve($country);

        if (! $country) {
            return '—';
        }

        $label = e($country);

        $palette = [
            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100',
            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
            'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100',
            'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-100',
            'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-100',
            'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-100',
            'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-100',
            'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-100',
        ];

        $class = $palette[abs(crc32(strtolower($country))) % count($palette)];

        return '<span class="rounded-full px-2 py-0.5 text-xs font-semibold '.$class.'">'.$label.'</span>';
    }
}
