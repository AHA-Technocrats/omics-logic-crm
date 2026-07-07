<?php

namespace AHATechnocrats\Admin\Http\Controllers\OmicsLogic;

use AHATechnocrats\Admin\DataGrids\OmicsLogic\SegmentDataGrid;
use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\Admin\Http\Requests\MassDestroyRequest;
use AHATechnocrats\OmicsLogic\Models\Segment;
use AHATechnocrats\OmicsLogic\Services\SegmentFilterCounter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SegmentController extends Controller
{
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(SegmentDataGrid::class)->process();
        }

        return view('admin::omics.segments.index');
    }

    public function create(): View
    {
        return view('admin::omics.segments.create');
    }

    public function store(Request $request, SegmentFilterCounter $counter): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'refresh_schedule' => 'required|in:manual,daily,weekly',
            'owner_id' => 'nullable|integer',
            'is_shared' => 'sometimes|boolean',
        ]);

        $filterQuery = $counter->buildFilterQueryFromRequest($request->all());

        $segment = Segment::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'filter_query' => $filterQuery,
            'owner_id' => $data['owner_id'] ?: auth()->guard('user')->id(),
            'refresh_schedule' => $data['refresh_schedule'],
            'is_shared' => $request->boolean('is_shared'),
            'contact_count_cached' => $counter->countForFilters($filterQuery),
            'last_refreshed_at' => now(),
        ]);

        session()->flash('success', trans('omicslogic::app.segments.create-success'));

        return redirect()->route('admin.omics.segments.index');
    }

    public function edit(int $id): View
    {
        $segment = Segment::findOrFail($id);

        return view('admin::omics.segments.edit', compact('segment'));
    }

    public function update(Request $request, int $id, SegmentFilterCounter $counter): RedirectResponse
    {
        $segment = Segment::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'refresh_schedule' => 'required|in:manual,daily,weekly',
            'owner_id' => 'nullable|integer',
            'is_shared' => 'sometimes|boolean',
        ]);

        $filterQuery = $counter->buildFilterQueryFromRequest($request->all());

        $segment->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'filter_query' => $filterQuery,
            'owner_id' => $data['owner_id'] ?: null,
            'refresh_schedule' => $data['refresh_schedule'],
            'is_shared' => $request->boolean('is_shared'),
            'contact_count_cached' => $counter->countForFilters($filterQuery),
            'last_refreshed_at' => now(),
        ]);

        session()->flash('success', trans('omicslogic::app.segments.update-success'));

        return redirect()->route('admin.omics.segments.index');
    }

    public function destroy(int $id): JsonResponse
    {
        Segment::findOrFail($id)->delete();

        return new JsonResponse([
            'message' => trans('omicslogic::app.segments.delete-success'),
        ]);
    }

    public function massDestroy(MassDestroyRequest $request): JsonResponse
    {
        foreach ($request->input('indices') as $id) {
            Segment::where('id', $id)->delete();
        }

        return new JsonResponse([
            'message' => trans('omicslogic::app.segments.delete-success'),
        ]);
    }
}
