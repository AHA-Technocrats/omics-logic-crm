<?php

namespace AHATechnocrats\Admin\Http\Controllers;

use AHATechnocrats\Contact\Repositories\OrganizationRepository;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use AHATechnocrats\Lead\Repositories\LeadRepository;
use AHATechnocrats\User\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;

class MassAssignController extends Controller
{
    public function __construct(
        protected OrganizationRepository $organizationRepository,
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository,
        protected UserRepository $userRepository
    ) {
    }

    /**
     * Display the Mass Assign page.
     */
    public function index(): View
    {
        return view('admin::mass-assign.index');
    }

    /**
     * Get organizations that are unassigned or belong to an admin.
     */
    public function getEntities(Request $request): JsonResponse
    {
        // For simplicity, we assume "Admin" means users with role ID 1, 
        // or just unassigned. We can also let the UI pass filters.
        
        $query = DB::table('organizations')
            ->leftJoin('users as account_owners', 'organizations.account_owner_id', '=', 'account_owners.id')
            ->select(
                'organizations.id',
                'organizations.name',
                'organizations.type',
                'organizations.account_owner_id',
                'account_owners.name as account_owner_name'
            )
            ->whereNull('organizations.account_owner_id')
            ->orWhereIn('organizations.account_owner_id', function($q) {
                $q->select('users.id')
                  ->from('users')
                  ->join('roles', 'users.role_id', '=', 'roles.id')
                  ->where('roles.permission_type', 'all');
            });
            
        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $query->where('organizations.name', 'like', '%' . $request->search . '%');
        }

        $organizations = $query->orderBy('organizations.name')->get();

        return response()->json([
            'data' => $organizations,
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
     * Assign selected organizations to selected users.
     */
    public function assign(Request $request): JsonResponse
    {
        $assignments = $request->input('assignments');
        
        $orgToUserMapping = [];
        
        if ($assignments) {
            // Process explicit manual assignments
            foreach ($assignments as $assignment) {
                if (empty($assignment['user_id']) || empty($assignment['org_ids'])) continue;
                foreach ($assignment['org_ids'] as $orgId) {
                    $orgToUserMapping[$orgId] = $assignment['user_id'];
                }
            }
        } else {
            // Process round-robin distribution
            $request->validate([
                'organization_ids' => 'required|array',
                'user_ids' => 'required|array|min:1',
            ]);

            $organizationIds = $request->input('organization_ids');
            $userIds = $request->input('user_ids');
            
            $totalUsers = count($userIds);
            $userIndex = 0;

            if (count($organizationIds) > 0 && $totalUsers > 0) {
                foreach ($organizationIds as $orgId) {
                    $orgToUserMapping[$orgId] = $userIds[$userIndex];
                    $userIndex = ($userIndex + 1) % $totalUsers;
                }
            }
        }

        if (empty($orgToUserMapping)) {
            return response()->json(['message' => 'No organizations or users selected.'], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($orgToUserMapping as $orgId => $assignToUserId) {
                Event::dispatch('contacts.organization.update.before', $orgId);

                $this->organizationRepository->update([
                    'account_owner_id' => $assignToUserId,
                ], $orgId);

                Event::dispatch('contacts.organization.update.after', $this->organizationRepository->find($orgId));

                // Auto-assign related persons
                $persons = $this->personRepository->findWhere(['organization_id' => $orgId]);
                foreach ($persons as $person) {
                    Event::dispatch('contacts.person.update.before', $person->id);
                    
                    $personData = [
                        'user_id' => $assignToUserId,
                        'organization_id' => $person->organization_id,
                    ];
                    
                    if ($person->emails) {
                        // Pass existing emails to prevent unique_id corruption
                        $personData['emails'] = is_string($person->emails) ? json_decode($person->emails, true) : (is_object($person->emails) ? $person->emails->toArray() : $person->emails);
                    }
                    
                    if ($person->contact_numbers) {
                        $personData['contact_numbers'] = is_string($person->contact_numbers) ? json_decode($person->contact_numbers, true) : (is_object($person->contact_numbers) ? $person->contact_numbers->toArray() : $person->contact_numbers);
                    }

                    $this->personRepository->update($personData, $person->id);
                    Event::dispatch('contacts.person.update.after', $person);
                }

                // Auto-assign related open leads
                $leads = $this->leadRepository->findWhereIn('person_id', $persons->pluck('id')->toArray());
                foreach ($leads as $lead) {
                    // Only assign if it's not won/lost (assuming status 1 means open, or check if closed_at is null)
                    if (is_null($lead->closed_at)) {
                        Event::dispatch('lead.update.before', $lead->id);
                        $this->leadRepository->update([
                            'entity_type' => 'leads',
                            'user_id' => $assignToUserId,
                        ], $lead->id);
                        Event::dispatch('lead.update.after', $this->leadRepository->find($lead->id));
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Mass assignment completed successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
