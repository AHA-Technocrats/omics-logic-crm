<?php

namespace AHATechnocrats\OmicsLogic\Services\Portal;

use DateTimeInterface;
use Illuminate\Support\Facades\Http;

class PortalApiClient
{
    /**
     * Fetch learner/lead records from the OmicsLogic portal API.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchLeads(
        string $apiUrl,
        ?string $apiToken,
        ?DateTimeInterface $since = null,
        ?string $leadsPath = null,
    ): array {
        $path = $leadsPath ?: (string) config('omicslogic.portal.leads_path', '/api/crm/leads');
        $url = rtrim($apiUrl, '/').'/'.ltrim($path, '/');

        $request = Http::timeout((int) config('omicslogic.portal.timeout', 30))
            ->acceptJson();

        if ($apiToken) {
            $request = $request->withToken($apiToken);
        }

        $params = [];

        if ($since) {
            $params['since'] = $since->format('Y-m-d\TH:i:s\Z');
        }

        $response = $request->get($url, $params);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Portal API request failed ('.$response->status().'): '.$response->body()
            );
        }

        return $this->extractLeads($response->json());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function extractLeads(mixed $body): array
    {
        if (! is_array($body)) {
            return [];
        }

        if (isset($body['data']) && is_array($body['data'])) {
            return array_values(array_filter($body['data'], 'is_array'));
        }

        if (isset($body['leads']) && is_array($body['leads'])) {
            return array_values(array_filter($body['leads'], 'is_array'));
        }

        if (array_is_list($body)) {
            return array_values(array_filter($body, 'is_array'));
        }

        return [];
    }
}
