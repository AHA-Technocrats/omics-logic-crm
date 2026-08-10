<?php

namespace AHATechnocrats\Admin\Http\Controllers\Settings;

use AHATechnocrats\Admin\DataGrids\Settings\CampaignCategoryDataGrid;
use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\Product\Repositories\CampaignCategoryRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;

class CampaignCategoryController extends Controller
{
    public function __construct(protected CampaignCategoryRepository $campaignCategoryRepository) {}

    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(CampaignCategoryDataGrid::class)->process();
        }

        return view('admin::settings.campaign-categories.index');
    }

    public function store(): JsonResponse
    {
        $name = trim((string) request()->input('name'));

        request()->merge(['name' => $name]);

        $this->validate(request(), [
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($this->campaignCategoryRepository->nameExists($name)) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.campaign-categories.index.name-exists'),
                'errors' => [
                    'name' => [trans('admin::app.settings.campaign-categories.index.name-exists')],
                ],
            ], 422);
        }

        Event::dispatch('settings.campaign_category.create.before');

        $category = $this->campaignCategoryRepository->create(['name' => $name]);

        Event::dispatch('settings.campaign_category.create.after', $category);

        return new JsonResponse([
            'data' => $category,
            'message' => trans('admin::app.settings.campaign-categories.index.create-success'),
        ]);
    }

    public function edit(int $id): View|JsonResponse
    {
        $category = $this->campaignCategoryRepository->findOrFail($id);

        return new JsonResponse([
            'data' => $category,
        ]);
    }

    public function update(int $id): JsonResponse
    {
        $name = trim((string) request()->input('name'));

        request()->merge(['name' => $name]);

        $this->validate(request(), [
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($this->campaignCategoryRepository->nameExists($name, $id)) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.campaign-categories.index.name-exists'),
                'errors' => [
                    'name' => [trans('admin::app.settings.campaign-categories.index.name-exists')],
                ],
            ], 422);
        }

        Event::dispatch('settings.campaign_category.update.before', $id);

        $category = $this->campaignCategoryRepository->rename($id, $name);

        Event::dispatch('settings.campaign_category.update.after', $category);

        return new JsonResponse([
            'data' => $category,
            'message' => trans('admin::app.settings.campaign-categories.index.update-success'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = $this->campaignCategoryRepository->findOrFail($id);

        if ($category->products()->count() > 0) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.campaign-categories.index.delete-failed-associated-campaigns'),
            ], 400);
        }

        try {
            Event::dispatch('settings.campaign_category.delete.before', $id);

            $category->delete();

            Event::dispatch('settings.campaign_category.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.settings.campaign-categories.index.delete-success'),
            ], 200);
        } catch (Exception $exception) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.campaign-categories.index.delete-failed'),
            ], 400);
        }
    }

    /**
     * Options for Select2 (id + text), ordered A–Z.
     */
    public function options(): JsonResponse
    {
        $categories = $this->campaignCategoryRepository
            ->getModel()
            ->newQuery()
            ->orderBy('name')
            ->get(['id', 'name']);

        return new JsonResponse([
            'data' => $categories->map(fn ($category) => [
                'id' => $category->id,
                'text' => $category->name,
            ])->values(),
        ]);
    }
}
