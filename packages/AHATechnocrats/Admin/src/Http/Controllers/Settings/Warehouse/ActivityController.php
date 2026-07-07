<?php

namespace AHATechnocrats\Admin\Http\Controllers\Settings\Warehouse;

use AHATechnocrats\Activity\Repositories\ActivityRepository;
use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\Admin\Http\Resources\ActivityResource;
use AHATechnocrats\Email\Repositories\EmailRepository;
use Illuminate\Http\Response;

class ActivityController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ActivityRepository $activityRepository,
        protected EmailRepository $emailRepository
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function index($id)
    {
        $activities = $this->activityRepository
            ->leftJoin('warehouse_activities', 'activities.id', '=', 'warehouse_activities.activity_id')
            ->where('warehouse_activities.warehouse_id', $id)
            ->get();

        return ActivityResource::collection($this->concatEmail($activities));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function concatEmail($activities)
    {
        return $activities->sortByDesc('id')->sortByDesc('created_at');
    }
}
