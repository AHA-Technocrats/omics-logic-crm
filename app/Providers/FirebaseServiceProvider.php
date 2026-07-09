<?php

namespace App\Providers;

use App\Firebase\FirebaseManager;
use App\Firebase\Repositories\AchievementRepository;
use App\Firebase\Repositories\FormRepository;
use App\Firebase\Repositories\PurchaseRepository;
use App\Firebase\Repositories\UserRepository;
use App\Firebase\Services\AchievementTimelineMapper;
use App\Firebase\Services\FirestoreFormMapper;
use App\Firebase\Services\FormService;
use App\Firebase\Services\FormSyncService;
use App\Firebase\Services\PurchaseHistoryMapper;
use App\Firebase\Services\UserService;
use App\Firebase\Support\GoogleHttpClientFactory;
use Illuminate\Support\ServiceProvider;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(base_path('config/firebase.php'), 'firebase');

        GoogleHttpClientFactory::configure();

        $this->app->singleton(FirebaseManager::class);

        $this->app->singleton(UserRepository::class);
        $this->app->singleton(AchievementRepository::class);
        $this->app->singleton(PurchaseRepository::class);
        $this->app->singleton(FormRepository::class);

        $this->app->singleton(AchievementTimelineMapper::class);
        $this->app->singleton(PurchaseHistoryMapper::class);
        $this->app->singleton(FirestoreFormMapper::class);
        $this->app->singleton(UserService::class);
        $this->app->singleton(FormService::class);
        $this->app->singleton(FormSyncService::class);
    }

    public function boot(): void
    {
        //
    }
}
