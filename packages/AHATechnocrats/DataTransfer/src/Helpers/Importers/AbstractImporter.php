<?php

namespace AHATechnocrats\DataTransfer\Helpers\Importers;

use AHATechnocrats\Attribute\Repositories\AttributeRepository;
use AHATechnocrats\Attribute\Repositories\AttributeValueRepository;
use AHATechnocrats\Core\Contracts\Validations\Decimal;
use AHATechnocrats\DataTransfer\Contracts\Import as ImportContract;
use AHATechnocrats\DataTransfer\Contracts\ImportBatch as ImportBatchContract;
use AHATechnocrats\DataTransfer\Helpers\Error;
use AHATechnocrats\DataTransfer\Helpers\Import;
use AHATechnocrats\DataTransfer\Helpers\Source;
use AHATechnocrats\DataTransfer\Jobs\Import\Completed as CompletedJob;
use AHATechnocrats\DataTransfer\Jobs\Import\ImportBatch as ImportBatchJob;
use AHATechnocrats\DataTransfer\Jobs\Import\IndexBatch as IndexBatchJob;
use AHATechnocrats\DataTransfer\Jobs\Import\Indexing as IndexingJob;
use AHATechnocrats\DataTransfer\Jobs\Import\LinkBatch as LinkBatchJob;
use AHATechnocrats\DataTransfer\Jobs\Import\Linking as LinkingJob;
use AHATechnocrats\DataTransfer\Repositories\ImportBatchRepository;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;

abstract class AbstractImporter
{
    /**
     * Error code for system exception.
     */
    public const ERROR_CODE_SYSTEM_EXCEPTION = 'system_exception';

    /**
     * Error code for column not found.
     */
    public const ERROR_CODE_COLUMN_NOT_FOUND = 'column_not_found';

    /**
     * Error code for column empty header.
     */
    public const ERROR_CODE_COLUMN_EMPTY_HEADER = 'column_empty_header';

    /**
     * Error code for column name invalid.
     */
    public const ERROR_CODE_COLUMN_NAME_INVALID = 'column_name_invalid';

    /**
     * Error code for invalid attribute.
     */
    public const ERROR_CODE_INVALID_ATTRIBUTE = 'invalid_attribute_name';

    /**
     * Error code for wrong quotes.
     */
    public const ERROR_CODE_WRONG_QUOTES = 'wrong_quotes';

    /**
     * Error code for wrong columns number.
     */
    public const ERROR_CODE_COLUMNS_NUMBER = 'wrong_columns_number';

    /**
     * Error message templates.
     */
    protected array $errorMessages = [
        self::ERROR_CODE_SYSTEM_EXCEPTION => 'data_transfer::app.validation.errors.system',
        self::ERROR_CODE_COLUMN_NOT_FOUND => 'data_transfer::app.validation.errors.column-not-found',
        self::ERROR_CODE_COLUMN_EMPTY_HEADER => 'data_transfer::app.validation.errors.column-empty-headers',
        self::ERROR_CODE_COLUMN_NAME_INVALID => 'data_transfer::app.validation.errors.column-name-invalid',
        self::ERROR_CODE_INVALID_ATTRIBUTE => 'data_transfer::app.validation.errors.invalid-attribute',
        self::ERROR_CODE_WRONG_QUOTES => 'data_transfer::app.validation.errors.wrong-quotes',
        self::ERROR_CODE_COLUMNS_NUMBER => 'data_transfer::app.validation.errors.column-numbers',
    ];

    public const BATCH_SIZE = 100;

    /**
     * Is linking required.
     */
    protected bool $linkingRequired = false;

    /**
     * Is indexing required.
     */
    protected bool $indexingRequired = false;

    /**
     * Column that receives the source selected on the import form.
     * Importers that support source assignment override this (e.g. persons
     * use "primary_source_id", leads use "lead_source_id"). Null disables it.
     */
    protected ?string $sourceColumn = null;

    /**
     * Error helper instance.
     *
     * @var Error
     */
    protected $errorHelper;

    /**
     * Import instance.
     */
    protected ImportContract $import;

    /**
     * Source instance.
     *
     * @var Source
     */
    protected $source;

    /**
     * Valid column names.
     */
    protected array $validColumnNames = [];

