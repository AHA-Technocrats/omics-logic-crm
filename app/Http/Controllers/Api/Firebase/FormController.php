<?php

namespace App\Http\Controllers\Api\Firebase;

use App\Firebase\Services\FormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FormController extends Controller
{
    public function __construct(protected FormService $formService) {}

    public function index(Request $request): JsonResponse
    {
        $result = $this->formService->getForms($request->query());

        $status = $result['status'] ?? 200;

        unset($result['status']);

        return response()->json($result, $status);
    }
}
