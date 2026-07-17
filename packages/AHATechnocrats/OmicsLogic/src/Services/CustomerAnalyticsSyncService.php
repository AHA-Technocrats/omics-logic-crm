<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\OmicsLogic\Models\AnalyticsEnrollment;
use AHATechnocrats\OmicsLogic\Models\AnalyticsUser;
use App\Firebase\Repositories\AchievementRepository;
use App\Firebase\Repositories\FormRepository;
use App\Firebase\Repositories\PurchaseRepository;
use App\Firebase\Repositories\UserRepository;
use App\Firebase\Services\AchievementTimelineMapper;
use App\Firebase\Services\PurchaseHistoryMapper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CustomerAnalyticsSyncService
{
    public function __construct(
        protected FormRepository $formRepository,
        protected UserRepository $userRepository,
        protected AchievementRepository $achievementRepository,
        protected PurchaseRepository $purchaseRepository,
        protected AchievementTimelineMapper $timelineMapper,
        protected PurchaseHistoryMapper $purchaseMapper,
    ) {}

    public function syncAll(): array
    {
        Log::info('Starting Customer Analytics Sync');
        
        $stats = [
            'users_updated' => 0,
            'enrollments_synced' => 0,
        ];

        // 1. Sync Demographic data from Forms
        $this->syncForms($stats);

        // 2. Sync UIDs from Users
        $this->syncUsers($stats);

        // 3. Sync Enrollments (Achievements & Purchases) for all synced users with UIDs
        $this->syncEnrollments($stats);

        Cache::put('omics_analytics_last_sync', now()->toIso8601String());

        Log::info('Finished Customer Analytics Sync', $stats);
        
        return $stats;
    }

    protected function syncForms(array &$stats): void
    {
        $cursor = null;
        $formFieldMap = config('firebase.forms.field_map', []);
        
        do {
            $forms = $this->formRepository->getPaginatedForms(100, $cursor);
            
            foreach ($forms['items'] as $form) {
                $email = $this->getFirstField($form, $formFieldMap['email'] ?? ['email']);
                if (! $email) {
                    continue;
                }

                $country = $this->getFirstField($form, $formFieldMap['country'] ?? ['country']);
                $organization = $this->getFirstField($form, $formFieldMap['organization'] ?? ['organization']);
                $education = $this->getFirstField($form, $formFieldMap['education'] ?? ['education']);
                $name = $this->getFirstField($form, $formFieldMap['name'] ?? ['name']);

                AnalyticsUser::updateOrCreate(
                    ['email' => strtolower($email)],
                    [
                        'name' => $name,
                        'country' => $country,
                        'organization' => $organization,
                        'education' => $education,
                    ]
                );
                
                $stats['users_updated']++;
            }
            
            $cursor = $forms['meta']['next_cursor'];
        } while ($forms['meta']['has_more']);
    }

    protected function syncUsers(array &$stats): void
    {
        $cursor = null;
        
        do {
            $users = $this->userRepository->getUsers(100, $cursor);
            
            foreach ($users['items'] as $user) {
                $email = $user['email'] ?? null;
                $uid = $user['uid'] ?? null;
                
                if (! $email || ! $uid) {
                    continue;
                }

                AnalyticsUser::updateOrCreate(
                    ['email' => strtolower($email)],
                    [
                        'uid' => $uid,
                        'name' => $user['displayName'] ?? $user['name'] ?? null,
                    ]
                );
            }
            
            $cursor = $users['meta']['next_cursor'];
        } while ($users['meta']['has_more']);
    }

    protected function syncEnrollments(array &$stats): void
    {
        // Only fetch enrollments for users who actually have a UID
        AnalyticsUser::whereNotNull('uid')->chunk(100, function ($users) use (&$stats) {
            foreach ($users as $user) {
                $uid = $user->uid;
                
                // Fetch achievements
                $achievementsRes = $this->achievementRepository->getUserAchievements($uid, 1000);
                $achievements = $achievementsRes['items'];
                $mappedAchievements = $this->timelineMapper->mapMany($achievements);
                
                foreach ($mappedAchievements as $item) {
                    $this->upsertEnrollment($user, $item, 'achievement');
                    $stats['enrollments_synced']++;
                }

                // Fetch purchases
                try {
                    $purchasesRes = $this->purchaseRepository->getUserPurchases($uid, 1000);
                    $purchases = $purchasesRes['items'];
                    $mappedPurchases = $this->purchaseMapper->mapMany($purchases);
                    
                    foreach ($mappedPurchases as $item) {
                        $this->upsertEnrollment($user, $item, 'purchase');
                        $stats['enrollments_synced']++;
                    }
                } catch (\Throwable $e) {
                    // Purchase collection might not exist for this user, safely ignore
                }
            }
        });
    }

    protected function upsertEnrollment(AnalyticsUser $user, array $item, string $sourceType): void
    {
        if (empty($item['id']) && empty($item['title'])) {
            return;
        }

        // Generate a deterministic ID if none provided by Firebase
        $enrollmentId = $item['id'] ?? md5($user->uid . '-' . $item['title'] . '-' . ($item['occurred_at'] ?? ''));

        // Try to guess product type from title if not explicitly set
        $productType = null;
        $title = strtolower($item['detail'] ?? $item['title'] ?? '');
        if (str_contains($title, 'workshop')) {
            $productType = 'workshop';
        } elseif (str_contains($title, 'track')) {
            $productType = 'track';
        } elseif (str_contains($title, 'program')) {
            $productType = 'program';
        } else {
            $productType = 'course';
        }

        AnalyticsEnrollment::updateOrCreate(
            [
                'user_uid' => $user->uid,
                'enrollment_id' => (string) $enrollmentId,
            ],
            [
                'product_name' => $item['detail'] ?? $item['title'] ?? 'Unknown',
                'product_type' => $productType,
                'rating' => $item['rating'] ?? null,
                'feedback' => $item['quote'] ?? null,
                'amount' => $item['amount'] ?? null,
                'currency' => $item['currency'] ?? null,
                'purchased_at' => isset($item['occurred_at']) ? Carbon::parse($item['occurred_at']) : null,
            ]
        );
    }

    protected function getFirstField(array $document, array $fields): ?string
    {
        foreach ($fields as $field) {
            if (! empty($document[$field]) && is_scalar($document[$field])) {
                return (string) $document[$field];
            }
        }
        return null;
    }
}
