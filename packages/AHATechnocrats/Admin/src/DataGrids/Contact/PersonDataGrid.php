<?php

namespace AHATechnocrats\Admin\DataGrids\Contact;

use AHATechnocrats\Contact\Repositories\OrganizationRepository;
use AHATechnocrats\DataGrid\DataGrid;
use AHATechnocrats\OmicsLogic\Services\CountryLabelResolver;
use AHATechnocrats\OmicsLogic\Support\PersonOwnerCell;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PersonDataGrid extends DataGrid
{
    public function __construct(
        protected OrganizationRepository $organizationRepository,
        protected CountryLabelResolver $countryLabelResolver,
    ) {}

    public function prepareQueryBuilder(): Builder
    {
        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('persons')
            ->select(
                'persons.id',
                'persons.name as person_name',
                'persons.emails',
                'persons.education_level',
                'persons.last_activity_at',
                'organizations.name as organization',
                'organizations.id as organization_id',
                'users.name as owner_name',
                'users.image as owner_image',
            )
            ->selectRaw('(SELECT COUNT(*) FROM '.$tablePrefix.'leads WHERE '.$tablePrefix.'leads.person_id = '.$tablePrefix.'persons.id) as leads_count')
            ->selectRaw('(
                SELECT COUNT(*) FROM '.$tablePrefix.'leads
                JOIN '.$tablePrefix.'lead_pipeline_stages ON '.$tablePrefix.'lead_pipeline_stages.id = '.$tablePrefix.'leads.lead_pipeline_stage_id
                WHERE '.$tablePrefix.'leads.person_id = '.$tablePrefix.'persons.id
                  AND '.$tablePrefix.'lead_pipeline_stages.code = \'won\'
            ) as won_leads_count')
            ->selectRaw('COALESCE(organizations.country_code, persons.country_code) as country_code')
            ->leftJoin('organizations', 'persons.organization_id', '=', 'organizations.id')
            ->leftJoin('users', 'persons.user_id', '=', 'users.id')
            ->whereNull('persons.merged_into_id');

        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $queryBuilder->whereIn('persons.user_id', $userIds);
        }

        $this->addFilter('id', 'persons.id');
        $this->addFilter('person_name', 'persons.name');
        $this->addFilter('organization', 'organizations.name');
        $this->addFilter('country_code', 'organizations.country_code');
        $this->addFilter('education_level', 'persons.education_level');
        $this->addFilter('owner_name', 'users.name');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'person_name',
            'label' => trans('omicslogic::app.datagrid.contact'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'searchable' => true,
            'closure' => function ($row) {
                $email = $this->primaryEmail($row);
                $initials = $this->initials($row->person_name);
                $url = route('admin.contacts.persons.view', $row->id);
                $name = e($row->person_name);

                return <<<HTML
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brandColor text-xs font-semibold text-white">{$initials}</div>
                    <div class="min-w-0">
                        <a href="{$url}" class="font-medium text-brandColor hover:underline dark:text-blue-400">{$name}</a>
                        <div class="truncate text-xs text-gray-500 dark:text-gray-400">{$email}</div>
                    </div>
                </div>
                HTML;
            },
        ]);

        $this->addColumn([
            'index' => 'organization',
            'label' => trans('omicslogic::app.datagrid.organization'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'filterable_type' => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => OrganizationRepository::class,
                'column' => ['label' => 'name', 'value' => 'name'],
            ],
            'closure' => function ($row) {
                if (! $row->organization) {
                    return '—';
                }

                if ($row->organization_id) {
                    $url = route('admin.contacts.organizations.view', $row->organization_id);

                    return '<a href="'.e($url).'" style="text-decoration:none;" class="text-gray-800 dark:text-white">'.e($row->organization).'</a>';
                }

                return e($row->organization);
            },
        ]);

        $this->addColumn([
            'index' => 'country_code',
            'label' => trans('omicslogic::app.datagrid.country'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'searchable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => collect(config('omicslogic.countries', []))
                ->map(fn (string $country) => ['label' => $country, 'value' => $country])
                ->values()
                ->all(),
            'closure' => fn ($row) => $this->countryBadge($row->country_code),
        ]);

        $this->addColumn([
            'index' => 'leads_count',
            'label' => trans('omicslogic::app.datagrid.leads'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => function ($row) {
                $count = (int) ($row->leads_count ?? 0);

                if ($count === 0) {
                    return '0';
                }

                $url = route('admin.contacts.persons.leads.index', $row->id);
                $label = trans('omicslogic::app.delete-timeline.leads-count', ['count' => $count]);

                return '<a class="text-brandColor hover:underline" href="'.e($url).'">'.e($label).'</a>';
            },
        ]);

        $this->addColumn([
            'index' => 'won_leads_count',
            'label' => trans('omicslogic::app.datagrid.customers') !== 'omicslogic::app.datagrid.customers' ? trans('omicslogic::app.datagrid.customers') : 'Customers',
            'type' => 'integer',
            'sortable' => true,
            'closure' => function ($row) {
                $count = (int) ($row->won_leads_count ?? 0);

                return '<span class="flex items-center gap-1">'
                    .'<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600 dark:text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"></circle><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"></path></svg>'
                    .'<span class="text-gray-800 dark:text-white">'.$count.'</span>'
                    .'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'owner_name',
            'label' => trans('omicslogic::app.datagrid.owner'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'searchable' => true,
            'closure' => fn ($row) => PersonOwnerCell::cell(
                $row->owner_name,
                $row->owner_image ?? null,
                (int) $row->id,
            ),
        ]);

        $this->addColumn([
            'index' => 'last_activity_at',
            'label' => trans('omicslogic::app.datagrid.last-activity'),
            'type' => 'date',
            'sortable' => true,
            'closure' => fn ($row) => $row->last_activity_at
                ? core()->formatDate($row->last_activity_at, 'd M Y')
                : '—',
        ]);
    }

    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('persons.view')) {
            $this->addAction([
                'icon' => 'icon-eye',
                'title' => trans('admin::app.contacts.persons.index.datagrid.view'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.contacts.persons.view', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('persons.edit')) {
            $this->addAction([
                'icon' => 'icon-edit',
                'title' => trans('admin::app.contacts.persons.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.contacts.persons.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('persons.delete')) {
            $this->addAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.contacts.persons.index.datagrid.delete'),
                'method' => 'CASCADE_DELETE',
                'url' => fn ($row) => route('admin.contacts.persons.delete-preview', $row->id),
            ]);
        }
    }

    public function prepareMassActions(): void
    {
        if (bouncer()->hasPermission('persons.delete')) {
            $this->addMassAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.contacts.persons.index.datagrid.delete'),
                'method' => 'POST',
                'url' => route('admin.contacts.persons.mass_delete'),
            ]);
        }
    }

    protected function primaryEmail(object $row): string
    {
        $emails = json_decode($row->emails ?? '[]', true);

        if (! is_array($emails)) {
            return '—';
        }

        $email = collect($emails)->pluck('value')->filter()->first();

        return $email ? e($email) : '—';
    }

    protected function initials(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];

        $initials = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');

        return e($initials ?: '?');
    }

    protected function countryBadge(?string $country): string
    {
        $country = $this->countryLabelResolver->resolve($country);

        if (! $country) {
            return '—';
        }

        return '<span style="" class="text-gray-800 dark:text-white">'.e($country).'</span>';
    }
}
