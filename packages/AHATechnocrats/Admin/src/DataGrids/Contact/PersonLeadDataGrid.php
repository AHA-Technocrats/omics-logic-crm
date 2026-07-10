<?php

namespace AHATechnocrats\Admin\DataGrids\Contact;

use AHATechnocrats\Admin\DataGrids\Lead\LeadDataGrid;
use AHATechnocrats\Product\Repositories\ProductRepository;
use AHATechnocrats\User\Repositories\UserRepository;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class PersonLeadDataGrid extends LeadDataGrid
{
    public function prepareQueryBuilder(): Builder
    {
        $tablePrefix = DB::getTablePrefix();
        $personId = $this->resolvePersonId();

        $queryBuilder = DB::table('leads')
            ->addSelect(
                'leads.id',
                'leads.title',
                'leads.status',
                'leads.lead_value',
                'leads.expected_close_date',
                'lead_sources.name as lead_source_name',
                'lead_types.name as lead_type_name',
                'leads.created_at',
                'lead_pipeline_stages.name as stage',
                'lead_pipeline_stages.code as stage_code',
                'users.id as user_id',
                'users.name as sales_person',
                'persons.id as person_id',
                'persons.lead_score',
                DB::raw('GROUP_CONCAT(DISTINCT '.$tablePrefix.'products.name SEPARATOR ", ") as campaign_name'),
            )
            ->leftJoin('users', 'leads.user_id', '=', 'users.id')
            ->leftJoin('persons', 'leads.person_id', '=', 'persons.id')
            ->leftJoin('lead_types', 'leads.lead_type_id', '=', 'lead_types.id')
            ->leftJoin('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
            ->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
            ->leftJoin('lead_products', 'leads.id', '=', 'lead_products.lead_id')
            ->leftJoin('products', 'lead_products.product_id', '=', 'products.id')
            ->where('leads.person_id', $personId)
            ->groupBy('leads.id');

        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $queryBuilder->whereIn('leads.user_id', $userIds);
        }

        $this->addFilter('id', 'leads.id');
        $this->addFilter('user', 'leads.user_id');
        $this->addFilter('sales_person', 'users.name');
        $this->addFilter('lead_source_name', 'lead_sources.id');
        $this->addFilter('lead_type_name', 'lead_types.id');
        $this->addFilter('type', 'lead_pipeline_stages.code');
        $this->addFilter('stage', 'lead_pipeline_stages.id');
        $this->addFilter('campaign_name', 'products.name');
        $this->addFilter('lead_score', 'persons.lead_score');
        $this->addFilter('created_at', 'leads.created_at');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.leads.index.datagrid.id'),
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'title',
            'label' => trans('admin::app.leads.index.datagrid.subject'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                $title = e($row->title ?: '—');
                $url = route('admin.leads.view', $row->id);

                return '<a class="text-brandColor hover:underline" href="'.e($url).'">'.$title.'</a>';
            },
        ]);

        $this->addColumn([
            'index' => 'campaign_name',
            'label' => trans('omicslogic::app.datagrid.program'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => false,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'closure' => fn ($row) => $row->campaign_name ? e($row->campaign_name) : '—',
            'filterable_options' => [
                'repository' => ProductRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
        ]);

        $this->addColumn([
            'index' => 'lead_source_name',
            'label' => trans('omicslogic::app.datagrid.source'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->sourceRepository->all(['name as label', 'id as value'])->toArray(),
            'closure' => fn ($row) => $row->lead_source_name ? e($row->lead_source_name) : '—',
        ]);

        $this->addColumn([
            'index' => 'lead_score',
            'label' => trans('omicslogic::app.datagrid.score'),
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
            'closure' => fn ($row) => $this->scoreBadge((int) ($row->lead_score ?? 0)),
        ]);

        $this->addColumn([
            'index' => 'stage',
            'label' => trans('omicslogic::app.datagrid.stage'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->stageRepository->all(['name as label', 'id as value'])->toArray(),
            'closure' => fn ($row) => $row->stage ? e($row->stage) : '—',
        ]);

        $this->addColumn([
            'index' => 'sales_person',
            'label' => trans('omicslogic::app.datagrid.owner'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => UserRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
            'closure' => fn ($row) => $row->sales_person
                ? e($row->sales_person)
                : '<span class="text-gray-400">'.e(trans('omicslogic::app.fields.unassigned')).'</span>',
        ]);

        $this->addColumn([
            'index' => 'lead_value',
            'label' => trans('admin::app.leads.index.datagrid.lead-value'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => false,
            'filterable' => true,
            'closure' => fn ($row) => core()->formatBasePrice($row->lead_value, 2),
        ]);

        $this->addColumn([
            'index' => 'lead_type_name',
            'label' => trans('admin::app.leads.index.datagrid.lead-type'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->typeRepository->all(['name as label', 'id as value'])->toArray(),
            'closure' => fn ($row) => $row->lead_type_name ? e($row->lead_type_name) : '—',
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('omicslogic::app.datagrid.last-activity'),
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'closure' => fn ($row) => $row->created_at
                ? core()->formatDate($row->created_at, 'd M Y')
                : '—',
        ]);
    }

    public function prepareMassActions(): void
    {
        //
    }

    protected function resolvePersonId(): int
    {
        return (int) request()->input('person_id');
    }
}
