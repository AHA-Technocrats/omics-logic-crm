<?php

namespace AHATechnocrats\Admin\Http\Controllers\Contact\Persons;

use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\Admin\Traits\AuthorizesOwnerAccess;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use App\Firebase\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class PersonPortalController extends Controller
{
    use AuthorizesOwnerAccess;

    public function __construct(
        protected PersonRepository $personRepository,
        protected UserService $userService,
    ) {}

    public function show(int $id): JsonResponse|RedirectResponse
    {
        $person = $this->personRepository->findOrFail($id);

        if ($redirect = $this->authorizeOwner($person->user_id, 'admin.contacts.persons.index')) {
            return $redirect;
        }

        $email = $person->emails[0]['value'] ?? null;

        if (! $email) {
            return response()->json([
                'success' => false,
                'message' => 'User does not exist in the portal. Please verify or update the email address in the CRM if the user exists.',
            ]);
        }

        try {
            $result = $this->userService->getByEmail($email, 50);
        } catch (ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'User does not exist in the portal. Please verify or update the email address in the CRM if the user exists.',
            ]);
        }

        $status = $result['status'] ?? 200;

        unset($result['status']);

        return response()->json($result, $status);
    }
}
