<?php

namespace AHATechnocrats\Admin\Http\Controllers\OmicsLogic;

use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\OmicsLogic\Models\Connector;
use AHATechnocrats\OmicsLogic\Models\ConnectorSyncRun;
use AHATechnocrats\OmicsLogic\Services\ConnectorSyncService;
use AHATechnocrats\WebForm\Repositories\WebFormRepository;
use App\Firebase\Services\ConnectorFirebaseSyncService;
use App\Firebase\Services\FormSyncService;
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

    public function edit(int $id, WebFormRepository $webFormRepository, ConnectorFirebaseSyncService $firebaseSyncService): View
    {
        $connector = Connector::query()->findOrFail($id);

        if ($connector->type === 'portal_api') {
            $firebaseSyncService->ensureWebFormMapping($connector);
            $connector->refresh();
        }

        $webForms = $webFormRepository->all(['id', 'title']);

        $firebaseConfigured = $firebaseSyncService->isFirebaseConfigured();

        $firebaseProjectId = config('firebase.project_id')
            ?: (is_readable((string) config('firebase.credentials'))
                ? (json_decode((string) file_get_contents((string) config('firebase.credentials')), true)['project_id'] ?? null)
                : null);

        return view('admin::omics.connectors.edit', compact(
            'connector',
            'webForms',
            'firebaseConfigured',
            'firebaseProjectId',
        ));
    }

    public function update(Request $request, int $id, ConnectorSyncService $syncService): RedirectResponse
    {
        $connector = Connector::query()->findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'status' => 'required|in:connected,disabled,error',
            'sync_schedule' => 'nullable|in:manual,hourly,daily,weekly',
            'sync_from_date' => 'nullable|date',
        ];

        if ($connector->type === 'portal_api') {
            $rules['web_form_id'] = 'required|integer|exists:web_forms,id';
        } else {
            $rules['web_form_id'] = 'nullable|integer|exists:web_forms,id';
        }

        $data = $request->validate($rules);

        $config = [
            'sync_schedule' => $data['sync_schedule'] ?? ($connector->config['sync_schedule'] ?? 'manual'),
            'sync_from_date' => $data['sync_from_date'] ?? ($connector->config['sync_from_date'] ?? null),
        ];

        if (! empty($data['web_form_id'])) {
            $config['web_form_id'] = (int) $data['web_form_id'];
        }

        $syncService->updateConfig($connector, [
            'name' => $data['name'],
            'status' => $data['status'],
            'config' => array_merge($connector->config ?? [], $config),
        ]);

        session()->flash('success', trans('omicslogic::app.connectors.update-success'));

        return redirect()->route('admin.omics.connectors.edit', $connector->id);
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
                'skipped' => $run->rows_merged,
                'failed' => $run->rows_failed,
            ]));
        } catch (\Throwable $exception) {
            session()->flash('error', trans('omicslogic::app.connectors.sync-failed', [
                'message' => $exception->getMessage(),
            ]));
        }

        return redirect()->route('admin.omics.connectors.index');
    }

    public function resetSync(int $id, FormSyncService $formSyncService): RedirectResponse
    {
        $connector = Connector::query()->findOrFail($id);

        if ($connector->type !== 'portal_api') {
            session()->flash('error', trans('omicslogic::app.connectors.sync-not-allowed'));

            return redirect()->route('admin.omics.connectors.edit', $connector->id);
        }

        $stats = $formSyncService->resetSyncState();

        $connector->update([
            'last_sync_at' => null,
            'last_sync_status' => null,
        ]);

        session()->flash('success', trans('omicslogic::app.connectors.reset-sync-success', [
            'submissions' => $stats['submissions'],
            'leads' => $stats['leads'],
            'persons' => $stats['persons'],
            'merge_pairs' => $stats['merge_pairs'],
            'organizations' => $stats['organizations'],
        ]));

        return redirect()->route('admin.omics.connectors.edit', $connector->id);
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
