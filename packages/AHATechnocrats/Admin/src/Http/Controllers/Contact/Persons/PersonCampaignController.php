<?php

namespace AHATechnocrats\Admin\Http\Controllers\Contact\Persons;

use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\Admin\Traits\AuthorizesOwnerAccess;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class PersonCampaignController extends Controller
{
    use AuthorizesOwnerAccess;

    public function __construct(protected PersonRepository $personRepository) {}

    public function index(int $id): JsonResponse|RedirectResponse
    {
        $person = $this->personRepository->findOrFail($id);

        if ($redirect = $this->authorizeOwner($person->user_id, 'admin.contacts.persons.index')) {
            return $redirect;
        }

        $leads = $person->leads()
            ->with(['stage', 'source', 'products.product'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $leads->map(fn ($lead) => [
                'id' => $lead->id,
                'title' => $lead->title,
                'campaign' => $lead->products->first()?->product?->name,
                'stage' => $lead->stage?->name,
                'source' => $lead->source?->name,
                'created_at' => $lead->created_at?->toIso8601String(),
                'relative' => $lead->created_at?->diffForHumans(),
                'view_url' => route('admin.leads.view', $lead->id),
            ])->values(),
        ]);
    }

    public function show(int $id, int $leadId): JsonResponse|RedirectResponse
    {
        $person = $this->personRepository->findOrFail($id);

        if ($redirect = $this->authorizeOwner($person->user_id, 'admin.contacts.persons.index')) {
            return $redirect;
        }

        $lead = $person->leads()
            ->with(['stage', 'source', 'products.product'])
            ->findOrFail($leadId);

        return response()->json([
            'data' => [
                'id' => $lead->id,
                'title' => $lead->title,
                'description' => $lead->description,
                'campaign' => $lead->products->first()?->product?->name,
                'stage' => $lead->stage?->name,
                'source' => $lead->source?->name,
                'created_at' => $lead->created_at?->toIso8601String(),
                'relative' => $lead->created_at?->diffForHumans(),
                'view_url' => route('admin.leads.view', $lead->id),
            ],
        ]);
    }
}
