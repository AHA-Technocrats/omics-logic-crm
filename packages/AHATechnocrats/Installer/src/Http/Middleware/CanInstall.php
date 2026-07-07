<?php

namespace AHATechnocrats\Installer\Http\Middleware;

use AHATechnocrats\Installer\Helpers\DatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class CanInstall
{
    /**
     * Handles Requests if application is already installed then redirect to dashboard else to installer.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (Str::contains($request->getPathInfo(), '/install')) {
            if ($this->isAlreadyInstalled()) {
                if (! $request->ajax()) {
                    return redirect()->route('admin.dashboard.index');
                }
            }
        } else {
            if (! $this->isAlreadyInstalled()) {
                return redirect()->route('installer.index');
            }
        }

        return $next($request);
    }

    /**
     * Check if application is already installed.
     */
    public function isAlreadyInstalled(): bool
    {
        if (file_exists(storage_path('installed'))) {
            return true;
        }

        if (app(DatabaseManager::class)->isInstalled()) {
            touch(storage_path('installed'));

            Event::dispatch('ahatechnocrats.installed');

            return true;
        }

        return false;
    }
}
