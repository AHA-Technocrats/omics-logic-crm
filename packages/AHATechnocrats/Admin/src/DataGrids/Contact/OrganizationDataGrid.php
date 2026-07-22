<?php

namespace AHATechnocrats\Admin\DataGrids\Contact;

use AHATechnocrats\DataGrid\DataGrid;
use AHATechnocrats\OmicsLogic\Enums\OrganizationType;
use AHATechnocrats\OmicsLogic\Services\CountryLabelResolver;
use AHATechnocrats\OmicsLogic\Support\OrganizationMetricIcon;
use AHATechnocrats\OmicsLogic\Support\OrganizationTypeIcon;
use AHATechnocrats\OmicsLogic\Support\UserProfileAvatar;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class OrganizationDataGrid extends DataGrid
{
    public function __construct(protected CountryLabelResolver $countryLabelResolver) {}

    protected $sortColumn = 'organizations.name';

    protected $sortOrder = 'asc';

    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('organizations')
            ->leftJoin('users as account_owners', 'organizations.account_owner_id', '=', 'account_owners.id')
            ->select(
                'organizations.id',
                'organizations.name',
                'organizations.type',
                'organizations.country_code',
                'account_owners.name as account_owner_name',
                'account_owners.image as account_owner_image',
            )
            ->selectRaw('(SELECT COUNT(*) FROM persons WHERE persons.organization_id = organizations.id) as contacts_count')
            ->selectRaw("(SELECT COUNT(DISTINCT persons.id) FROM persons INNER JOIN leads ON leads.person_id = persons.id INNER JOIN lead_pipeline_stages ON lead_pipeline_stages.id = leads.lead_pipeline_stage_id WHERE persons.organization_id = organizations.id AND lead_pipeline_stages.code IN ('follow-up', 'prospect', 'negotiation')) as engaged_count")
            ->selectRaw("(SELECT COUNT(DISTINCT persons.id) FROM persons INNER JOIN leads ON leads.person_id = persons.id INNER JOIN lead_pipeline_stages ON lead_pipeline_stages.id = leads.lead_pipeline_stage_id WHERE persons.organization_id = organizations.id AND lead_pipeline_stages.code = 'won') as customers_count");

        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $queryBuilder->where(function ($query) use ($userIds) {
                $query->whereIn('organizations.account_owner_id', $userIds)
                    ->orWhereIn('organizations.user_id', $userIds);
            });
        }

        $this->addFilter('id', 'organizations.id');
        $this->addFilter('name', 'organizations.name');
        $this->addFilter('country_code', 'organizations.country_code');
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
            'closure' => fn ($row) => $this->organizationCell($row),
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
            'index' => 'contacts_count',
            'label' => trans('omicslogic::app.datagrid.contacts'),
            'tooltip' => trans('omicslogic::app.datagrid.tooltip.contacts'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => fn ($row) => '<span class="font-bold">'.(int) ($row->contacts_count ?? 0).'</span>',
        ]);

        $this->addColumn([
            'index' => 'engaged_count',
            'label' => trans('omicslogic::app.datagrid.engaged'),
            'tooltip' => trans('omicslogic::app.datagrid.tooltip.engaged'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => fn ($row) => $this->engagedCell((int) ($row->engaged_count ?? 0)),
        ]);

        $this->addColumn([
            'index' => 'customers_count',
            'label' => trans('omicslogic::app.datagrid.customers'),
            'tooltip' => trans('omicslogic::app.datagrid.tooltip.customers'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => fn ($row) => $this->customersCell((int) ($row->customers_count ?? 0)),
        ]);

        $this->addColumn([
            'index' => 'account_owner_name',
            'label' => trans('omicslogic::app.datagrid.owner'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'closure' => fn ($row) => $this->ownerCell($row),
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
            'text-gray-700 dark:text-white font-600 text-xs',
            // 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100',
            // 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
            // 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100',
            // 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-100',
            // 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-100',
            // 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-100',
            // 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-100',
            // 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-100',
        ];

        $class = $palette[abs(crc32(strtolower($country))) % count($palette)];

        return '<span class="rounded-full px-2 py-0.5 text-xs font-semibold '.$class.'">'.$label.'</span>';
    }

    protected function organizationCell(object $row): string
    {
        $name = e($row->name);
        $url = route('admin.contacts.organizations.view', $row->id);
        $typeLabel = $this->organizationTypeLabel($row->type ?? null);
        $icon = OrganizationTypeIcon::html($row->type ?? null);
        $backgroundColor = $this->organizationAvatarColor($row->name);

        $typeHtml = $typeLabel
            ? '<span class="block truncate text-xs text-gray-500 dark:text-gray-400">'.e($typeLabel).'</span>'
            : '';

        return '<div class="flex items-center gap-2.5">'
            .'<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full" style="background-color:'.$backgroundColor.';">'
            .$icon
            .'</div>'
            .'<div class="min-w-0">'
            .'<a href="'.$url.'" class="font-medium text-gray-800 hover:text-brandColor dark:text-white dark:hover:text-blue-400">'.$name.'</a>'
            .$typeHtml
            .'</div>'
            .'</div>';
    }

    protected function organizationTypeLabel(?string $type): ?string
    {
        if (! $type) {
            return trans('omicslogic::app.fields.any');
        }

        return OrganizationType::tryFrom(strtolower($type))?->label()
            ?? ucfirst($type);
    }

    protected function organizationAvatarColor(string $name): string
    {
        $palette = [
            '#b91c1c',
            '#1d4ed8',
            '#d97706',
            '#0d9488',
            '#a21caf',
            '#4338ca',
        ];

        return $palette[abs(crc32(strtolower($name))) % count($palette)];
    }

    protected function engagedCell(int $count): string
    {
        return '<span style="display:inline-flex;align-items:center;gap:6px;">'
            .OrganizationMetricIcon::flame()
            .'<span>'.$count.'</span>'
            .'</span>';
    }

    protected function customersCell(int $count): string
    {
        return '<span style="display:inline-flex;align-items:center;gap:6px;">'
            .OrganizationMetricIcon::award()
            .'<span>'.$count.'</span>'
            .'</span>';
    }

    protected function ownerCell(object $row): string
    {
        if (empty($row->account_owner_name)) {
            return $this->unassignedOwnerBadge((int) $row->id);
        }

        $content = '<div style="display:flex;align-items:center;gap:8px;">'
            .UserProfileAvatar::html($row->account_owner_name, $row->account_owner_image ?? null)
            .'<span class="text-gray-800 dark:text-white">'.e($row->account_owner_name).'</span>'
            .'</div>';

        if (! bouncer()->hasPermission('organizations.edit')) {
            return $content;
        }

        $url = e(route('admin.contacts.organizations.edit', (int) $row->id));

        return '<a href="'.$url.'" style="display:inline-flex;text-decoration:none;color:inherit;cursor:pointer;">'
            .$content
            .'</a>';
    }

    protected function unassignedOwnerBadge(int $organizationId): string
    {
        $label = e(trans('omicslogic::app.fields.unassigned'));

        $badge = '<span style="display:inline-flex;align-items:center;gap:6px;background-color:#ffedd5;color:#92400e;border-radius:9999px;padding:4px 12px;font-size:12px;font-weight:600;">'
            .'<i class="fa fa-user-plus" style="font-size:12px;"></i>'
            .$label
            .'</span>';

        if (! bouncer()->hasPermission('organizations.edit')) {
            return $badge;
        }

        $url = e(route('admin.contacts.organizations.edit', $organizationId));

        return '<a href="'.$url.'" style="display:inline-flex;text-decoration:none;color:inherit;cursor:pointer;">'
            .$badge
            .'</a>';
    }
}
