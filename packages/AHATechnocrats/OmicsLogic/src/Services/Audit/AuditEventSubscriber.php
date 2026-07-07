<?php

namespace AHATechnocrats\OmicsLogic\Services\Audit;

use AHATechnocrats\OmicsLogic\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

/**
 * Listens to the CRM's lifecycle events and turns them into audit records.
 *
 * Krayin fires paired "<entity>.<op>.before" / "<entity>.<op>.after" events.
 * The "before" phase carries only an id (or the model), so we snapshot the
 * current database state there; the "after" phase carries the mutated model,
 * which we diff against the snapshot to record exactly what changed.
 *
 * Registered as a singleton so the per-request pending snapshots survive
 * between the before/after phases of the same action.
 */
class AuditEventSubscriber
{
    /**
     * event name => [entity, operation, phase]
     *
     * @var array<string, array{entity: string, operation: string, phase: string}>
     */
    protected array $map = [];

    /**
     * Snapshots captured during a "before" phase, awaiting their "after".
     * Keyed by "<entity>:<operation>" and used as a LIFO stack so nested/mass
     * actions pair up correctly.
     *
     * @var array<string, array<int, array{id: int|null, attributes: array<string, mixed>}>>
     */
    protected array $pending = [];

    public function __construct(
        protected AuditDiffer $differ,
        protected AuditDescriber $describer,
    ) {
        $this->map = $this->buildMap();
    }

    /**
     * The full list of event names this subscriber wants to listen to.
     *
     * @return array<int, string>
     */
    public function events(): array
    {
        return array_keys($this->map);
    }

    /**
     * Entry point invoked for every registered event.
     *
     * @param  array<int, mixed>  $args  The arguments the event was dispatched with.
     */
    public function handle(string $eventName, array $args): void
    {
        if (! config('audit.enabled', true)) {
            return;
        }

        if (app()->runningInConsole() && ! config('audit.log_console', false)) {
            return;
        }

        $meta = $this->map[$eventName] ?? null;

        if ($meta === null) {
            return;
        }

        $payload = $args[0] ?? null;

        try {
            match ($meta['operation']) {
                'create' => $this->onCreate($meta['entity'], $meta['phase'], $eventName, $payload),
                'update' => $this->onUpdate($meta['entity'], $meta['phase'], $eventName, $payload),
                'delete' => $this->onDelete($meta['entity'], $meta['phase'], $eventName, $payload),
                default => null,
            };
        } catch (\Throwable $e) {
            // Auditing is a side effect — it must never break the action itself.
            report($e);
        }
    }

    protected function onCreate(string $entity, string $phase, string $eventName, mixed $payload): void
    {
        if ($phase !== 'after' || ! $payload instanceof Model) {
            return;
        }

        $attributes = $this->attributes($payload);

        $this->write(
            entity: $entity,
            action: 'created',
            event: $eventName,
            id: $this->idOf($payload),
            name: $this->nameOf($entity, $attributes),
            before: null,
            after: $this->differ->snapshot($attributes),
        );
    }

    protected function onUpdate(string $entity, string $phase, string $eventName, mixed $payload): void
    {
        if ($phase === 'before') {
            if (($snapshot = $this->loadSnapshot($entity, $payload)) !== null) {
                $this->push($entity, 'update', $snapshot);
            }

            return;
        }

        if (! $payload instanceof Model) {
            return;
        }

        $after = $this->attributes($payload);
        $before = $this->pop($entity, 'update')['attributes'] ?? null;

        $changes = $this->differ->diff($before, $after);

        if (empty($changes['before']) && empty($changes['after'])) {
            return;
        }

        $this->write(
            entity: $entity,
            action: 'updated',
            event: $eventName,
            id: $this->idOf($payload),
            name: $this->nameOf($entity, $after),
            before: $changes['before'],
            after: $changes['after'],
        );
    }

