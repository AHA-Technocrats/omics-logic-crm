<?php

namespace AHATechnocrats\Admin\Http\Controllers\Settings;

use AHATechnocrats\Admin\Http\Controllers\Controller;
use AHATechnocrats\Attribute\Repositories\AttributeRepository;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use AHATechnocrats\EmailTemplate\Models\EmailTemplate;
use AHATechnocrats\Lead\Repositories\LeadRepository;
use AHATechnocrats\Lead\Repositories\PipelineRepository;
use AHATechnocrats\Lead\Repositories\SourceRepository;
use AHATechnocrats\Lead\Repositories\TypeRepository;
use AHATechnocrats\WebForm\DataGrids\WebFormDataGrid;
use AHATechnocrats\WebForm\Helpers\WebFormCampaigns;
use AHATechnocrats\WebForm\Helpers\WebFormFieldOrder;
use AHATechnocrats\WebForm\Repositories\WebFormRepository;
use AHATechnocrats\WebForm\Services\WebFormShortUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;

class WebFormController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected WebFormRepository $webFormRepository,
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository,
        protected PipelineRepository $pipelineRepository,
        protected SourceRepository $sourceRepository,
        protected TypeRepository $typeRepository,
        protected WebFormShortUrl $webFormShortUrl,
    ) {}

    /**
     * Display a listing of the email template.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(WebFormDataGrid::class)->process();
        }

        return view('admin::settings.web-forms.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $tempAttributes = $this->attributeRepository->findWhereIn('entity_type', ['persons', 'leads', 'organizations']);

        $attributes = [];

        foreach ($tempAttributes as $attribute) {
            if (
                $attribute->entity_type == 'persons'
                && in_array($attribute->code, ['name', 'emails', 'contact_numbers'])
            ) {
                $attributes['default'][] = $attribute;
            } else {
                $attributes['other'][] = $attribute;
            }
        }

        return view('admin::settings.web-forms.create', [
            'attributes' => $attributes,
            'defaultFields' => WebFormFieldOrder::defaultEditorFields(),
            'emailTemplates' => EmailTemplate::query()->orderBy('name')->get(['id', 'name']),
            'activeCampaigns' => WebFormCampaigns::activeAsOptions(),
        ]);
    }

    /**
     * Store a newly created email templates in storage.
     */
    public function store(): RedirectResponse
    {
        $this->validate(request(), [
            'title' => 'required',
            'submit_button_label' => 'required',
            'submit_success_action' => 'required',
            'submit_success_content' => 'required_if:submit_success_action,redirect',
            'thank_you_content' => 'required_if:submit_success_action,message',
        ]);

        Event::dispatch('settings.web_forms.create.before');

        $data = request()->all();

        if (($data['submit_success_action'] ?? '') === 'message' && empty($data['submit_success_content'])) {
            $data['submit_success_content'] = 'Your response has been recorded.';
        }

        $webForm = $this->webFormRepository->create($data);

        Event::dispatch('settings.web_forms.create.after', $webForm);

        session()->flash('success', trans('admin::app.settings.webforms.index.create-success'));

        return redirect()->route('admin.web_forms.index');
    }

    /**
     * Show the form for editing the specified email template.
     */
    public function edit(int $id): View
    {
        $webForm = $this->webFormRepository->findOrFail($id);

        $attributes = $this->attributeRepository->findWhere([
            ['entity_type', 'IN', ['persons', 'leads', 'organizations']],
            ['id', 'NOTIN', $webForm->attributes()->pluck('attribute_id')->toArray()],
        ]);

        $shortPublicUrl = $this->webFormShortUrl->publicUrl($webForm->fresh());

        return view('admin::settings.web-forms.edit', [
            'webForm' => $webForm->fresh(),
            'shortPublicUrl' => $shortPublicUrl,
            'attributes' => $attributes,
            'emailTemplates' => EmailTemplate::query()->orderBy('name')->get(['id', 'name']),
            'activeCampaigns' => WebFormCampaigns::activeAsOptions(),
        ]);
    }

    /**
     * Update the specified email template in storage.
     */
    public function update(int $id): RedirectResponse
    {
        $this->validate(request(), [
            'title' => 'required',
            'submit_button_label' => 'required',
            'submit_success_action' => 'required',
            'submit_success_content' => 'required_if:submit_success_action,redirect',
            'thank_you_content' => 'required_if:submit_success_action,message',
        ]);

        Event::dispatch('settings.web_forms.update.before', $id);

        $data = request()->all();

        if (($data['submit_success_action'] ?? '') === 'message' && empty($data['submit_success_content'])) {
            $data['submit_success_content'] = 'Your response has been recorded.';
        }

        $webForm = $this->webFormRepository->update($data, $id);

        Event::dispatch('settings.web_forms.update.after', $webForm);

        session()->flash('success', trans('admin::app.settings.webforms.index.update-success'));

        return redirect()->route('admin.web_forms.index');
    }

    /**
     * Persist customization drawer settings (colors, campaign interest, campaign list).
     */
    public function updateCustomization(int $id): JsonResponse
    {
        $this->validate(request(), [
            'program_field' => 'required|in:none,required',
            'campaign_scope' => 'nullable|in:all,selected',
            'background_color' => 'nullable|string|max:20',
            'form_background_color' => 'nullable|string|max:20',
            'form_title_color' => 'nullable|string|max:20',
            'form_submit_button_color' => 'nullable|string|max:20',
            'attribute_label_color' => 'nullable|string|max:20',
        ]);

        Event::dispatch('settings.web_forms.update.before', $id);

        $webForm = $this->webFormRepository->updateCustomization($id, request()->all());

        Event::dispatch('settings.web_forms.update.after', $webForm);

        return response()->json([
            'message' => trans('admin::app.settings.webforms.form.customization-saved'),
            'program_field' => $webForm->program_field,
            'campaign_scope' => $webForm->campaign_scope,
            'field_order' => $webForm->field_order,
        ]);
    }

    /**
     * Remove the specified email template from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $webForm = $this->webFormRepository->findOrFail($id);

        try {
            Event::dispatch('settings.web_forms.delete.before', $id);

            $webForm->delete($id);

            Event::dispatch('settings.web_forms.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.settings.webforms.index.delete-success'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.webforms.index.delete-failed'),
            ], 400);
        }
    }
}
