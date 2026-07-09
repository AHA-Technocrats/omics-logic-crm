<?php

namespace AHATechnocrats\Installer\Listeners;

use AHATechnocrats\User\Repositories\UserRepository;
use GuzzleHttp\Client;

class Installer
{
    /**
     * Api endpoint
     *
     * @var string
     */
    protected const API_ENDPOINT = 'https://updates.ahatechnocrats.com/api/updates';

    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected UserRepository $userRepository) {}

    /**
     * After AHATechnocrats is successfully installed
     *
     * @return void
     */
    public function installed()
    {
        $user = $this->userRepository->first();

        $httpClient = new Client;

        try {
            $httpClient->request('POST', self::API_ENDPOINT, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'domain' => config('app.url'),
                    'email' => $user?->email,
                    'name' => $user?->name,
                    'country_code' => config('app.default_country') ?? 'IN',
                ],
            ]);
        } catch (\Exception $e) {
            /**
             * Skip the error
             */
        }
    }
}
