<?php

namespace AHATechnocrats\DataTransfer\Helpers\Importers\Leads;

use AHATechnocrats\Attribute\Repositories\AttributeRepository;
use AHATechnocrats\Attribute\Repositories\AttributeValueRepository;
use AHATechnocrats\DataTransfer\Contracts\ImportBatch as ImportBatchContract;
use AHATechnocrats\DataTransfer\Helpers\Import;
use AHATechnocrats\DataTransfer\Helpers\Importers\AbstractImporter;
use AHATechnocrats\DataTransfer\Repositories\ImportBatchRepository;
use AHATechnocrats\Lead\Repositories\LeadRepository;
use AHATechnocrats\OmicsLogic\Services\Import\LeadImportProcessor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;

class Importer extends AbstractImporter
{
    /**
     * Error code for non existing title on delete.
     */
    const ERROR_ID_NOT_FOUND_FOR_DELETE = 'id_not_found_to_delete';

    /**
     * Permanent entity columns.
     *
     * A lead row carries everything needed to create/link its contact and
     * organization by NAME — no numeric IDs are required in the file.
     */
    protected array $validColumnNames = [
        'title',
        'description',
        'lead_value',
        'status',
        'person_name',
        'email',
        'phone',
        'job_title',
        'organization_name',
        'country',
        'education_level',
        'inquiry_details',
        'timestamp',
        'source',
        'lead_type',
        'pipeline',
        'stage',
        'owner',
        'product',
        'expected_close_date',
    ];

    /**
     * Accept "raw" web-form / Google-Form headers as-is by mapping them onto the
     * canonical column names above. Keys are normalized header slugs (lowercased,
     * non-alphanumerics stripped). "Full Name" feeds both the person name and the
     * lead title. Canonical snake_case headers keep working (identity mapping).
     *
     * @var array<string, string|string[]>
     */
    protected array $columnAliases = [
        'timestamp' => 'timestamp',
        'emailaddress' => 'email',
        'email' => 'email',
        'fullname' => ['person_name', 'title'],
        'name' => ['person_name', 'title'],
        'phonenumber' => 'phone',
        'phone' => 'phone',
        'mobile' => 'phone',
        'country' => 'country',
        'companyorganizationuniversity' => 'organization_name',
        'company' => 'organization_name',
        'organization' => 'organization_name',
        'organizationname' => 'organization_name',
        'levelofeducation' => 'education_level',
        'education' => 'education_level',
        'educationlevel' => 'education_level',
        'anyotherdetailsqueriesyouwishtomention' => 'inquiry_details',
        'otherdetails' => 'inquiry_details',
        'queries' => 'inquiry_details',
        'product' => 'product',
        'jobtitle' => 'job_title',
    ];

    /**
     * Preserve the original submission row so it can be stored verbatim.
     */
    protected bool $keepRawRow = true;

    /**
     * Error message templates.
     */
    protected array $messages = [
        self::ERROR_ID_NOT_FOUND_FOR_DELETE => 'data_transfer::app.importers.leads.validation.errors.id-not-found',
    ];

    /**
     * Columns that must be present in the file.
     *
     * @var string[]
     */
    protected $permanentAttributes = ['title'];

    /**
     * Permanent entity column.
     */
    protected string $masterAttributeCode = 'title';

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(
        protected ImportBatchRepository $importBatchRepository,
        protected LeadRepository $leadRepository,
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository,
        protected Storage $leadsStorage,
        protected LeadImportProcessor $leadImportProcessor,
    ) {
        parent::__construct(
            $importBatchRepository,
            $attributeRepository,
            $attributeValueRepository,
        );
    }

    /**
     * Initialize leads error templates.
     */
    protected function initErrorMessages(): void
    {
        foreach ($this->messages as $errorCode => $message) {
            $this->errorHelper->addErrorMessage($errorCode, trans($message));
        }

        parent::initErrorMessages();
    }

