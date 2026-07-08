<?php

namespace App\Firebase;

use App\Firebase\Exceptions\FirebaseConnectionException;
use App\Firebase\Http\FirestoreRestClient;

class FirebaseManager
{
    protected ?FirestoreRestClient $client = null;

    public function firestore(): FirestoreRestClient
    {
        if ($this->client !== null) {
            return $this->client;
        }

        try {
            $serviceAccount = $this->resolveServiceAccountArray();

            if ($serviceAccount === null) {
                throw new FirebaseConnectionException('Firebase credentials are not configured.');
            }

            $this->client = new FirestoreRestClient(
                $serviceAccount,
                $this->resolveProjectId($serviceAccount),
            );
        } catch (FirebaseConnectionException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            throw new FirebaseConnectionException('Unable to connect to Firebase.');
        }

        return $this->client;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function resolveServiceAccountArray(): ?array
    {
        $json = config('firebase.credentials_json');

        if (is_string($json) && $json !== '') {
            $decoded = json_decode($json, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        $path = config('firebase.credentials');

        if (! is_string($path) || $path === '' || ! is_readable($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  array<string, mixed>|null  $serviceAccount
     */
    protected function resolveProjectId(?array $serviceAccount = null): string
    {
        if ($projectId = config('firebase.project_id')) {
            return (string) $projectId;
        }

        $serviceAccount ??= $this->resolveServiceAccountArray();

        if (! empty($serviceAccount['project_id'])) {
            return (string) $serviceAccount['project_id'];
        }

        throw new FirebaseConnectionException('Firebase project ID is not configured.');
    }
}
