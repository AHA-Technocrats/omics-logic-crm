<?php

namespace AHATechnocrats\Admin\DataGrids\OmicsLogic;

use AHATechnocrats\Admin\DataGrids\Lead\LeadDataGrid;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use AHATechnocrats\OmicsLogic\Models\Segment;
use AHATechnocrats\OmicsLogic\Services\SegmentFilterCounter;
use AHATechnocrats\Product\Repositories\ProductRepository;
use AHATechnocrats\User\Repositories\UserRepository;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SegmentLeadDataGrid extends LeadDataGrid
{
    public function prepareQueryBuilder(): Builder
    {
        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('leads')
            ->addSelect(
                'leads.id',
                'leads.title',
                'leads.status',
                'leads.lead_value',
                'lead_sources.name as lead_source_name',
                'lead_types.name as lead_type_name',
                'leads.created_at',
                'lead_pipeline_stages.name as stage',
                'users.id as user_id',
                'users.name as sales_person',
                'persons.id as person_id',
                'persons.name as person_name',
                DB::raw('GROUP_CONCAT(DISTINCT '.$tablePrefix.'products.name) as campaign_name'),
            )
            ->leftJoin('users', 'leads.user_id', '=', 'users.id')
            ->leftJoin('persons', 'leads.person_id', '=', 'persons.id')
            ->leftJoin('lead_types', 'leads.lead_type_id', '=', 'lead_types.id')
            ->leftJoin('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
            ->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
            ->leftJoin('lead_products', 'leads.id', '=', 'lead_products.lead_id')
            ->leftJoin('products', 'lead_products.product_id', '=', 'products.id')
            ->whereNull('persons.merged_into_id')
            ->groupBy('leads.id');

        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $queryBuilder->whereIn('leads.user_id', $userIds);
        }

        $segment = $this->resolveSegment();

        app(SegmentFilterCounter::class)->applyFiltersToQuery(
            $queryBuilder,
            $segment->filter_query ?? [],
            'persons',
        );

        $this->addFilter('id', 'leads.id');
        $this->addFilter('user', 'leads.user_id');
        $this->addFilter('sales_person', 'users.name');
        $this->addFilter('lead_source_name', 'lead_sources.id');
        $this->addFilter('lead_type_name', 'lead_types.id');
        $this->addFilter('person_name', 'persons.name');
        $this->addFilter('type', 'lead_pipeline_stages.code');
        $this->addFilter('stage', 'lead_pipeline_stages.id');
        $this->addFilter('campaign_name', 'products.name');
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
            'index' => 'sales_person',
            'label' => trans('admin::app.leads.index.datagrid.sales-person'),
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
        ]);

        $this->addColumn([
            'index' => 'title',
            'label' => trans('admin::app.leads.index.datagrid.subject'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'lead_source_name',
            'label' => trans('admin::app.leads.index.datagrid.source'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->sourceRepository->all(['name as label', 'id as value'])->toArray(),
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
        ]);

        $this->addColumn([
            'index' => 'campaign_name',
            'label' => trans('omicslogic::app.datagrid.program'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => false,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'closure' => fn ($row) => $row->campaign_name ?? '--',
            'filterable_options' => [
                'repository' => ProductRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
        ]);

        $this->addColumn([
            'index' => 'person_name',
            'label' => trans('admin::app.leads.index.datagrid.contact-person'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'filterable_options' => [
                'repository' => PersonRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
            'closure' => function ($row) {
                if (! $row->person_id) {
                    return '--';
                }

                $route = route('admin.contacts.persons.view', $row->person_id);

                return "<a class=\"text-brandColor transition-all hover:underline\" href='".$route."'>".$row->person_name.'</a>';
            },
        ]);

        $this->addColumn([
            'index' => 'stage',
            'label' => trans('admin::app.leads.index.datagrid.stage'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => $this->stageRepository->all(['name as label', 'id as value'])->toArray(),
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.leads.index.datagrid.created-at'),
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
        ]);
    }

    public function prepareMassActions(): void
    {
        //
    }

    protected function resolveSegment(): Segment
    {
        return Segment::query()->findOrFail((int) request()->input('segment_id'));
    }
}