    /**
     * Optional header aliases for importers that accept "raw" file headers.
     *
     * Keys are normalized header slugs (lowercased, non-alphanumerics stripped)
     * and values are the canonical internal column name — or an array of names
     * when one source column feeds several canonical columns. Leave empty to
     * disable aliasing (default) so existing importers are unaffected.
     *
     * @var array<string, string|string[]>
     */
    protected array $columnAliases = [];

    /**
     * Keep the untouched source row (original headers => values) under the
     * "_raw_submission" key so importers can persist the raw payload. Off by
     * default to preserve existing importer behavior.
     */
    protected bool $keepRawRow = false;

    /**
     * Array of numbers of validated rows as keys and boolean TRUE as values.
     */
    protected array $validatedRows = [];

    /**
     * Number of rows processed by validation.
     */
    protected int $processedRowsCount = 0;

    /**
     * Number of created items.
     */
    protected int $createdItemsCount = 0;

    /**
     * Number of updated items.
     */
    protected int $updatedItemsCount = 0;

    /**
     * Number of deleted items.
     */
    protected int $deletedItemsCount = 0;

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(
        protected ImportBatchRepository $importBatchRepository,
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository
    ) {}

    /**
     * Validate data row.
     */
    abstract public function validateRow(array $rowData, int $rowNumber): bool;

    /**
     * Import data rows.
     */
    abstract public function importBatch(ImportBatchContract $importBatchContract): bool;

    /**
     * Initialize Product error messages.
     */
    protected function initErrorMessages(): void
    {
        foreach ($this->errorMessages as $errorCode => $message) {
            $this->errorHelper->addErrorMessage($errorCode, trans($message));
        }
    }

    /**
     * Import instance.
     */
    public function setImport(ImportContract $import): self
    {
        $this->import = $import;

        return $this;
    }

    /**
     * Import instance.
     *
     * @param  Source  $errorHelper
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Import instance.
     *
     * @param  Error  $errorHelper
     */
    public function setErrorHelper($errorHelper): self
    {
        $this->errorHelper = $errorHelper;

        $this->initErrorMessages();

        return $this;
    }

    /**
     * Import instance.
     *
     * @return Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Retrieve valid column names.
     */
    public function getValidColumnNames(): array
    {
        return $this->validColumnNames;
    }

