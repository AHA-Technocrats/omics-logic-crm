<?php

namespace AHATechnocrats\WebForm\Http\Controllers;

use AHATechnocrats\Attribute\Repositories\AttributeRepository;
use AHATechnocrats\Contact\Models\Organization;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use AHATechnocrats\Lead\Repositories\LeadRepository;
use AHATechnocrats\Lead\Repositories\PipelineRepository;
use AHATechnocrats\Lead\Repositories\SourceRepository;
use AHATechnocrats\Lead\Repositories\TypeRepository;
use AHATechnocrats\OmicsLogic\Services\FormSubmissionGuard;
use AHATechnocrats\OmicsLogic\Services\OrganizationAssigneeResolver;
use AHATechnocrats\OmicsLogic\Services\OrganizationResolver;
use AHATechnocrats\OmicsLogic\Services\OrganizationSearchService;
use AHATechnocrats\OmicsLogic\Services\WebFormSubmissionMapper;
use AHATechnocrats\WebForm\Http\Requests\WebForm;
use AHATechnocrats\WebForm\Models\WebFormSubmission;
use AHATechnocrats\WebForm\Repositories\WebFormRepository;
use AHATechnocrats\WebForm\Services\WebFormSubmitterMailer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;

class WebFormController extends Controller
{
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected WebFormRepository $webFormRepository,
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository,
        protected PipelineRepository $pipelineRepository,
        protected SourceRepository $sourceRepository,
        protected TypeRepository $typeRepository,
        protected FormSubmissionGuard $formSubmissionGuard,
        protected OrganizationResolver $organizationResolver,
        protected OrganizationSearchService $organizationSearchService,
        protected OrganizationAssigneeResolver $organizationAssigneeResolver,
        protected WebFormSubmissionMapper $submissionMapper,
        protected WebFormSubmitterMailer $submitterMailer,
    ) {}

    public function formJS(string $formId): Response
    {
        $webForm = $this->webFormRepository->findOneByField('form_id', $formId);

        $formToken = $this->formSubmissionGuard->issueFormToken();

        return response()->view('web_form::settings.web-forms.embed', compact('webForm', 'formToken'))
            ->header('Content-Type', 'text/javascript');
    }

    public function formStore(int $id): JsonResponse
    {
        $webForm = $this->webFormRepository->findOrFail($id);

        if (! ($webForm->is_active ?? true)) {
            abort(404);
        }

        $mapped = $this->submissionMapper->map(request()->all(), $webForm);

        request()->merge([
            'persons' => $mapped['person'],
            'leads' => array_merge(request('leads', []), $mapped['lead']),
        ]);

        $guardResult = $this->formSubmissionGuard->validate(request(), $webForm);

        $this->resolveOrganizationFromRequest($webForm, $mapped['organization']['country_code'] ?? null);

        $email = $mapped['person']['emails'][0]['value'] ?? null;

        $person = $email
            ? $this->personRepository->getModel()->where('normalized_email', strtolower(trim($email)))->first()
            : null;

        if ($person) {
            request()->request->add(['persons' => array_merge(request('persons'), ['id' => $person->id])]);
        }

        app(WebForm::class);

        $createLead = (bool) ($webForm->create_lead ?? true);

        if ($createLead) {
            request()->request->add(['entity_type' => 'leads']);

            Event::dispatch('lead.create.before');

            $data = request('leads', []);
            $data['entity_type'] = 'leads';
            $data['person'] = request('persons');
            $data['status'] = 1;

            $pipeline = $this->pipelineRepository->getDefaultPipeline();
            $stage = $pipeline->stages()->first();

            $data['lead_pipeline_id'] = $pipeline->id;
            $data['lead_pipeline_stage_id'] = $stage->id;
            $data['title'] = $data['title'] ?? ($mapped['lead']['title'] ?? 'Web Form Lead');
            $data['lead_value'] = $data['lead_value'] ?? 0;
            $data['description'] = $data['description'] ?? ($mapped['lead']['description'] ?? null);

            if (empty($data['lead_source_id'])) {
                $source = $this->sourceRepository->findOneByField('name', 'Web Form')
                    ?: $this->sourceRepository->first();
                $data['lead_source_id'] = $source?->id;
            }

            $assigneeId = data_get(request('persons'), 'user_id');

            if ($assigneeId) {
                $data['user_id'] = $assigneeId;
            }

            $data['lead_type_id'] = $data['lead_type_id'] ?? $this->typeRepository->first()?->id;

            if ($person) {
                $this->personRepository->update(array_merge(request('persons'), [
                    'entity_type' => 'persons',
                ]), $person->id);
                $data['person']['id'] = $person->id;
            }

            $lead = $this->leadRepository->create($data);

            Event::dispatch('lead.create.after', $lead);

            $person = $lead->person;
        } else {
            if ($person) {
                $person = $this->personRepository->update(array_merge(request('persons'), [
                    'entity_type' => 'persons',
                ]), $person->id);
            } else {
                Event::dispatch('contacts.person.create.before');

                $data = request('persons');
                request()->request->add(['entity_type' => 'persons']);
                $data['entity_type'] = 'persons';
                $data['spam_score'] = $guardResult['spam_score'];
                $data['spam_status'] = $guardResult['spam_score'] >= 30 ? 'suspect' : 'clean';

                $person = $this->personRepository->create($data);

                Event::dispatch('contacts.person.create.after', $person);
            }
        }

        WebFormSubmission::query()->create([
            'web_form_id' => $webForm->id,
            'person_id' => $person?->id,
            'payload' => request()->only(['persons', 'leads']),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'spam_score' => $guardResult['spam_score'],
            'status' => $guardResult['spam_score'] >= 30 ? 'suspect' : 'accepted',
            'created_at' => $mapped['submitted_at'] ?? now(),
            'updated_at' => $mapped['submitted_at'] ?? now(),
        ]);

        if ($guardResult['spam_score'] < 30) {
            $this->submitterMailer->sendIfConfigured($webForm, request()->only(['persons', 'leads']));
        }

        if ($webForm->submit_success_action == 'message') {
            return response()->json([
                'redirect' => route('admin.settings.web_forms.thank_you', $webForm->form_id),
            ], 200);
        }

        return response()->json([
            'redirect' => $webForm->submit_success_content,
        ], 200);
    }

    public function checkEmail(int $id): JsonResponse
    {
        $webForm = $this->webFormRepository->findOrFail($id);

        if (! ($webForm->is_active ?? true)) {
            abort(404);
        }

        $email = strtolower(trim((string) request()->input('email', '')));

        if ($email === '') {
            return response()->json(['already_submitted' => false]);
        }

        $alreadySubmitted = WebFormSubmission::query()
            ->where('web_form_id', $webForm->id)
            ->whereHas('person', function ($query) use ($email) {
                $query->where('normalized_email', $email);
            })
            ->exists();

        return response()->json([
            'already_submitted' => $alreadySubmitted,
        ]);
    }

    public function thankYou(string $id): View
    {
        $webForm = $this->webFormRepository->findOneByField('form_id', $id);

        if (is_null($webForm) || ! ($webForm->is_active ?? true)) {
            abort(404);
        }

        return view('web_form::settings.web-forms.thank-you', compact('webForm'));
    }

    public function preview(string $id): View
    {
        $webForm = $this->webFormRepository->findOneByField('form_id', $id);

        if (is_null($webForm) || ! ($webForm->is_active ?? true)) {
            abort(404);
        }

        $webForm->load(['attributes.attribute']);

        $formToken = $this->formSubmissionGuard->issueFormToken();

        return view('web_form::settings.web-forms.preview', compact('webForm', 'formToken'));
    }

    public function view(int $id): View
    {
        $webForm = $this->webFormRepository->findOneByField('id', $id);

        if (is_null($webForm)) {
            abort(404);
        }

        request()->merge(['id' => $webForm->form_id]);

        $webForm->load(['attributes.attribute']);

        $formToken = $this->formSubmissionGuard->issueFormToken();

        return view('web_form::settings.web-forms.preview', compact('webForm', 'formToken'));
    }

    public function searchOrganizations(): JsonResponse
    {
        $query = (string) request()->input('q', '');

        return response()->json([
            'data' => $this->organizationSearchService->search($query),
        ]);
    }

    protected function resolveOrganizationFromRequest($webForm, ?string $countryCode = null): void
    {
        $persons = request('persons', []);
        $organizationId = $persons['organization_id'] ?? null;
        $orgName = request('organization_name')
            ?: $persons['organization_name'] ?? null
            ?: request('organizations.name');

        if (! $organizationId && ! $orgName && $webForm->organization_field !== 'required') {
            return;
        }

        $countryCode = $countryCode
            ?: $persons['country_code'] ?? null
            ?: request('country');

        $organization = null;

        if ($organizationId) {
            $organization = Organization::query()->find($organizationId);

            if ($organization) {
                $orgName = $organization->name;
            }
        }

        if (! $organization) {
            $organization = $this->organizationResolver->resolve(
                $orgName,
                (bool) $webForm->allow_org_create,
                $countryCode,
            );
        } elseif ($countryCode) {
            $organization = $this->organizationResolver->resolve(
                $organization->name,
                false,
                $countryCode,
            ) ?? $organization;
        }

        if ($organization) {
            $persons['organization_id'] = $organization->id;
            $persons['organization_name'] = $organization->name;
            $persons['country_code'] = $organization->country_code ?? $countryCode;

            $assigneeId = $this->organizationAssigneeResolver->resolve($organization);

            if ($assigneeId) {
                $persons['user_id'] = $assigneeId;
            }

            request()->request->add(['persons' => $persons]);
        }
    }
}