    protected function onDelete(string $entity, string $phase, string $eventName, mixed $payload): void
    {
        if ($phase === 'before') {
            if (($snapshot = $this->loadSnapshot($entity, $payload)) !== null) {
                $this->push($entity, 'delete', $snapshot);
            }

            return;
        }

        $entry = $this->pop($entity, 'delete');
        $snapshot = $entry['attributes']
            ?? ($payload instanceof Model ? $this->attributes($payload) : null);
        $id = $entry['id'] ?? $this->idOf($payload);

        $this->write(
            entity: $entity,
            action: 'deleted',
            event: $eventName,
            id: $id,
            name: $this->nameOf($entity, $snapshot ?? []),
            before: $this->differ->snapshot($snapshot),
            after: null,
        );
    }

    /**
     * Persist an audit record via the shared logger.
     *
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    protected function write(
        string $entity,
        string $action,
        string $event,
        ?int $id,
        ?string $name,
        ?array $before,
        ?array $after,
    ): void {
        $label = (string) config("audit.entities.$entity.label", ucfirst($entity));

        $changes = $action === 'updated'
            ? ['before' => $before ?? [], 'after' => $after ?? []]
            : ['before' => [], 'after' => []];

        AuditLogger::log(
            action: $action,
            entityType: $entity,
            entityId: $id,
            before: $before,
            after: $after,
            event: $event,
            description: $this->describer->describe($entity, $label, $action, $id, $name, $changes),
        );
    }

    /**
     * Build the event => metadata lookup from the entity registry.
     *
     * @return array<string, array{entity: string, operation: string, phase: string}>
     */
    protected function buildMap(): array
    {
        $map = [];

        foreach ((array) config('audit.entities', []) as $entity => $config) {
            $prefixes = $this->operationPrefixes($config['events'] ?? $entity);

            foreach ($prefixes as $operation => $prefix) {
                foreach (['before', 'after'] as $phase) {
                    $map[$prefix.'.'.$phase] = [
                        'entity' => $entity,
                        'operation' => $operation,
                        'phase' => $phase,
                    ];
                }
            }
        }

        return $map;
    }

    /**
     * Resolve the create/update/delete event prefixes for an entity.
     *
     * @param  string|array<string, string>  $events
     * @return array<string, string>
     */
    protected function operationPrefixes(string|array $events): array
    {
        if (is_array($events)) {
            return $events;
        }

        return [
            'create' => $events.'.create',
            'update' => $events.'.update',
            'delete' => $events.'.delete',
        ];
    }

    /**
     * Load a fresh snapshot of an entity's current database state.
     *
     * @return array<string, mixed>|null
     */
    protected function loadSnapshot(string $entity, mixed $payload): ?array
    {
        if ($payload instanceof Model) {
            return $this->attributes($payload);
        }

        $id = $this->idOf($payload);
        $model = $this->modelFor($entity);

        if ($id === null || $model === null) {
            return null;
        }

        $record = $model->newQuery()->find($id);

        return $record ? $this->attributes($record) : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function attributes(Model $model): array
    {
        return $model->getAttributes();
    }

    protected function modelFor(string $entity): ?Model
    {
        $class = config("audit.entities.$entity.model");

        if (! is_string($class) || ! class_exists($class)) {
            return null;
        }

        $instance = new $class;

        return $instance instanceof Model ? $instance : null;
    }

    protected function idOf(mixed $payload): ?int
    {
        if ($payload instanceof Model) {
            $key = $payload->getKey();

            return is_numeric($key) ? (int) $key : null;
        }

        return is_numeric($payload) ? (int) $payload : null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function nameOf(string $entity, array $attributes): ?string
    {
        $field = config("audit.entities.$entity.label_field", 'name');

        $value = $attributes[$field] ?? null;

        return $value !== null && $value !== '' ? (string) $value : null;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function push(string $entity, string $operation, array $attributes): void
    {
        $id = $attributes['id'] ?? null;

        $this->pending[$entity.':'.$operation][] = [
            'id' => is_numeric($id) ? (int) $id : null,
            'attributes' => $attributes,
        ];
    }

    /**
     * @return array{id: int|null, attributes: array<string, mixed>}|null
     */
    protected function pop(string $entity, string $operation): ?array
    {
        $key = $entity.':'.$operation;

        if (empty($this->pending[$key])) {
            return null;
        }

        return array_pop($this->pending[$key]);
    }
}
