<?php

namespace AHATechnocrats\Admin\Http\Controllers\OmicsLogic;

use AHATechnocrats\Admin\DataGrids\OmicsLogic\AuditLogDataGrid;
use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\OmicsLogic\Models\AuditLog;
use AHATechnocrats\OmicsLogic\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(AuditLogDataGrid::class)->process();
        }

        return view('admin::omics.audit.index');
    }

    public function show(int $id): View
    {
        $log = AuditLog::query()->findOrFail($id);

        return view('admin::omics.audit.show', compact('log'));
    }

    public function undo(int $id): RedirectResponse
    {
        $log = AuditLog::query()->findOrFail($id);

        if (! AuditLogger::undo($log)) {
            session()->flash('error', trans('omicslogic::app.audit.undo-failed'));
        } else {
            session()->flash('success', trans('omicslogic::app.audit.undo-success'));
        }

        return redirect()->route('admin.omics.audit.index');
    }
}
