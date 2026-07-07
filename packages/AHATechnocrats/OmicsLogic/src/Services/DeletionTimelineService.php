<?php

namespace AHATechnocrats\OmicsLogic\Services;

use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\Contact\Models\Person;

class DeletionTimelineService
{
    public function personTimeline(Person $person): array
    {
        $leads = $person->leads()->get(['id', 'title']);
        $leadsComplete = $leads->isEmpty();

        return [
            'entity' => [
                'type' => 'person',
                'id' => $person->id,
                'name' => $person->name,
            ],
            'can_delete' => $leadsComplete,
            'delete_url' => route('admin.contacts.persons.delete', $person->id),
            'steps' => [
                $this->leadStep($leads, $leadsComplete ? 'complete' : 'active'),
                $this->personStep($person, $leadsComplete ? 'active' : 'pending'),
            ],
        ];
    }

    public function organizationTimeline(Organization $organization): array
    {
        $persons = $organization->persons()
            ->with(['leads:id,person_id,title'])
            ->get(['id', 'name']);

        $leadItems = collect();

        foreach ($persons as $person) {
            foreach ($person->leads as $lead) {
                $leadItems->push($this->leadItem($lead, $person->name));
            }
        }

        $leadsComplete = $leadItems->isEmpty();
        $personsComplete = $persons->isEmpty();
        $canDelete = $personsComplete;

        $personItems = $persons->map(fn (Person $person) => [
            'id' => $person->id,
            'label' => $person->name,
            'type' => 'person',
            'meta' => trans('omicslogic::app.delete-timeline.leads-count', [
                'count' => $person->leads->count(),
            ]),
            'blocked' => $person->leads->isNotEmpty(),
            'delete_url' => route('admin.contacts.persons.delete', $person->id),
            'view_url' => route('admin.contacts.persons.view', $person->id),
        ])->values()->all();

        return [
            'entity' => [
                'type' => 'organization',
                'id' => $organization->id,
                'name' => $organization->name,
            ],
            'can_delete' => $canDelete,
            'delete_url' => route('admin.contacts.organizations.delete', $organization->id),
            'steps' => [
                [
                    'key' => 'leads',
                    'label' => trans('omicslogic::app.delete-timeline.step-leads'),
                    'description' => trans('omicslogic::app.delete-timeline.step-leads-desc'),
                    'status' => $leadsComplete ? 'complete' : 'active',
                    'items' => $leadItems->values()->all(),
                ],
                [
                    'key' => 'persons',
                    'label' => trans('omicslogic::app.delete-timeline.step-contacts'),
                    'description' => trans('omicslogic::app.delete-timeline.step-contacts-desc'),
                    'status' => $leadsComplete
                        ? ($personsComplete ? 'complete' : 'active')
                        : 'pending',
                    'items' => $personItems,
                ],
                [
                    'key' => 'organization',
                    'label' => trans('omicslogic::app.delete-timeline.step-organization'),
                    'description' => trans('omicslogic::app.delete-timeline.step-organization-desc'),
                    'status' => $canDelete ? 'active' : 'pending',
                    'items' => [[
                        'id' => $organization->id,
                        'label' => $organization->name,
                        'type' => 'organization',
                        'delete_url' => route('admin.contacts.organizations.delete', $organization->id),
                        'view_url' => route('admin.contacts.organizations.view', $organization->id),
                    ]],
                ],
            ],
        ];
    }

    protected function leadStep($leads, string $status): array
    {
        return [
            'key' => 'leads',
            'label' => trans('omicslogic::app.delete-timeline.step-leads'),
            'description' => trans('omicslogic::app.delete-timeline.step-leads-desc'),
            'status' => $status,
            'items' => $leads->map(fn ($lead) => $this->leadItem($lead))->values()->all(),
        ];
    }

    protected function personStep(Person $person, string $status): array
    {
        return [
            'key' => 'person',
            'label' => trans('omicslogic::app.delete-timeline.step-contact'),
            'description' => trans('omicslogic::app.delete-timeline.step-contact-desc'),
            'status' => $status,
            'items' => [[
                'id' => $person->id,
                'label' => $person->name,
                'type' => 'person',
                'delete_url' => route('admin.contacts.persons.delete', $person->id),
                'view_url' => route('admin.contacts.persons.view', $person->id),
            ]],
        ];
    }

    protected function leadItem($lead, ?string $meta = null): array
    {
        return [
            'id' => $lead->id,
            'label' => $lead->title,
            'type' => 'lead',
            'meta' => $meta,
            'delete_url' => route('admin.leads.delete', $lead->id),
            'view_url' => route('admin.leads.view', $lead->id),
        ];
    }
}
