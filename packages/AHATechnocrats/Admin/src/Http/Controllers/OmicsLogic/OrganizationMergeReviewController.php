<?php

namespace AHATechnocrats\Admin\Http\Controllers\OmicsLogic;

use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\OmicsLogic\Models\OrganizationMergeReviewPair;
use AHATechnocrats\OmicsLogic\Services\OrganizationMergeReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationMergeReviewController extends Controller
{
    public function resolve(Request $request, int $id, OrganizationMergeReviewService $mergeReviewService): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:merge,separate,dismiss',
        ]);

        $pair = OrganizationMergeReviewPair::query()
            ->where('status', 'pending')
            ->findOrFail($id);

        $mergeReviewService->resolve(
            $pair,
            $request->input('action'),
            (int) auth()->guard('user')->id(),
        );

        $message = match ($request->input('action')) {
            'merge' => trans('omicslogic::app.merge-organizations.merged-success'),
            'separate' => trans('omicslogic::app.merge-organizations.separate-success'),
            default => trans('omicslogic::app.merge-organizations.dismiss-success'),
        };

        session()->flash('success', $message);

        return redirect()->route('admin.omics.merge.index', ['tab' => 'organizations']);
    }
}
