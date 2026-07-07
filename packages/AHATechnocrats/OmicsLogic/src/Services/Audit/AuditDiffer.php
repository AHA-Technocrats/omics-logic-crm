<?php

namespace AHATechnocrats\OmicsLogic\Services\Audit;

/**
 * Computes clean, redacted before/after representations of a model's state.
 *
 * The differ is intentionally pure (no framework/database dependencies) so it
 * is trivial to unit test and reason about.
 */
class AuditDiffer
{
    /**
     * @param  array<int, string>  $redactedKeys  Attribute names whose values must never be stored.
     * @param  array<int, string>  $ignoredKeys  Attribute names excluded from diffs entirely.
     */
    public function __construct(
        protected array $redactedKeys = [],
        protected array $ignoredKeys = [],
    ) {}

    /**
     * Build a full, cleaned snapshot for create/delete records.
     *
     * @param  array<string, mixed>|null  $attributes
     * @return array<string, mixed>|null
     */
    public function snapshot(?array $attributes): ?array
    {
        if ($attributes === null) {
            return null;
        }

        $snapshot = [];

        foreach ($attributes as $key => $value) {
            if ($this->isIgnored($key)) {
                continue;
            }

            $snapshot[$key] = $this->isRedacted($key)
                ? self::REDACTED
                : $this->presentable($value);
        }

        return $snapshot ?: null;
    }

    /**
     * Produce a changed-only diff between two states.
     *
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @return array{before: array<string, mixed>, after: array<string, mixed>}
     */
    public function diff(?array $before, ?array $after): array
    {
        $before ??= [];
        $after ??= [];

        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));

        $changedBefore = [];
        $changedAfter = [];

        foreach ($keys as $key) {
            if ($this->isIgnored($key)) {
                continue;
            }

            $oldValue = $before[$key] ?? null;
            $newValue = $after[$key] ?? null;

            if ($this->comparable($oldValue) === $this->comparable($newValue)) {
                continue;
            }

            if ($this->isRedacted($key)) {
                $changedBefore[$key] = self::REDACTED;
                $changedAfter[$key] = self::REDACTED;

                continue;
            }

            $changedBefore[$key] = $this->presentable($oldValue);
            $changedAfter[$key] = $this->presentable($newValue);
        }

        return [
            'before' => $changedBefore,
            'after' => $changedAfter,
        ];
    }

    /**
     * Placeholder stored in place of sensitive values.
     */
    public const REDACTED = '••••••';

    protected function isRedacted(string $key): bool
    {
        return in_array(strtolower($key), array_map('strtolower', $this->redactedKeys), true);
    }

    protected function isIgnored(string $key): bool
    {
        return in_array($key, $this->ignoredKeys, true);
    }

    /**
     * Normalize a value into a string used only for equality comparison.
     * This absorbs the string/int/bool differences between a raw database row
     * and a hydrated Eloquent model so diffs stay free of false positives.
     */
    protected function comparable(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if ($value === null) {
            return '';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return json_encode($value) ?: '';
    }

    /**
     * Convert a value into something safe and readable for JSON storage.
     */
    protected function presentable(mixed $value): mixed
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        return json_encode($value);
    }
}
