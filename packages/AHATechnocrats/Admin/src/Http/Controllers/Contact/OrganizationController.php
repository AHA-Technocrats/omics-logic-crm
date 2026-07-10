<?php

namespace AHATechnocrats\Admin\Http\Controllers\Contact;

use AHATechnocrats\Admin\DataGrids\Contact\OrganizationDataGrid;
use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\Admin\Http\Requests\AttributeForm;
use AHATechnocrats\Admin\Http\Requests\MassDestroyRequest;
use AHATechnocrats\Admin\Traits\AuthorizesOwnerAccess;
use AHATechnocrats\Contact\Repositories\OrganizationRepository;
use AHATechnocrats\OmicsLogic\Services\DeletionTimelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    use AuthorizesOwnerAccess;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected OrganizationRepository $organizationRepository)
    {
        request()->request->add(['entity_type' => 'organizations']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(OrganizationDataGrid::class)->process();
        }

        return view('admin::contacts.organizations.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin::contacts.organizations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AttributeForm $request): RedirectResponse|JsonResponse
    {
        Event::dispatch('contacts.organization.create.before');

        $organization = $this->organizationRepository->create(request()->all());

        Event::dispatch('contacts.organization.create.after', $organization);

        if (request()->ajax()) {
            return response()->json([
                'data' => $organization,
                'message' => trans('admin::app.contacts.organizations.index.create-success'),
            ]);
        }

        session()->flash('success', trans('admin::app.contacts.organizations.index.create-success'));

        return redirect()->route('admin.contacts.organizations.index');
    }

    /**
     * Display the specified organization.
     */
    public function show(int $id): View|RedirectResponse
    {
        $organization = $this->organizationRepository
            ->with(['persons.primaryProduct', 'accountOwner'])
            ->findOrFail($id);

        if ($redirect = $this->authorizeOrganizationAccess($organization)) {
            return $redirect;
        }

        $customers = $organization->persons->where('lifecycle_stage', 'customer')->count();

        $stats = [
            'contacts' => $organization->persons->count(),
            'engaged' => $organization->persons->where('lifecycle_stage', 'engaged')->count(),
            'customers' => $customers,
            'estimated_value' => $customers * 1200,
        ];

        $topProgram = $organization->persons
            ->pluck('primaryProduct.name')
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        return view('admin::contacts.organizations.view', compact('organization', 'stats', 'topProgram'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $organization = $this->organizationRepository
            ->with(['accountOwner'])
            ->findOrFail($id);

        if ($redirect = $this->authorizeOrganizationAccess($organization)) {
            return $redirect;
        }

        return view('admin::contacts.organizations.edit', compact('organization'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AttributeForm $request, int $id): RedirectResponse
    {
        $organization = $this->organizationRepository->findOrFail($id);

        if ($redirect = $this->authorizeOrganizationAccess($organization)) {
            return $redirect;
        }

        Event::dispatch('contacts.organization.update.before', $id);

        $organization = $this->organizationRepository->update(request()->all(), $id);

        Event::dispatch('contacts.organization.update.after', $organization);

        session()->flash('success', trans('admin::app.contacts.organizations.index.update-success'));

        return redirect()->route('admin.contacts.organizations.index');
    }

    /**
     * Search organization results.
     */
    public function search(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $results = $this->organizationRepository->findWhere([
            ['name', 'like', '%' . urldecode(request()->input('query')) . '%'],
        ]);

        return \Illuminate\Http\Resources\Json\JsonResource::collection($results);
    }

    /**
     * Preview what must be deleted before this organization can be removed.
     */
    public function deletePreview(int $id): JsonResponse
    {
        $organization = $this->organizationRepository->findOrFail($id);

        if ($this->authorizeOrganizationAccess($organization)) {
            return response()->json([
                'message' => trans('admin::app.errors.401'),
            ], 401);
        }

        return response()->json(
            app(DeletionTimelineService::class)->organizationTimeline($organization)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $organization = $this->organizationRepository->findOrFail($id);

        if ($this->authorizeOrganizationAccess($organization)) {
            return response()->json([
                'message' => trans('admin::app.errors.401'),
            ], 401);
        }

        if ($organization->persons()->exists()) {
            return response()->json([
                'message' => trans('omicslogic::app.delete-timeline.organization-has-persons'),
            ], 422);
        }

        try {
            Event::dispatch('contact.organization.delete.before', $id);

            $this->organizationRepository->delete($id);

            Event::dispatch('contact.organization.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.contacts.organizations.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.contacts.organizations.index.delete-failed'),
            ], 400);
        }
    }

    /**
     * Mass Delete the specified resources.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $organizations = $this->organizationRepository->findWhereIn('id', $massDestroyRequest->input('indices'));

        foreach ($organizations as $organization) {
            if ($this->authorizeOrganizationAccess($organization)) {
                continue;
            }

            Event::dispatch('contact.organization.delete.before', $organization);

            $this->organizationRepository->delete($organization->id);

            Event::dispatch('contact.organization.delete.after', $organization);
        }

        return response()->json([
            'message' => trans('admin::app.contacts.organizations.index.delete-success'),
        ]);
    }

    /**
     * Ensure the current user can access the given organization record.
     */
    protected function authorizeOrganizationAccess($organization): ?RedirectResponse
    {
        return $this->authorizeAnyOwner(
            [$organization->account_owner_id, $organization->user_id],
            'admin.contacts.organizations.index'
        );
    }
}
