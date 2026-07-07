<?php

namespace AHATechnocrats\Admin\Http\Controllers\OmicsLogic;

use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\OmicsLogic\Models\MergeReviewPair;
use AHATechnocrats\OmicsLogic\Models\OrganizationMergeReviewPair;
use AHATechnocrats\OmicsLogic\Services\MergeReviewService;
use AHATechnocrats\OmicsLogic\Services\OrganizationMergeReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MergeReviewController extends Controller
{
    public function index(
        MergeReviewService $mergeReviewService,
        OrganizationMergeReviewService $organizationMergeReviewService,
    ): View {
        $activeTab = request('tab') === 'organizations' ? 'organizations' : 'persons';

        $stats = $mergeReviewService->getQueueStats();

        $pairs = MergeReviewPair::query()
            ->with(['personA.organization', 'personB.organization'])
            ->where('status', 'pending')
            ->orderByDesc('confidence')
            ->limit(50)
            ->get()
            ->filter(fn (MergeReviewPair $pair) => $pair->personA && $pair->personB)
            ->map(function (MergeReviewPair $pair) use ($mergeReviewService) {
                return [
                    'pair' => $pair,
                    'person_a' => $mergeReviewService->personSnapshot($pair->personA),
                    'person_b' => $mergeReviewService->personSnapshot($pair->personB),
                ];
            });

        $organizationStats = $organizationMergeReviewService->getQueueStats();

        $organizationPairs = OrganizationMergeReviewPair::query()
            ->with(['organizationA', 'organizationB'])
            ->where('status', 'pending')
            ->orderByDesc('confidence')
            ->limit(50)
            ->get()
            ->filter(fn (OrganizationMergeReviewPair $pair) => $pair->organizationA && $pair->organizationB)
            ->map(function (OrganizationMergeReviewPair $pair) use ($organizationMergeReviewService) {
                return [
                    'pair' => $pair,
                    'organization_a' => $organizationMergeReviewService->organizationSnapshot($pair->organizationA),
                    'organization_b' => $organizationMergeReviewService->organizationSnapshot($pair->organizationB),
                ];
            });

        return view('admin::omics.merge.index', [
            'activeTab' => $activeTab,
            'pairs' => $pairs,
            'stats' => $stats,
            'pendingCount' => $stats['pending'],
            'organizationPairs' => $organizationPairs,
            'organizationStats' => $organizationStats,
            'organizationPendingCount' => $organizationStats['pending'],
        ]);
    }

    public function resolve(Request $request, int $id, MergeReviewService $mergeReviewService): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:merge,separate,dismiss',
        ]);

        $pair = MergeReviewPair::query()
            ->where('status', 'pending')
            ->findOrFail($id);

        $mergeReviewService->resolve(
            $pair,
            $request->input('action'),
            (int) auth()->guard('user')->id(),
        );

        $message = match ($request->input('action')) {
            'merge' => trans('omicslogic::app.merge.merged-success'),
            'separate' => trans('omicslogic::app.merge.separate-success'),
            default => trans('omicslogic::app.merge.dismiss-success'),
        };

        session()->flash('success', $message);

        return redirect()->route('admin.omics.merge.index', ['tab' => 'persons']);
    }
}
