<?php

namespace AHATechnocrats\Admin\DataGrids\Lead;

use AHATechnocrats\Contact\Repositories\PersonRepository;
use AHATechnocrats\Contract\Repositories\Pipeline;
use AHATechnocrats\DataGrid\DataGrid;
use AHATechnocrats\Lead\Repositories\PipelineRepository;
use AHATechnocrats\Lead\Repositories\SourceRepository;
use AHATechnocrats\Lead\Repositories\StageRepository;
use AHATechnocrats\Lead\Repositories\TypeRepository;
use AHATechnocrats\Product\Repositories\ProductRepository;
use AHATechnocrats\Tag\Repositories\TagRepository;
use AHATechnocrats\User\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class LeadDataGrid extends DataGrid
{
    /**
     * Pipeline instance.
     *
     * @var Pipeline
     */
    protected $pipeline;

    /**
     * Create data grid instance.
     *
     * @return void
     */
    public function __construct(
        protected PipelineRepository $pipelineRepository,
        protected StageRepository $stageRepository,
        protected SourceRepository $sourceRepository,
        protected TypeRepository $typeRepository,
        protected UserRepository $userRepository,
        protected TagRepository $tagRepository,
    ) {
        if (request('pipeline_id')) {
            $this->pipeline = $this->pipelineRepository->find(request('pipeline_id'));
        } else {
            $this->pipeline = $this->pipelineRepository->getDefaultPipeline();
        }
    }

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $tablePrefix = DB::getTablePrefix();

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
                'lead_tags.tag_id as tag_id',
                'users.id as user_id',
                'users.name as sales_person',
                'persons.id as person_id',
                'persons.name as person_name',
                'persons.lead_score',
                'tags.name as tag_name',
                'lead_pipelines.rotten_days as pipeline_rotten_days',
                'lead_pipeline_stages.code as stage_code',
                DB::raw('CASE WHEN DATEDIFF(NOW(),'.$tablePrefix.'leads.created_at) >='.$tablePrefix.'lead_pipelines.rotten_days THEN 1 ELSE 0 END as rotten_lead'),
                DB::raw('GROUP_CONCAT(DISTINCT '.$tablePrefix.'products.name) as campaign_name'),
            )
            ->leftJoin('users', 'leads.user_id', '=', 'users.id')
            ->leftJoin('persons', 'leads.person_id', '=', 'persons.id')
            ->leftJoin('lead_types', 'leads.lead_type_id', '=', 'lead_types.id')
            ->leftJoin('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
            ->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
            ->leftJoin('lead_pipelines', 'leads.lead_pipeline_id', '=', 'lead_pipelines.id')
            ->leftJoin('lead_tags', 'leads.id', '=', 'lead_tags.lead_id')
            ->leftJoin('tags', 'tags.id', '=', 'lead_tags.tag_id')
            ->leftJoin('lead_products', 'leads.id', '=', 'lead_products.lead_id')
            ->leftJoin('products', 'lead_products.product_id', '=', 'products.id')
            ->groupBy('leads.id')
            ->where('leads.lead_pipeline_id', $this->pipeline->id);

        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $queryBuilder->whereIn('leads.user_id', $userIds);
        }

        $dateRange = request('date_range', 'last_30_days');

        switch ($dateRange) {
            case 'today':
                $queryBuilder->whereDate('leads.created_at', Carbon::today());
                break;
            case 'yesterday':
                $queryBuilder->whereDate('leads.created_at', Carbon::yesterday());
                break;
            case 'this_week':
                $queryBuilder->whereBetween('leads.created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
                break;
            case 'this_month':
                $queryBuilder->whereBetween('leads.created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth(),
                ]);
                break;
            case 'last_30_days':
                $queryBuilder->where('leads.created_at', '>=', Carbon::now()->subDays(30));
                break;
            case 'last_month':
                $queryBuilder->whereBetween('leads.created_at', [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth(),
                ]);
                break;
            case 'this_year':
                $queryBuilder->whereBetween('leads.created_at', [
                    Carbon::now()->startOfYear(),
                    Carbon::now()->endOfYear(),
                ]);
                break;
            case 'date_wise':
                if (request('date_from') && request('date_to')) {
                    $queryBuilder->whereBetween('leads.created_at', [
                        request('date_from').' 00:00:00',
                        request('date_to').' 23:59:59',
                    ]);
                }
                break;
            case 'all':
                break;
        }

        if (! is_null(request()->input('rotten_lead.in'))) {
            $queryBuilder->havingRaw($tablePrefix.'rotten_lead = '.request()->input('rotten_lead.in'));
        }

        $this->addFilter('id', 'leads.id');
        $this->addFilter('user', 'leads.user_id');
        $this->addFilter('sales_person', 'users.name');
        $this->addFilter('lead_source_name', 'lead_sources.id');
        $this->addFilter('lead_type_name', 'lead_types.id');
        $this->addFilter('person_name', 'persons.name');
        $this->addFilter('type', 'lead_pipeline_stages.code');
        $this->addFilter('stage', 'lead_pipeline_stages.id');
        $this->addFilter('tag_name', 'tags.name');
        $this->addFilter('campaign_name', 'products.name');
        $this->addFilter('lead_score', 'persons.lead_score');
        $this->addFilter('expected_close_date', 'leads.expected_close_date');
        $this->addFilter('created_at', 'leads.created_at');
        $this->addFilter('rotten_lead', DB::raw('DATEDIFF(NOW(), '.$tablePrefix.'leads.created_at) >= '.$tablePrefix.'lead_pipelines.rotten_days'));

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
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
            'index' => 'tag_name',
            'label' => trans('admin::app.leads.index.datagrid.tag-name'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'searchable_dropdown',
            'closure' => fn ($row) => $row->tag_name ?? '--',
            'filterable_options' => [
                'repository' => TagRepository::class,
                'column' => [
                    'label' => 'name',
                    'value' => 'name',
                ],
            ],
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
            'index' => 'lead_score',
            'label' => trans('omicslogic::app.datagrid.score'),
            'type' => 'integer',
            'sortable' => true,
            'filterable' => true,
            'closure' => fn ($row) => $this->scoreBadge((int) ($row->lead_score ?? 0)),
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
            'filterable_options' => $this->pipeline->stages->pluck('name', 'id')
                ->map(function ($name, $id) {
                    return ['value' => $id, 'label' => $name];
                })
                ->values()
                ->all(),
        ]);

        $this->addColumn([
            'index' => 'rotten_lead',
            'label' => trans('admin::app.leads.index.datagrid.rotten-lead'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => false,
            'closure' => function ($row) {
                if (! $row->rotten_lead) {
                    return trans('admin::app.leads.index.datagrid.no');
                }

                if (in_array($row->stage_code, ['won', 'lost'])) {
                    return trans('admin::app.leads.index.datagrid.no');
                }

                return trans('admin::app.leads.index.datagrid.yes');
            },
        ]);

        $this->addColumn([
            'index' => 'expected_close_date',
            'label' => trans('admin::app.leads.index.datagrid.date-to'),
            'type' => 'date',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
            'closure' => function ($row) {
                if (! $row->expected_close_date) {
                    return '--';
                }

                return $row->expected_close_date;
            },
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

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('leads.view')) {
            $this->addAction([
                'icon' => 'icon-eye',
                'title' => trans('admin::app.leads.index.datagrid.view'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.leads.view', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('leads.delete')) {
            $this->addAction([
                'icon' => 'icon-delete',
                'title' => trans('admin::app.leads.index.datagrid.delete'),
                'method' => 'delete',
                'url' => fn ($row) => route('admin.leads.delete', $row->id),
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        $this->addMassAction([
            'icon' => 'icon-delete',
            'title' => trans('admin::app.leads.index.datagrid.mass-delete'),
            'method' => 'POST',
            'url' => route('admin.leads.mass_delete'),
        ]);

        $this->addMassAction([
            'title' => trans('admin::app.leads.index.datagrid.mass-update'),
            'url' => route('admin.leads.mass_update'),
            'method' => 'POST',
            'options' => $this->pipeline->stages->map(fn ($stage) => [
                'label' => $stage->name,
                'value' => $stage->id,
            ])->toArray(),
        ]);
    }

    protected function scoreBadge(int $score): string
    {
        $class = match (true) {
            $score >= 75 => 'font-semibold text-green-600 dark:text-green-400',
            $score >= 50 => 'font-semibold text-amber-600 dark:text-amber-400',
            default => 'font-semibold text-gray-500 dark:text-gray-400',
        };

        return '<span class="'.$class.'">'.$score.'</span>';
    }
}
