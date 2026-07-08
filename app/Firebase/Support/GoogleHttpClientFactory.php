<?php

namespace App\Firebase\Support;

use Google\Auth\HttpHandler\HttpClientCache;
use GuzzleHttp\Client;

class GoogleHttpClientFactory
{
    /**
     * @return array<string, mixed>
     */
    public static function guzzleOptions(): array
    {
        $options = [
            'timeout' => (int) config('firebase.http.timeout', 15),
            'connect_timeout' => (int) config('firebase.http.connect_timeout', 10),
        ];

        $verify = self::resolveVerify();

        if ($verify === false) {
            $options['verify'] = false;
            $options['curl'] = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ];
        } else {
            $options['verify'] = $verify;
        }

        return $options;
    }

    public static function resolveVerify(): bool|string
    {
        $configured = config('firebase.http.verify');

        if ($configured !== null && $configured !== '') {
            if (is_string($configured) && $configured !== 'true' && $configured !== 'false' && is_readable($configured)) {
                return $configured;
            }

            return filter_var($configured, FILTER_VALIDATE_BOOLEAN);
        }

        $caBundle = config('firebase.http.ca_bundle');

        if (is_string($caBundle) && $caBundle !== '' && is_readable($caBundle)) {
            return $caBundle;
        }

        if (app()->environment('local')) {
            return false;
        }

        $defaultBundle = storage_path('app/cacert.pem');

        if (is_readable($defaultBundle)) {
            return $defaultBundle;
        }

        return true;
    }

    public static function configure(): void
    {
        HttpClientCache::setHttpClient(new Client(self::guzzleOptions()));

        $verify = self::resolveVerify();

        if (is_string($verify) && is_readable($verify)) {
            ini_set('curl.cainfo', $verify);
            ini_set('openssl.cafile', $verify);
        }
    }
}
