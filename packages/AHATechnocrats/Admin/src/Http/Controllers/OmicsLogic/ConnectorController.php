<?php

namespace AHATechnocrats\Admin\Http\Controllers\OmicsLogic;

use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\OmicsLogic\Models\Connector;
use AHATechnocrats\OmicsLogic\Models\ConnectorSyncRun;
use AHATechnocrats\OmicsLogic\Services\ConnectorSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConnectorController extends Controller
{
    public function index(): View
    {
        $this->ensureDefaultConnectors();

        $connectors = Connector::query()->orderBy('name')->get();

        $recentRuns = ConnectorSyncRun::query()
            ->with('connector')
            ->orderByDesc('started_at')
            ->limit(25)
            ->get();

        return view('admin::omics.connectors.index', compact('connectors', 'recentRuns'));
    }

    public function edit(int $id): View
    {
        $connector = Connector::query()->findOrFail($id);

        return view('admin::omics.connectors.edit', compact('connector'));
    }

    public function update(Request $request, int $id, ConnectorSyncService $syncService): RedirectResponse
    {
        $connector = Connector::query()->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:connected,disabled,error',
            'api_url' => 'nullable|url|max:500',
            'api_token' => 'nullable|string|max:500',
            'sync_schedule' => 'nullable|in:manual,hourly,daily,weekly',
        ]);

        $syncService->updateConfig($connector, [
            'name' => $data['name'],
            'status' => $data['status'],
            'config' => array_filter([
                'api_url' => $data['api_url'] ?? null,
                'api_token' => $data['api_token'] ?? null,
                'sync_schedule' => $data['sync_schedule'] ?? 'manual',
            ], fn ($value) => $value !== null && $value !== ''),
        ]);

        session()->flash('success', trans('omicslogic::app.connectors.update-success'));

        return redirect()->route('admin.omics.connectors.index');
    }

    public function sync(int $id, ConnectorSyncService $syncService): RedirectResponse
    {
        $connector = Connector::query()->findOrFail($id);

        if ($connector->type !== 'portal_api') {
            session()->flash('error', trans('omicslogic::app.connectors.sync-not-allowed'));

            return redirect()->route('admin.omics.connectors.index');
        }

        try {
            $run = $syncService->sync($connector);

            session()->flash('success', trans('omicslogic::app.connectors.sync-success', [
                'rows' => $run->rows_total,
                'new' => $run->rows_new,
            ]));
        } catch (\Throwable $exception) {
            session()->flash('error', trans('omicslogic::app.connectors.sync-failed', [
                'message' => $exception->getMessage(),
            ]));
        }

        return redirect()->route('admin.omics.connectors.index');
    }

    protected function ensureDefaultConnectors(): void
    {
        if (Connector::query()->exists()) {
            return;
        }

        foreach ([
            ['type' => 'web_form', 'name' => 'Web Forms', 'status' => 'connected'],
            ['type' => 'portal_api', 'name' => 'OmicsLogic Portal', 'status' => 'disabled'],
            ['type' => 'csv_import', 'name' => 'CSV / Excel Import', 'status' => 'connected'],
        ] as $connector) {
            Connector::query()->create($connector);
        }
    }
}
