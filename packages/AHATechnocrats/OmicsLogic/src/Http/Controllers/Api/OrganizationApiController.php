<?php

namespace AHATechnocrats\OmicsLogic\Http\Controllers\Api;

use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\OmicsLogic\Http\Requests\StoreOrganizationRequest;
use AHATechnocrats\OmicsLogic\Services\OrganizationResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OrganizationApiController extends Controller
{
    public function __construct(
        protected OrganizationResolver $organizationResolver,
    ) {}

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $organizations = Organization::query()
            ->where('name', 'like', '%'.$query.'%')
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'type', 'country_code']);

        return response()->json(['data' => $organizations]);
    }

    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        $organization = $this->organizationResolver->resolve(
            $request->input('name'),
            allowCreate: true,
        );

        return response()->json([
            'data' => $organization,
            'message' => 'Organization ready.',
        ], 201);
    }
}
