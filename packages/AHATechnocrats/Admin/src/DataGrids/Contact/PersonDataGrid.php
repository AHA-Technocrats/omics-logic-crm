<?php

namespace AHATechnocrats\Admin\DataGrids\Contact;

use AHATechnocrats\Contact\Repositories\OrganizationRepository;
use AHATechnocrats\DataGrid\DataGrid;
use AHATechnocrats\OmicsLogic\Services\CountryLabelResolver;
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
                'persons.engagement_lessons',
                'persons.last_activity_at',
                'organizations.name as organization',
                'organizations.id as organization_id',
                'users.name as owner_name',
            )
            ->selectRaw('(SELECT COUNT(*) FROM '.$tablePrefix.'leads WHERE '.$tablePrefix.'leads.person_id = '.$tablePrefix.'persons.id) as leads_count')
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

                    return '<a class="text-brandColor hover:underline" href="'.$url.'">'.e($row->organization).'</a>';
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
            'index' => 'engagement_lessons',
            'label' => trans('omicslogic::app.datagrid.lessons'),
            'type' => 'integer',
            'sortable' => true,
            'closure' => fn ($row) => (int) ($row->engagement_lessons ?? 0),
        ]);

        $this->addColumn([
            'index' => 'owner_name',
            'label' => trans('omicslogic::app.datagrid.owner'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'searchable' => true,
            'closure' => fn ($row) => $row->owner_name
                ? e($row->owner_name)
                : '<span class="text-gray-400">'.e(trans('omicslogic::app.fields.unassigned')).'</span>',
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
