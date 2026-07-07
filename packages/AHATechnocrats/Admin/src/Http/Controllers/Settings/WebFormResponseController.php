<?php

namespace AHATechnocrats\Admin\Http\Controllers\Settings;

use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\WebForm\DataGrids\WebFormSubmissionDataGrid;
use AHATechnocrats\WebForm\Models\WebFormSubmission;
use AHATechnocrats\WebForm\Repositories\WebFormRepository;
use AHATechnocrats\WebForm\Services\WebFormSubmissionExport;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WebFormResponseController extends Controller
{
    public function __construct(
        protected WebFormRepository $webFormRepository,
    ) {}

    public function index(int $id): View|JsonResponse
    {
        $webForm = $this->webFormRepository->findOrFail($id);

        if (request()->ajax()) {
            request()->merge(['web_form_id' => $id]);

            return datagrid(WebFormSubmissionDataGrid::class)->process();
        }

        $submissionCount = WebFormSubmission::query()
            ->where('web_form_id', $id)
            ->count();

        return view('admin::settings.web-forms.responses', compact('webForm', 'submissionCount'));
    }

    public function export(int $id): BinaryFileResponse
    {
        $webForm = $this->webFormRepository->findOrFail($id);

        $submissions = WebFormSubmission::query()
            ->where('web_form_id', $id)
            ->orderByDesc('created_at')
            ->get();

        $filename = sprintf(
            '%s-responses-%s.xlsx',
            str($webForm->title)->slug(),
            now()->format('Y-m-d')
        );

        return Excel::download(
            new WebFormSubmissionExport($webForm, $submissions),
            $filename
        );
    }
}
