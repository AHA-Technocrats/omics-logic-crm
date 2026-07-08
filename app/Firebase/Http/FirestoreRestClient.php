<?php

namespace App\Firebase\Http;

use App\Firebase\Exceptions\FirebaseConnectionException;
use App\Firebase\Support\GoogleHttpClientFactory;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use GuzzleHttp\Client;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class FirestoreRestClient
{
    protected ?string $accessToken = null;

    protected ?int $tokenExpiresAt = null;

    public function __construct(
        protected array $serviceAccount,
        public string $projectId,
    ) {}

    /**
     * @return array<string, mixed>|null
     */
    public function getDocument(string $collection, string $documentId): ?array
    {
        $path = sprintf(
            'projects/%s/databases/(default)/documents/%s/%s',
            $this->projectId,
            $collection,
            $documentId,
        );

        try {
            $response = $this->http()->get($this->baseUrl().'/'.$path);

            if ($response->status() === 404) {
                return null;
            }

            if (! $response->successful()) {
                throw new FirebaseConnectionException('Unable to query Firestore.');
            }

            return $this->mapDocument($response->json());
        } catch (FirebaseConnectionException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            throw new FirebaseConnectionException('Unable to query Firestore.');
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function queryCollection(
        string $collection,
        ?string $parentDocumentPath = null,
        ?array $fieldFilter = null,
        ?string $orderByField = null,
        string $orderDirection = 'DESC',
        int $limit = 20,
        ?string $startAfterDocumentId = null,
    ): array {
        $structuredQuery = [
            'from' => [
                ['collectionId' => $collection],
            ],
            'limit' => $limit,
        ];

        if ($fieldFilter) {
            $structuredQuery['where'] = isset($fieldFilter['compositeFilter'])
                ? ['compositeFilter' => $fieldFilter['compositeFilter']]
                : ['fieldFilter' => $fieldFilter];
        }

        if ($orderByField) {
            $structuredQuery['orderBy'] = [[
                'field' => ['fieldPath' => $orderByField],
                'direction' => strtoupper($orderDirection) === 'ASC' ? 'ASCENDING' : 'DESCENDING',
            ]];
        }

        if ($startAfterDocumentId) {
            $startDoc = $parentDocumentPath
                ? $this->getDocumentByPath($parentDocumentPath.'/'.$startAfterDocumentId)
                : $this->getDocument($collection, $startAfterDocumentId);

            if ($startDoc) {
                $structuredQuery['startAt'] = [
                    'values' => [$this->encodeValue($startDoc)],
                    'before' => false,
                ];
            }
        }

        $parent = $parentDocumentPath
            ?: sprintf('projects/%s/databases/(default)/documents', $this->projectId);

        return $this->runQuery($parent, $structuredQuery);
    }

    /**
     * @param  array<string, mixed>  $structuredQuery
     * @return array<int, array<string, mixed>>
     */
    protected function runQuery(string $parent, array $structuredQuery): array
    {
        try {
            $response = $this->http()->post($this->baseUrl().'/'.$parent.':runQuery', [
                'structuredQuery' => $structuredQuery,
            ]);

            if (! $response->successful()) {
                throw new FirebaseConnectionException('Unable to query Firestore.');
            }

            $documents = [];

            foreach ($response->json() as $row) {
                if (! empty($row['document'])) {
                    $documents[] = $this->mapDocument($row['document']);
                }
            }

            return $documents;
        } catch (FirebaseConnectionException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            throw new FirebaseConnectionException('Unable to query Firestore.');
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function getDocumentByPath(string $documentPath): ?array
    {
        try {
            $response = $this->http()->get($this->baseUrl().'/'.$documentPath);

            return $response->successful() ? $this->mapDocument($response->json()) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    protected function mapDocument(array $document): array
    {
        $name = (string) ($document['name'] ?? '');
        $segments = explode('/', $name);
        $id = array_pop($segments) ?: null;

        $fields = [];

        foreach ((array) ($document['fields'] ?? []) as $key => $value) {
            $fields[$key] = $this->decodeValue($value);
        }

        return array_merge(['id' => $id], $fields);
    }

    /**
     * @param  array<string, mixed>  $value
     */
    protected function decodeValue(array $value): mixed
    {
        $type = array_key_first($value);

        return match ($type) {
            'stringValue' => $value['stringValue'],
            'integerValue' => (int) $value['integerValue'],
            'doubleValue' => (float) $value['doubleValue'],
            'booleanValue' => (bool) $value['booleanValue'],
            'timestampValue' => $value['timestampValue'],
            'nullValue' => null,
            'mapValue' => collect($value['mapValue']['fields'] ?? [])
                ->mapWithKeys(fn ($item, $key) => [$key => $this->decodeValue($item)])
                ->all(),
            'arrayValue' => array_map(
                fn ($item) => $this->decodeValue($item),
                $value['arrayValue']['values'] ?? [],
            ),
            default => $value[$type] ?? null,
        };
    }

    /**
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    protected function encodeValue(array $document): array
    {
        $values = [];

        foreach ($document as $key => $value) {
            if ($key === 'id') {
                continue;
            }

            $values[] = $this->encodeScalar($value);
        }

        return ['arrayValue' => ['values' => $values]];
    }

    protected function encodeScalar(mixed $value): array
    {
        return match (true) {
            is_string($value) => ['stringValue' => $value],
            is_int($value) => ['integerValue' => (string) $value],
            is_float($value) => ['doubleValue' => $value],
            is_bool($value) => ['booleanValue' => $value],
            $value === null => ['nullValue' => null],
            default => ['stringValue' => (string) $value],
        };
    }

    protected function http(): PendingRequest
    {
        return Http::withToken($this->accessToken())
            ->withOptions(GoogleHttpClientFactory::guzzleOptions())
            ->acceptJson()
            ->asJson();
    }

    protected function accessToken(): string
    {
        if ($this->accessToken && $this->tokenExpiresAt && time() < ($this->tokenExpiresAt - 60)) {
            return $this->accessToken;
        }

        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/cloud-platform', 'https://www.googleapis.com/auth/datastore'],
            $this->serviceAccount,
        );

        $handler = HttpHandlerFactory::build(new Client(GoogleHttpClientFactory::guzzleOptions()));
        $token = $credentials->fetchAuthToken($handler);

        $this->accessToken = (string) ($token['access_token'] ?? '');
        $this->tokenExpiresAt = time() + (int) ($token['expires_in'] ?? 3600);

        if ($this->accessToken === '') {
            throw new FirebaseConnectionException('Unable to authenticate with Firebase.');
        }

        return $this->accessToken;
    }

    protected function baseUrl(): string
    {
        return 'https://firestore.googleapis.com/v1';
    }
}
