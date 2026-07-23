<?php

namespace AHATechnocrats\Admin\DataGrids\Settings;

use AHATechnocrats\DataGrid\DataGrid;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('users')
            ->distinct()
            ->addSelect(
                'users.id',
                'users.name',
                'users.email',
                'users.image',
                'users.status',
                'users.created_at',
                'users.view_permission',
                'roles.name as role_name'
            )
            ->leftJoin('user_groups', 'users.id', '=', 'user_groups.user_id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id');

        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $queryBuilder->whereIn('users.id', $userIds);
        }

        return $queryBuilder;
    }

    /**
     * Add columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.settings.users.index.datagrid.id'),
            'type' => 'string',
            'sortable' => true,
            'width' => '40px',
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('admin::app.settings.users.index.datagrid.name'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
            'width' => '2fr',
            'closure' => function ($row) {
                return [
                    'image' => $row->image ? Storage::url($row->image) : null,
                    'name' => $row->name,
                ];
            },
        ]);

        $this->addColumn([
            'index' => 'role_name',
            'label' => trans('admin::app.settings.users.index.datagrid.role') !== 'admin::app.settings.users.index.datagrid.role' ? trans('admin::app.settings.users.index.datagrid.role') : 'Role',
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
            'width' => '1.5fr',
        ]);

        $this->addColumn([
            'index' => 'view_permission',
            'label' => trans('admin::app.settings.users.index.create.view-permission') ?: 'View Permission',
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
            'width' => '1fr',
            'closure' => function ($row) {
                return ucfirst($row->view_permission);
            }
        ]);

        $this->addColumn([
            'index' => 'email',
            'label' => trans('admin::app.settings.users.index.datagrid.email'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => true,
            'filterable' => true,
            'width' => '2fr',
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('admin::app.settings.users.index.datagrid.status'),
            'type' => 'boolean',
            'filterable' => true,
            'sortable' => true,
            'searchable' => true,
            'width' => '100px',
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.settings.users.index.datagrid.created-at'),
            'type' => 'date',
            'sortable' => true,
            'searchable' => true,
            'filterable_type' => 'date_range',
            'filterable' => true,
            'width' => '150px',
        ]);

        $this->addFilter('id', 'users.id');
        $this->addFilter('name', 'users.name');
        $this->addFilter('email', 'users.email');
        $this->addFilter('status', 'users.status');
        $this->addFilter('created_at', 'users.created_at');
        $this->addFilter('view_permission', 'users.view_permission');
        $this->addFilter('role_name', 'roles.name');
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('settings.user.users.edit')) {
            $this->addAction([
                'index' => 'edit',
                'icon' => 'icon-edit',
                'title' => trans('admin::app.settings.users.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.settings.users.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('settings.user.users.delete')) {
            $this->addAction([
                'index' => 'delete',
                'icon' => 'icon-delete',
                'title' => trans('admin::app.settings.users.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.settings.users.delete', $row->id),
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
            'title' => trans('admin::app.settings.users.index.datagrid.delete'),
            'method' => 'POST',
            'url' => route('admin.settings.users.mass_delete'),
        ]);

        $this->addMassAction([
            'title' => trans('admin::app.settings.users.index.datagrid.update-status'),
            'method' => 'POST',
            'url' => route('admin.settings.users.mass_update'),
            'options' => [
                [
                    'label' => trans('admin::app.settings.users.index.datagrid.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('admin::app.settings.users.index.datagrid.inactive'),
                    'value' => 0,
                ],
            ],
        ]);
    }
}
