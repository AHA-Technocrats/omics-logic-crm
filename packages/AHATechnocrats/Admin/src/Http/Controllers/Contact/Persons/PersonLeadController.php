<?php

namespace AHATechnocrats\Admin\Http\Controllers\Contact\Persons;

use AHATechnocrats\Admin\DataGrids\Contact\PersonLeadDataGrid;
use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\Admin\Traits\AuthorizesOwnerAccess;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PersonLeadController extends Controller
{
    use AuthorizesOwnerAccess;

    public function __construct(protected PersonRepository $personRepository) {}

    public function index(int $id): View|JsonResponse|RedirectResponse
    {
        $person = $this->personRepository->findOrFail($id);

        if ($redirect = $this->authorizeOwner($person->user_id, 'admin.contacts.persons.index')) {
            return $redirect;
        }

        if (request()->ajax()) {
            request()->merge(['person_id' => $id]);

            return datagrid(PersonLeadDataGrid::class)->process();
        }

        return view('admin::contacts.persons.leads.index', compact('person'));
    }
}
