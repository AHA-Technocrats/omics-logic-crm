<?php

namespace App\Firebase\Services;

use App\Firebase\Exceptions\FirebaseConnectionException;
use App\Firebase\Repositories\AchievementRepository;
use App\Firebase\Repositories\UserRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected AchievementRepository $achievementRepository,
        protected AchievementTimelineMapper $timelineMapper,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getByEmail(string $email, int $achievementsLimit = 50, ?string $achievementsCursor = null): array
    {
        $this->validateEmail($email);

        try {
            $user = $this->userRepository->findByEmail($email);
        } catch (FirebaseConnectionException) {
            return $this->errorResponse('Unable to fetch data at this time.', 503);
        }

        if ($user === null) {
            return [
                'success' => false,
                'message' => 'User does not exist in the portal. Please verify or update the email address in the CRM if the user exists.',
            ];
        }

        try {
            $achievements = $this->achievementRepository->getUserAchievements(
                (string) $user['uid'],
                $achievementsLimit,
                $achievementsCursor,
            );
        } catch (FirebaseConnectionException) {
            return $this->errorResponse('Unable to fetch data at this time.', 503);
        }

        $timeline = $this->timelineMapper->mapMany($achievements['items']);

        return [
            'success' => true,
            'message' => 'User found successfully.',
            'data' => [
                'user' => $user,
                'achievements' => $achievements['items'],
                'achievements_meta' => $achievements['meta'],
                'timeline' => $timeline,
            ],
        ];
    }

    /**
     * @throws ValidationException
     */
    protected function validateEmail(string $email): void
    {
        Validator::make(['email' => $email], [
            'email' => ['required', 'email'],
        ])->validate();
    }

    /**
     * @return array<string, mixed>
     */
    protected function errorResponse(string $message, int $status = 503): array
    {
        return [
            'success' => false,
            'message' => $message,
            'status' => $status,
        ];
    }
}