    /**
     * Normalize a raw header into a lookup slug used by the alias map.
     */
    protected function normalizeHeaderKey(string $header): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower(trim($header)));
    }

    /**
     * Map raw source headers onto canonical column names using the alias map.
     * A single header may expand into multiple canonical columns.
     */
    protected function mapColumnNames(array $columns): array
    {
        if (empty($this->columnAliases)) {
            return $columns;
        }

        $mapped = [];

        foreach ($columns as $column) {
            $canonical = $this->columnAliases[$this->normalizeHeaderKey((string) $column)] ?? $column;

            foreach ((array) $canonical as $name) {
                $mapped[] = $name;
            }
        }

        return $mapped;
    }

    /**
     * Re-key a source row onto canonical column names, optionally preserving the
     * untouched original row under "_raw_submission".
     */
    protected function mapRowColumns(array $rowData): array
    {
        if (empty($this->columnAliases) && ! $this->keepRawRow) {
            return $rowData;
        }

        $mapped = [];

        foreach ($rowData as $key => $value) {
            $canonical = $this->columnAliases[$this->normalizeHeaderKey((string) $key)] ?? $key;

            foreach ((array) $canonical as $name) {
                $mapped[$name] = $value;
            }
        }

        if ($this->keepRawRow) {
            $mapped['_raw_submission'] = $rowData;
        }

        return $mapped;
    }

    /**
     * Validate data.
     */
    public function validateData(): void
    {
        Event::dispatch('data_transfer.imports.validate.before', $this->import);

        $errors = [];

        $columns = $this->mapColumnNames($this->getSource()->getColumnNames());

        $absentColumns = array_diff($this->permanentAttributes, $columns);

        if (! empty($absentColumns)) {
            $errors[self::ERROR_CODE_COLUMN_NOT_FOUND] = $absentColumns;
        }

        foreach ($columns as $columnNumber => $columnName) {
            if (empty($columnName)) {
                $errors[self::ERROR_CODE_COLUMN_EMPTY_HEADER][] = $columnNumber + 1;
            } elseif (! preg_match('/^[a-z][a-z0-9_]*$/', $columnName)) {
                $errors[self::ERROR_CODE_COLUMN_NAME_INVALID][] = $columnName;
            } elseif (! in_array($columnName, $this->getValidColumnNames())) {
                $errors[self::ERROR_CODE_INVALID_ATTRIBUTE][] = $columnName;
            }
        }

        /**
         * Add Columns Errors.
         */
        foreach ($errors as $errorCode => $error) {
            $this->addErrors($errorCode, $error);
        }

        if (! $this->errorHelper->getErrorsCount()) {
            $this->saveValidatedBatches();
        }

        Event::dispatch('data_transfer.imports.validate.after', $this->import);
    }

    /**
     * Save validated batches.
     */
    protected function saveValidatedBatches(): self
    {
        $source = $this->getSource();

        $batchRows = [];

        $source->rewind();

        /**
         * Clean previous saved batches.
         */
        $this->importBatchRepository->deleteWhere([
            'import_id' => $this->import->id,
        ]);

        while (
            $source->valid()
            || count($batchRows)
        ) {
            if (
                count($batchRows) == self::BATCH_SIZE
                || ! $source->valid()
            ) {
                $this->importBatchRepository->create([
                    'import_id' => $this->import->id,
                    'data' => $batchRows,
                ]);

                $batchRows = [];
            }

            if ($source->valid()) {
                $rowData = $this->mapRowColumns($source->current());

                if ($this->validateRow($rowData, $source->getCurrentRowNumber())) {
                    $batchRows[] = $this->prepareRowForDb($rowData);
                }

                $this->processedRowsCount++;

                $source->next();
            }
        }

        return $this;
    }

    /**
     * Prepare validation rules.
     */
    public function getValidationRules(string $entityType, array $rowData): array
    {
        if (empty($entityType)) {
            return [];
        }

        $rules = [];

        $attributes = $this->attributeRepository->scopeQuery(fn ($query) => $query->whereIn('code', array_keys($rowData))->where('entity_type', $entityType))->get();

        foreach ($attributes as $attribute) {
            $validations = [];

            if ($attribute->type == 'boolean') {
                continue;
            } elseif ($attribute->type == 'address') {
                if (! $attribute->is_required) {
                    continue;
                }

                $validations = [
                    $attribute->code.'.address' => 'required',
                    $attribute->code.'.country' => 'required',
                    $attribute->code.'.state' => 'required',
                    $attribute->code.'.city' => 'required',
                    $attribute->code.'.postcode' => 'required',
                ];
            } elseif ($attribute->type == 'email') {
                $validations = [
                    $attribute->code => [$attribute->is_required ? 'required' : 'nullable'],
                    $attribute->code.'.*.value' => [$attribute->is_required ? 'required' : 'nullable', 'email'],
                    $attribute->code.'.*.label' => $attribute->is_required ? 'required' : 'nullable',
                ];
            } elseif ($attribute->type == 'phone') {
                $validations = [
                    $attribute->code => [$attribute->is_required ? 'required' : 'nullable'],
                    $attribute->code.'.*.value' => [$attribute->is_required ? 'required' : 'nullable'],
                    $attribute->code.'.*.label' => $attribute->is_required ? 'required' : 'nullable',
                ];
            } else {
                $validations[$attribute->code] = [$attribute->is_required ? 'required' : 'nullable'];

                if ($attribute->type == 'text' && $attribute->validation) {
                    array_push($validations[$attribute->code],
                        $attribute->validation == 'decimal'
                        ? new Decimal
                        : $attribute->validation
                    );
                }

                if ($attribute->type == 'price') {
                    array_push($validations[$attribute->code], new Decimal);
                }
            }

            if ($attribute->is_unique) {
                array_push($validations[in_array($attribute->type, ['email', 'phone'])
                    ? $attribute->code.'.*.value'
                    : $attribute->code
                ], function ($field, $value, $fail) use ($attribute) {
                    if (! $this->attributeValueRepository->isValueUnique(null, $attribute->entity_type, $attribute, $field)) {
                        $fail(trans('data_transfer::app.validation.errors.already-exists', ['attribute' => $attribute->name]));
                    }
                });
            }

            $rules = [
                ...$rules,
                ...$validations,
            ];
        }

        return $rules;
    }

    /**
     * Start the import process.
     */
    public function importData(?ImportBatchContract $importBatch = null): bool
    {
        if ($importBatch) {
            $this->importBatch($importBatch);

            return true;
        }

        $typeBatches = [];

        foreach ($this->import->batches as $batch) {
            $typeBatches['import'][] = new ImportBatchJob($batch);

            if ($this->isLinkingRequired()) {
                $typeBatches['link'][] = new LinkBatchJob($batch);
            }

            if ($this->isIndexingRequired()) {
                $typeBatches['index'][] = new IndexBatchJob($batch);
            }
        }

        $chain[] = Bus::batch($typeBatches['import']);

        if (! empty($typeBatches['link'])) {
            $chain[] = new LinkingJob($this->import);

            $chain[] = Bus::batch($typeBatches['link']);
        }

        if (! empty($typeBatches['index'])) {
            $chain[] = new IndexingJob($this->import);

            $chain[] = Bus::batch($typeBatches['index']);
        }

        $chain[] = new CompletedJob($this->import);

        Bus::chain($chain)->dispatch();

        return true;
    }

    /**
     * Link resource data.
     */
    public function linkData(ImportBatchContract $importBatch): bool
    {
        $this->linkBatch($importBatch);

        return true;
    }

    /**
     * Index resource data.
     */
    public function indexData(ImportBatchContract $importBatch): bool
    {
        $this->indexBatch($importBatch);

        return true;
    }

    /**
     * Add errors to error aggregator.
     */
    protected function addErrors(string $code, mixed $errors): void
    {
        $this->errorHelper->addError(
            $code,
            null,
            implode('", "', $errors)
        );
    }

    /**
     * Add row as skipped.
     *
     * @param  int|null  $rowNumber
     * @param  string|null  $columnName
     * @param  string|null  $errorMessage
     * @return $this
     */
    protected function skipRow($rowNumber, string $errorCode, $columnName = null, $errorMessage = null): self
    {
        $this->errorHelper->addError(
            $errorCode,
            $rowNumber,
            $columnName,
            $errorMessage
        );

        $this->errorHelper->addRowToSkip($rowNumber);

        return $this;
    }

    /**
     * Prepare row data to save into the database.
     */
    protected function prepareRowForDb(array $rowData): array
    {
        $rowData = array_map(function ($value) {
            return $value === '' ? null : $value;
        }, $rowData);

        return $rowData;
    }

    /**
     * Assign the source chosen on the import form to a row.
     *
     * Only fills the configured source column when the row does not already
     * carry a value, so per-row values in the file always win. Skipped for
     * delete imports and when the importer does not support source assignment.
     */
    protected function applyImportSource(array $rowData): array
    {
        if (
            $this->sourceColumn === null
            || $this->import->action == Import::ACTION_DELETE
        ) {
            return $rowData;
        }

        $sourceId = $this->import->source_id ?? null;

        if (empty($sourceId)) {
            return $rowData;
        }

        if (empty($rowData[$this->sourceColumn])) {
            $rowData[$this->sourceColumn] = $sourceId;
        }

        return $rowData;
    }

    /**
     * Returns number of checked rows.
     */
    public function getProcessedRowsCount(): int
    {
        return $this->processedRowsCount;
    }

    /**
     * Returns number of created items count.
     */
    public function getCreatedItemsCount(): int
    {
        return $this->createdItemsCount;
    }

    /**
     * Returns number of updated items count.
     */
    public function getUpdatedItemsCount(): int
    {
        return $this->updatedItemsCount;
    }

    /**
     * Returns number of deleted items count.
     */
    public function getDeletedItemsCount(): int
    {
        return $this->deletedItemsCount;
    }

    /**
     * Is linking resource required for the import operation.
     */
    public function isLinkingRequired(): bool
    {
        if ($this->import->action == Import::ACTION_DELETE) {
            return false;
        }

        return $this->linkingRequired;
    }

    /**
     * Is indexing resource required for the import operation.
     */
    public function isIndexingRequired(): bool
    {
        if ($this->import->action == Import::ACTION_DELETE) {
            return false;
        }

        return $this->indexingRequired;
    }
}
