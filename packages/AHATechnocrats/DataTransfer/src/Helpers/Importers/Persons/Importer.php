<?php

namespace AHATechnocrats\DataTransfer\Helpers\Importers\Persons;

use AHATechnocrats\Attribute\Repositories\AttributeRepository;
use AHATechnocrats\Attribute\Repositories\AttributeValueRepository;
use AHATechnocrats\Contact\Repositories\PersonRepository;
use AHATechnocrats\DataTransfer\Contracts\ImportBatch as ImportBatchContract;
use AHATechnocrats\DataTransfer\Helpers\Import;
use AHATechnocrats\DataTransfer\Helpers\Importers\AbstractImporter;
use AHATechnocrats\DataTransfer\Repositories\ImportBatchRepository;
use AHATechnocrats\OmicsLogic\Services\Import\PersonImportProcessor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;

class Importer extends AbstractImporter
{
    /**
     * Error code for non existing email.
     */
    const ERROR_EMAIL_NOT_FOUND_FOR_DELETE = 'email_not_found_to_delete';

    /**
     * Error code for duplicated email.
     */
    const ERROR_DUPLICATE_EMAIL = 'duplicated_email';

    /**
     * Name-based columns — contact and organization are resolved/created by
     * name at import time (same pattern as leads). No CRM numeric IDs required.
     */
    protected array $validColumnNames = [
        'person_name',
        'name',
        'email',
        'phone',
        'job_title',
        'organization_name',
        'country',
        'education_level',
        'inquiry_details',
        'timestamp',
        'source',
        'owner',
    ];

    /**
     * Accept raw web-form / Google-Form headers and map them onto canonical
     * columns. organization_id maps to organization_name when clients put
     * university/company *names* in an ID-labeled column.
     *
     * @var array<string, string|string[]>
     */
    protected array $columnAliases = [
        'timestamp' => 'timestamp',
        'emailaddress' => 'email',
        'email' => 'email',
        'fullname' => 'person_name',
        'name' => 'person_name',
        'personname' => 'person_name',
        'phonenumber' => 'phone',
        'phone' => 'phone',
        'mobile' => 'phone',
        'country' => 'country',
        'companyorganizationuniversity' => 'organization_name',
        'company' => 'organization_name',
        'organization' => 'organization_name',
        'organizationname' => 'organization_name',
        'organizationid' => 'organization_name',
        'levelofeducation' => 'education_level',
        'education' => 'education_level',
        'educationlevel' => 'education_level',
        'anyotherdetailsqueriesyouwishtomention' => 'inquiry_details',
        'otherdetails' => 'inquiry_details',
        'queries' => 'inquiry_details',
        'jobtitle' => 'job_title',
        'owner' => 'owner',
        'source' => 'source',
    ];

    /**
     * Preserve the original submission row.
     */
    protected bool $keepRawRow = true;

    /**
     * Error message templates.
     */
    protected array $messages = [
        self::ERROR_EMAIL_NOT_FOUND_FOR_DELETE => 'data_transfer::app.importers.persons.validation.errors.email-not-found',
        self::ERROR_DUPLICATE_EMAIL => 'data_transfer::app.importers.persons.validation.errors.duplicate-email',
    ];

    /**
     * Columns that should be present for delete lookups.
     *
     * @var string[]
     */
    protected $permanentAttributes = ['email'];

    /**
     * Permanent entity column.
     */
    protected string $masterAttributeCode = 'email';

    /**
     * Emails seen in the current file (duplicate check).
     */
    protected array $emails = [];

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(
        protected ImportBatchRepository $importBatchRepository,
        protected PersonRepository $personRepository,
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository,
        protected Storage $personStorage,
        protected PersonImportProcessor $personImportProcessor,
    ) {
        parent::__construct(
            $importBatchRepository,
            $attributeRepository,
            $attributeValueRepository,
        );
    }

    /**
     * Initialize person error templates.
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
        $this->personStorage->init();

        parent::validateData();
    }

    /**
     * Validates row.
     */
    public function validateRow(array $rowData, int $rowNumber): bool
    {
        if (isset($this->validatedRows[$rowNumber])) {
            return ! $this->errorHelper->isRowInvalid($rowNumber);
        }

        $this->validatedRows[$rowNumber] = true;

        if ($this->import->action == Import::ACTION_DELETE) {
            $email = $rowData['email'] ?? '';

            if (! $this->isEmailExist($email)) {
                $this->skipRow($rowNumber, self::ERROR_EMAIL_NOT_FOUND_FOR_DELETE, 'email');

                return false;
            }

            return true;
        }

        $validator = Validator::make($rowData, [
            'person_name' => 'required_without_all:name,email,phone',
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $failedAttributes = $validator->failed();

            foreach ($validator->errors()->getMessages() as $attributeCode => $message) {
                $errorCode = array_key_first($failedAttributes[$attributeCode] ?? []);

                $this->skipRow($rowNumber, $errorCode, $attributeCode, current($message));
            }
        }

        $email = $rowData['email'] ?? null;

        if (! empty($email)) {
            if (! in_array($email, $this->emails, true)) {
                $this->emails[] = $email;
            } else {
                $message = sprintf(
                    trans($this->messages[self::ERROR_DUPLICATE_EMAIL]),
                    $email
                );

                $this->skipRow($rowNumber, self::ERROR_DUPLICATE_EMAIL, 'email', $message);
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
            $this->deletePersons($batch);
        } else {
            $this->savePersons($batch);
        }

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
     * Delete persons from current batch.
     */
    protected function deletePersons(ImportBatchContract $batch): bool
    {
        $this->personStorage->load(Arr::pluck($batch->data, 'email'));

        $idsToDelete = [];

        foreach ($batch->data as $rowData) {
            $email = $rowData['email'] ?? '';

            if (! $this->isEmailExist($email)) {
                continue;
            }

            $idsToDelete[] = $this->personStorage->get($email);
        }

        $idsToDelete = array_unique(array_filter($idsToDelete));

        $this->deletedItemsCount = count($idsToDelete);

        if ($idsToDelete) {
            $this->personRepository->deleteWhere([['id', 'IN', $idsToDelete]]);
        }

        return true;
    }

    /**
     * Save persons from current batch using the same name-based resolution as leads.
     */
    protected function savePersons(ImportBatchContract $batch): bool
    {
        $defaultSourceId = $this->import->source_id ?? null;

        foreach ($batch->data as $rowData) {
            try {
                $email = $rowData['email'] ?? null;
                $existed = $email ? $this->personStorage->has($email) : false;

                if (! $existed && $email) {
                    $existing = $this->personRepository->getModel()
                        ->where('normalized_email', strtolower(trim($email)))
                        ->whereNull('merged_into_id')
                        ->exists();
                    $existed = $existing;
                }

                $person = $this->personImportProcessor->process($rowData, $defaultSourceId);

                if (! $person) {
                    continue;
                }

                if ($existed) {
                    $this->updatedItemsCount++;
                } else {
                    $this->createdItemsCount++;
                }

                if ($email) {
                    $this->personStorage->set($email, $person->id);
                }
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return true;
    }

    /**
     * Check if email exists.
     */
    public function isEmailExist(string $email): bool
    {
        return $this->personStorage->has($email);
    }
}
