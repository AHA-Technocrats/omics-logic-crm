<?php

namespace AHATechnocrats\WebForm\DataGrids;

use AHATechnocrats\DataGrid\DataGrid;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class WebFormSubmissionDataGrid extends DataGrid
{
    protected $sortColumn = 'created_at';

    protected $sortOrder = 'desc';

    public function prepareQueryBuilder(): Builder
    {
        $webFormId = (int) request()->input('web_form_id');

        $queryBuilder = DB::table('web_form_submissions')
            ->where('web_form_submissions.web_form_id', $webFormId)
            ->leftJoin('persons', 'web_form_submissions.person_id', '=', 'persons.id')
            ->addSelect(
                'web_form_submissions.id',
                'web_form_submissions.status',
                'web_form_submissions.created_at',
                'web_form_submissions.payload',
                'persons.name as person_name',
            );

        $this->addFilter('id', 'web_form_submissions.id');
        $this->addFilter('status', 'web_form_submissions.status');
        $this->addFilter('created_at', 'web_form_submissions.created_at');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.settings.webforms.responses.datagrid.timestamp'),
            'type' => 'datetime',
            'sortable' => true,
            'filterable' => true,
            'closure' => fn ($row) => core()->formatDate($row->created_at, 'd M Y H:i A'),
        ]);

        $this->addColumn([
            'index' => 'person_name',
            'label' => trans('admin::app.settings.webforms.responses.datagrid.name'),
            'type' => 'string',
            'searchable' => true,
            'closure' => fn ($row) => $row->person_name ?: $this->payloadValue($row, 'name'),
        ]);

        $this->addColumn([
            'index' => 'email',
            'label' => trans('admin::app.settings.webforms.responses.datagrid.email'),
            'type' => 'string',
            'closure' => fn ($row) => $this->payloadEmail($row),
        ]);

        $this->addColumn([
            'index' => 'campaign',
            'label' => trans('admin::app.settings.webforms.responses.datagrid.campaign'),
            'type' => 'string',
            'closure' => fn ($row) => $this->payloadValue($row, 'program_interest'),
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('admin::app.settings.webforms.responses.datagrid.status'),
            'type' => 'string',
            'sortable' => true,
            'filterable' => true,
            'closure' => fn ($row) => ucfirst((string) $row->status),
        ]);
    }

    protected function payloadValue(object $row, string $key): ?string
    {
        $payload = is_string($row->payload) ? json_decode($row->payload, true) : (array) ($row->payload ?? []);
        $person = $payload['persons'] ?? [];

        if ($key === 'name') {
            return $person['name'] ?? null;
        }

        return $person[$key] ?? null;
    }

    protected function payloadEmail(object $row): ?string
    {
        $payload = is_string($row->payload) ? json_decode($row->payload, true) : (array) ($row->payload ?? []);
        $person = $payload['persons'] ?? [];

        return $person['emails'][0]['value'] ?? $person['email'] ?? null;
    }
}
