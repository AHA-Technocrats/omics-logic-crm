<?php

namespace App\Http\Controllers\Api\Firebase;

use App\Firebase\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function show(Request $request): JsonResponse
    {
        try {
            $result = $this->userService->getByEmail(
                (string) $request->query('email', ''),
                (int) $request->query('achievements_limit', 50),
                $request->query('achievements_cursor'),
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'A valid email address is required.',
                'errors' => $exception->errors(),
            ], 422);
        }

        $status = $result['status'] ?? 200;

        unset($result['status']);

        return response()->json($result, $status);
    }
}
