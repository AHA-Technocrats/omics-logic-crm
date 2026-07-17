<?php

namespace AHATechnocrats\Admin\Http\Controllers\OmicsLogic;

use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\Core\Models\Country;
use AHATechnocrats\OmicsLogic\Models\AnalyticsEnrollment;
use AHATechnocrats\OmicsLogic\Models\AnalyticsUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CustomerAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $availableYears = AnalyticsEnrollment::query()
            ->whereNotNull('purchased_at')
            ->selectRaw('YEAR(purchased_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year)
            ->values();

        if ($availableYears->isEmpty()) {
            $availableYears->push((int) date('Y'));
        }

        $requestedYear = $request->integer('year');
        $year = $availableYears->contains($requestedYear)
            ? $requestedYear
            : $availableYears->first();

        // System-wide calculations (all users, all years)
        $systemTotalUsers = AnalyticsUser::count();
        $systemTotalEnrollments = AnalyticsEnrollment::where('product_name', '!=', 'OmicsLogic Code')->count();
        $systemUsersWithEnrollments = AnalyticsUser::whereHas('enrollments', function ($q) {
            $q->where('product_name', '!=', 'OmicsLogic Code');
        })->count();
        $systemTotalFeedback = AnalyticsEnrollment::whereNotNull('feedback')->count();
        $systemAvgRating = AnalyticsEnrollment::avg('rating');

        // Aggregations
        $totalCustomers = AnalyticsUser::whereHas('enrollments', function ($q) use ($year) {
            $q->whereYear('purchased_at', $year);
        })->count();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $customersByMonth = array_fill(0, 12, 0);

        $monthlyData = AnalyticsEnrollment::selectRaw('MONTH(purchased_at) as month, COUNT(DISTINCT user_uid) as count')
            ->whereYear('purchased_at', $year)
            ->groupBy('month')
            ->get();

        foreach ($monthlyData as $row) {
            if ($row->month >= 1 && $row->month <= 12) {
                $customersByMonth[$row->month - 1] = $row->count;
            }
        }

        $avgSatisfaction = AnalyticsEnrollment::whereNotNull('rating')
            ->whereYear('purchased_at', $year)
            ->avg('rating');

        $countriesReached = AnalyticsUser::whereNotNull('country')
            ->whereHas('enrollments', function ($q) use ($year) {
                $q->whereYear('purchased_at', $year);
            })
            ->distinct('country')
            ->count('country');

        // Organizations
        $orgDataRaw = AnalyticsUser::select('organization', DB::raw('COUNT(*) as count'))
            ->whereNotNull('organization')
            ->whereHas('enrollments', function ($q) use ($year) {
                $q->whereYear('purchased_at', $year);
            })
            ->groupBy('organization')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $orgLabels = $orgDataRaw->pluck('organization')->toArray();
        $orgData = $orgDataRaw->pluck('count')->toArray();

        // Countries
        $countryDataRaw = AnalyticsUser::select('country', DB::raw('COUNT(*) as count'))
            ->whereNotNull('country')
            ->whereHas('enrollments', function ($q) use ($year) {
                $q->whereYear('purchased_at', $year);
            })
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // jsVectorMap requires ISO 3166-1 alpha-2 codes (e.g. IN), not names (India).
        $countryNameToCode = Country::query()
            ->get(['code', 'name'])
            ->mapWithKeys(fn ($country) => [strtolower(trim($country->name)) => strtoupper($country->code)])
            ->all();

        $countryMapData = [];
        foreach (
            AnalyticsUser::select('country', DB::raw('COUNT(*) as count'))
                ->whereNotNull('country')
                ->whereHas('enrollments', function ($q) use ($year) {
                    $q->whereYear('purchased_at', $year);
                })
                ->groupBy('country')
                ->get() as $row
        ) {
            $code = $this->resolveCountryIsoCode((string) $row->country, $countryNameToCode);

            if ($code === null) {
                continue;
            }

            $countryMapData[$code] = ($countryMapData[$code] ?? 0) + (int) $row->count;
        }

        $countryLabels = $countryDataRaw->pluck('country')->toArray();
        $countryData = $countryDataRaw->pluck('count')->toArray();

        $customersByYear = AnalyticsEnrollment::query()
            ->selectRaw('YEAR(purchased_at) as year, COUNT(DISTINCT user_uid) as count')
            ->whereNotNull('purchased_at')
            ->groupByRaw('YEAR(purchased_at)')
            ->orderBy('year')
            ->pluck('count', 'year')
            ->map(fn ($count) => (int) $count)
            ->toArray();

        // Engagement by product
        $productStats = AnalyticsEnrollment::select('product_name', 'product_type', DB::raw('COUNT(*) as enrollments'), DB::raw('AVG(rating) as avg_rating'), DB::raw('COUNT(feedback) as feedbacks_count'))
            ->where('product_name', '!=', 'OmicsLogic Code') // exclude platform events
            ->whereYear('purchased_at', $year)
            ->groupBy('product_name', 'product_type')
            ->orderByDesc('enrollments')
            ->get();

        $data = [
            'months' => $months,
            'customersByMonth' => $customersByMonth,
            'totalCustomers' => $totalCustomers,
            'avgSatisfaction' => round((float) $avgSatisfaction, 1),
            'systemTotalUsers' => $systemTotalUsers,
            'systemTotalEnrollments' => $systemTotalEnrollments,
            'systemUsersWithEnrollments' => $systemUsersWithEnrollments,
            'systemTotalFeedback' => $systemTotalFeedback,
            'systemAvgRating' => round((float) $systemAvgRating, 1),
            'countriesReached' => $countriesReached,
            'orgLabels' => $orgLabels,
            'orgData' => $orgData,
            'countryLabels' => $countryLabels,
            'countryData' => $countryData,
            'countryMapData' => $countryMapData,
            'customersByYear' => $customersByYear,
            'productStats' => $productStats,
            'lastSynced' => Cache::get('omics_analytics_last_sync'),
            'selectedYear' => $year,
            'availableYears' => $availableYears,
        ];

        return view('admin::omics.analytics.customer', compact('data'));
    }

    public function sync()
    {
        try {
            Artisan::call('omics:sync-customer-analytics');

            return redirect()->back()->with('success', 'Sync completed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Sync failed: '.$e->getMessage());
        }
    }

    /**
     * Convert a stored country name/code to a jsVectorMap region code.
     */
    protected function resolveCountryIsoCode(string $value, array $countryNameToCode): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $aliases = [
            'usa' => 'US',
            'united states' => 'US',
            'united states of america' => 'US',
            'uk' => 'GB',
            'united kingdom' => 'GB',
            'great britain' => 'GB',
            'russia' => 'RU',
            'south korea' => 'KR',
            'korea' => 'KR',
            'vietnam' => 'VN',
            'uae' => 'AE',
            'united arab emirates' => 'AE',
        ];

        $normalized = strtolower($value);

        if (isset($aliases[$normalized])) {
            return $aliases[$normalized];
        }

        if (isset($countryNameToCode[$normalized])) {
            return $countryNameToCode[$normalized];
        }

        if (strlen($value) === 2) {
            return strtoupper($value);
        }

        return null;
    }
}
