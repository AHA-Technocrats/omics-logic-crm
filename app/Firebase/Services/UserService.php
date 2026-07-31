<?php

namespace App\Firebase\Services;

use App\Firebase\Exceptions\FirebaseConnectionException;
use App\Firebase\Repositories\AchievementRepository;
use App\Firebase\Repositories\PurchaseRepository;
use App\Firebase\Repositories\UserRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        protected UserRepository $userRepository,
        protected AchievementRepository $achievementRepository,
        protected PurchaseRepository $purchaseRepository,
        protected AchievementTimelineMapper $timelineMapper,
        protected PurchaseHistoryMapper $purchaseMapper,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getByEmail(
        string $email,
        int $achievementsLimit = 50,
        ?string $achievementsCursor = null,
        int $purchasesLimit = 50,
        ?string $purchasesCursor = null,
    ): array {
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

        $uid = (string) $user['uid'];

        try {
            $achievements = $this->achievementRepository->getUserAchievements(
                $uid,
                $achievementsLimit,
                $achievementsCursor,
            );
        } catch (FirebaseConnectionException) {
            return $this->errorResponse('Unable to fetch data at this time.', 503);
        }

        try {
            $purchases = $this->purchaseRepository->getUserPurchases(
                $uid,
                $purchasesLimit,
                $purchasesCursor,
            );
        } catch (FirebaseConnectionException $exception) {
            report($exception);

            // Ordered Purchases queries can fail when documents lack the order
            // field. Retry without ordering so real purchases still surface.
            try {
                $purchases = $this->purchaseRepository->getUserPurchasesUnordered($uid, $purchasesLimit);
            } catch (FirebaseConnectionException $retryException) {
                report($retryException);

                $purchases = [
                    'items' => [],
                    'meta' => [
                        'limit' => $purchasesLimit,
                        'has_more' => false,
                        'next_cursor' => null,
                    ],
                ];
            }
        }

        $timeline = $this->timelineMapper->mapMany($achievements['items']);
        $purchaseHistory = $this->resolvePurchaseHistory($purchases['items'], $achievements['items']);

        return [
            'success' => true,
            'message' => 'User found successfully.',
            'data' => [
                'user' => $user,
                'achievements' => $achievements['items'],
                'achievements_meta' => $achievements['meta'],
                'timeline' => $timeline,
                'purchases' => $purchases['items'],
                'purchases_meta' => $purchases['meta'],
                'purchase_history' => $purchaseHistory,
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $purchases
     * @param  array<int, array<string, mixed>>  $achievements
     * @return array<int, array<string, mixed>>
     */
    protected function resolvePurchaseHistory(array $purchases, array $achievements): array
    {
        $mappedPurchases = ! empty($purchases)
            ? $this->purchaseMapper->mapMany($purchases)
            : [];

        $mappedFromAchievements = $this->purchaseMapper->mapManyFromAchievements($achievements);

        if (empty($mappedPurchases)) {
            return $mappedFromAchievements;
        }

        if (empty($mappedFromAchievements)) {
            return $mappedPurchases;
        }

        $combined = [];

        foreach (array_merge($mappedPurchases, $mappedFromAchievements) as $item) {
            $key = implode('|', [
                (string) ($item['id'] ?? ''),
                (string) ($item['title'] ?? ''),
                (string) ($item['occurred_at'] ?? ''),
            ]);

            if (! isset($combined[$key])) {
                $combined[$key] = $item;
            }
        }

        $items = array_values($combined);

        usort($items, function (array $a, array $b) {
            return strcmp((string) ($b['occurred_at'] ?? ''), (string) ($a['occurred_at'] ?? ''));
        });

        return $items;
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