    /**
     * Validate data.
     */
    public function validateData(): void
    {
        $this->leadsStorage->init();

        parent::validateData();
    }

    /**
     * Validates row.
     */
    public function validateRow(array $rowData, int $rowNumber): bool
    {
        /**
         * If row is already validated than no need for further validation.
         */
        if (isset($this->validatedRows[$rowNumber])) {
            return ! $this->errorHelper->isRowInvalid($rowNumber);
        }

        $this->validatedRows[$rowNumber] = true;

        /**
         * On delete, we only need an existing lead title.
         */
        if ($this->import->action == Import::ACTION_DELETE) {
            if (! $this->isTitleExist($rowData['title'] ?? '')) {
                $this->skipRow($rowNumber, self::ERROR_ID_NOT_FOUND_FOR_DELETE, 'title');

                return false;
            }

            return true;
        }

        /**
         * Name-based validation — lookups (source/type/pipeline/stage/owner/
         * organization/contact) are resolved or created at import time, so the
         * file only needs a title and well-formed optional values.
         */
        $validator = Validator::make($rowData, [
            'title' => 'required',
            'email' => 'nullable|email',
            'lead_value' => 'nullable|numeric',
            'status' => 'nullable|in:0,1',
            'expected_close_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            $failedAttributes = $validator->failed();

            foreach ($validator->errors()->getMessages() as $attributeCode => $message) {
                $errorCode = array_key_first($failedAttributes[$attributeCode] ?? []);

                $this->skipRow($rowNumber, $errorCode, $attributeCode, current($message));
            }
        }

        return ! $this->errorHelper->isRowInvalid($rowNumber);
    }

    /**
     * Start the import process.
     */
    public function importBatch(ImportBatchContract $batch): bool
    {
        Event::dispatch('data_transfer.imports.batch.import.before', $batch);

        if ($batch->import->action == Import::ACTION_DELETE) {
            $this->deleteLeads($batch);
        } else {
            $this->saveLeads($batch);
        }

        /**
         * Update import batch summary.
         */
        $batch = $this->importBatchRepository->update([
            'state' => Import::STATE_PROCESSED,

            'summary' => [
                'created' => $this->getCreatedItemsCount(),
                'updated' => $this->getUpdatedItemsCount(),
                'deleted' => $this->getDeletedItemsCount(),
            ],
        ], $batch->id);

        Event::dispatch('data_transfer.imports.batch.import.after', $batch);

        return true;
    }

    /**
     * Delete leads from current batch.
     */
    protected function deleteLeads(ImportBatchContract $batch): bool
    {
        /**
         * Load leads storage with batch titles.
         */
        $this->leadsStorage->load(Arr::pluck($batch->data, 'title'));

        $idsToDelete = [];

        foreach ($batch->data as $rowData) {
            if (! $this->isTitleExist($rowData['title'])) {
                continue;
            }

            $idsToDelete[] = $this->leadsStorage->get($rowData['title'])['id'];
        }

        $idsToDelete = array_unique($idsToDelete);

        $this->deletedItemsCount = count($idsToDelete);

        $this->leadRepository->deleteWhere([['id', 'IN', $idsToDelete]]);

        return true;
    }

    /**
     * Save leads from current batch.
     *
     * Each row is processed individually so the contact and organization can be
     * matched (or created and queued for merge review) from the lead values.
     */
    protected function saveLeads(ImportBatchContract $batch): bool
    {
        $defaultSourceId = $this->import->source_id ?? null;

        foreach ($batch->data as $rowData) {
            try {
                $lead = $this->leadImportProcessor->process($rowData, $defaultSourceId);

                if ($lead) {
                    $this->createdItemsCount++;
                }
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return true;
    }

    /**
     * Check if title exists.
     */
    public function isTitleExist(string $title): bool
    {
        return $this->leadsStorage->has($title);
    }
}
