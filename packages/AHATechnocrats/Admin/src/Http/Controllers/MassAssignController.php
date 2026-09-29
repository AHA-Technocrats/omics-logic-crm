<?php

namespace AHATechnocrats\Admin\Http\Controllers;

use AHATechnocrats\Lead\Repositories\LeadRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;

class MassAssignController extends Controller
{
    public function __construct(
        protected LeadRepository $leadRepository
    ) {}

    /**
     * Display the Mass Assign page.
     */
    public function index(): View
    {
        return view('admin::mass-assign.index');
    }

    /**
     * Get open leads that are unassigned or belong to an admin.
     */
    public function getEntities(Request $request): JsonResponse
    {
        $adminUserIds = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('roles.permission_type', 'all')
            ->pluck('users.id');

        $query = DB::table('leads')
            ->leftJoin('users', 'leads.user_id', '=', 'users.id')
            ->leftJoin('persons', 'leads.person_id', '=', 'persons.id')
            ->select(
                'leads.id',
                'leads.title as name',
                'leads.user_id',
                'users.name as owner_name',
                'persons.name as person_name'
            )
            ->whereNull('leads.closed_at')
            ->where(function ($q) use ($adminUserIds) {
                $q->whereNull('leads.user_id');

                if ($adminUserIds->isNotEmpty()) {
                    $q->orWhereIn('leads.user_id', $adminUserIds);
                }
            });

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('leads.title', 'like', '%'.$search.'%')
                    ->orWhere('persons.name', 'like', '%'.$search.'%');
            });
        }

        $leads = $query->orderBy('leads.title')->get();

        return response()->json([
            'data' => $leads,
        ]);
    }

    /**
     * Get all available users for assignment.
     */
    public function getUsers(): JsonResponse
    {
        $users = DB::table('users')
            ->select('users.id', 'users.name', 'users.email', 'users.image')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.status', 1)
            ->where('roles.permission_type', '!=', 'all')
            ->orderBy('users.name')
            ->get();

        return response()->json([
            'data' => $users,
        ]);
    }

    /**
     * Assign selected leads to selected users.
     */
    public function assign(Request $request): JsonResponse
    {
        $assignments = $request->input('assignments');

        $leadToUserMapping = [];

        if ($assignments) {
            foreach ($assignments as $assignment) {
                if (empty($assignment['user_id']) || empty($assignment['lead_ids'])) {
                    continue;
                }

                foreach ($assignment['lead_ids'] as $leadId) {
                    $leadToUserMapping[(int) $leadId] = (int) $assignment['user_id'];
                }
            }
        } else {
            $request->validate([
                'lead_ids' => 'required|array',
                'user_ids' => 'required|array|min:1',
            ]);

            $leadIds = $request->input('lead_ids');
            $userIds = $request->input('user_ids');

            $totalUsers = count($userIds);
            $userIndex = 0;

            if (count($leadIds) > 0 && $totalUsers > 0) {
                foreach ($leadIds as $leadId) {
                    $leadToUserMapping[(int) $leadId] = (int) $userIds[$userIndex];
                    $userIndex = ($userIndex + 1) % $totalUsers;
                }
            }
        }

        if (empty($leadToUserMapping)) {
            return response()->json(['message' => trans('admin::app.mass_assign.no-selection')], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($leadToUserMapping as $leadId => $assignToUserId) {
                Event::dispatch('lead.update.before', $leadId);

                $this->leadRepository->update([
                    'entity_type' => 'leads',
                    'user_id' => $assignToUserId,
                ], $leadId, ['user_id']);

                Event::dispatch('lead.update.after', $this->leadRepository->find($leadId));
            }

            DB::commit();

            return response()->json([
                'message' => trans('admin::app.mass_assign.success'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
