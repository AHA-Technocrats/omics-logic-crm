<?php

namespace AHATechnocrats\Admin\Http\Controllers\Contact\Persons;

use AHATechnocrats\Admin\DataGrids\Contact\PersonDataGrid;
use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\Admin\Http\Requests\AttributeForm;
use AHATechnocrats\Admin\Http\Requests\MassDestroyRequest;
use AHATechnocrats\Admin\Http\Resources\PersonResource;
use AHATechnocrats\Admin\Traits\AuthorizesOwnerAccess;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use AHATechnocrats\OmicsLogic\Services\DeletionTimelineService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Prettus\Repository\Criteria\RequestCriteria;

class PersonController extends Controller
{
    use AuthorizesOwnerAccess;

    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct(protected PersonRepository $personRepository)
    {
        request()->request->add(['entity_type' => 'persons']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(PersonDataGrid::class)->process();
        }

        return view('admin::contacts.persons.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin::contacts.persons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AttributeForm $request): RedirectResponse|JsonResponse
    {
        Event::dispatch('contacts.person.create.before');

        $person = $this->personRepository->create($request->all());

        Event::dispatch('contacts.person.create.after', $person);

        if (request()->ajax()) {
            return response()->json([
                'data' => $person,
                'message' => trans('admin::app.contacts.persons.index.create-success'),
            ]);
        }

        session()->flash('success', trans('admin::app.contacts.persons.index.create-success'));

        return redirect()->route('admin.contacts.persons.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View|RedirectResponse
    {
        $person = $this->personRepository
            ->with([
                'organization.accountOwner',
                'user',
            ])
            ->findOrFail($id);

        if ($redirect = $this->authorizeOwner($person->user_id, 'admin.contacts.persons.index')) {
            return $redirect;
        }

        return view('admin::contacts.persons.view', compact('person'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $person = $this->personRepository
            ->with(['organization.accountOwner', 'user', 'primarySource', 'primaryProduct'])
            ->findOrFail($id);

        if ($redirect = $this->authorizeOwner($person->user_id, 'admin.contacts.persons.index')) {
            return $redirect;
        }

        return view('admin::contacts.persons.edit', compact('person'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AttributeForm $request, int $id): RedirectResponse|JsonResponse
    {
        $person = $this->personRepository->findOrFail($id);

        if ($redirect = $this->authorizeOwner($person->user_id, 'admin.contacts.persons.index')) {
            if (request()->ajax()) {
                return response()->json([
                    'message' => trans('admin::app.errors.401'),
                ], 401);
            }

            return $redirect;
        }

        Event::dispatch('contacts.person.update.before', $id);

        $person = $this->personRepository->update($request->all(), $id);

        Event::dispatch('contacts.person.update.after', $person);

        if (request()->ajax()) {
            return response()->json([
                'data' => $person,
                'message' => trans('admin::app.contacts.persons.index.update-success'),
            ], 200);
        }

        session()->flash('success', trans('admin::app.contacts.persons.index.update-success'));

        if (request()->input('_redirect') === 'view') {
            return redirect()->route('admin.contacts.persons.view', $id);
        }

        return redirect()->route('admin.contacts.persons.index');
    }

    /**
     * Search person results.
     */
    public function search(): JsonResource
    {
        $personRepository = $this->personRepository
            ->pushCriteria(app(RequestCriteria::class));

        if ($searchTerm = request()->query('query')) {
            $personRepository = $personRepository->scopeQuery(function ($query) use ($searchTerm) {
                return $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%')
                        ->orWhere('emails', 'like', '%'.$searchTerm.'%')
                        ->orWhere('contact_numbers', 'like', '%'.$searchTerm.'%');
                });
            });
        }

        if ($userIds = bouncer()->getAuthorizedUserIds()) {
            $persons = $personRepository->with(['organization'])->findWhereIn('user_id', $userIds);
        } else {
            $persons = $personRepository->with(['organization'])->all();
        }

        return PersonResource::collection($persons);
    }

    /**
     * Preview what must be deleted before this person can be removed.
     */
    public function deletePreview(int $id): JsonResponse
    {
        $person = $this->personRepository->findOrFail($id);

        if ($redirect = $this->authorizeOwner($person->user_id, 'admin.contacts.persons.index')) {
            return response()->json([
                'message' => trans('admin::app.errors.401'),
            ], 401);
        }

        return response()->json(
            app(DeletionTimelineService::class)->personTimeline($person)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $person = $this->personRepository->findOrFail($id);

        if ($redirect = $this->authorizeOwner($person->user_id, 'admin.contacts.persons.index')) {
            return response()->json([
                'message' => trans('admin::app.errors.401'),
            ], 401);
        }

        try {
            Event::dispatch('contacts.person.delete.before', $person);

            $this->personRepository->delete($id);

            Event::dispatch('contacts.person.delete.after', $person);

            return response()->json([
                'message' => trans('admin::app.contacts.persons.index.delete-success'),
            ], 200);

        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage() ?: trans('admin::app.contacts.persons.index.delete-failed'),
            ], 400);
        }
    }

    /**
     * Mass destroy the specified resources from storage.
     */
    public function massDestroy(MassDestroyRequest $request): JsonResponse
    {
        try {
            $persons = $this->personRepository->findWhereIn('id', $request->input('indices', []));

            $deletedCount = 0;

            $blockedCount = 0;

            foreach ($persons as $person) {
                if ($this->authorizeOwner($person->user_id, 'admin.contacts.persons.index')) {
                    $blockedCount++;

                    continue;
                }

                try {
                    Event::dispatch('contacts.person.delete.before', $person);

                    $this->personRepository->delete($person->id);

                    Event::dispatch('contacts.person.delete.after', $person);

                    $deletedCount++;
                } catch (Exception) {
                    $blockedCount++;
                }
            }

            $statusCode = 200;

            switch (true) {
                case $deletedCount > 0 && $blockedCount === 0:
                    $message = trans('admin::app.contacts.persons.index.all-delete-success');

                    break;

                case $deletedCount > 0 && $blockedCount > 0:
                    $message = trans('admin::app.contacts.persons.index.partial-delete-warning');

                    break;

                case $deletedCount === 0 && $blockedCount > 0:
                    $message = trans('admin::app.contacts.persons.index.none-delete-warning');

                    $statusCode = 400;

                    break;

                default:
                    $message = trans('admin::app.contacts.persons.index.no-selection');

                    $statusCode = 400;

                    break;
            }

            return response()->json(['message' => $message], $statusCode);
        } catch (Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.contacts.persons.index.delete-failed'),
            ], 400);
        }
    }
}
