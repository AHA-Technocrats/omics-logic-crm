<?php

namespace AHATechnocrats\OmicsLogic\Database\Seeders;

use AHATechnocrats\OmicsLogic\Models\Connector;
use AHATechnocrats\OmicsLogic\Models\DisposableEmailDomain;
use Illuminate\Database\Seeder;

class OmicsLogicSeeder extends Seeder
{
    public function run(): void
    {
        $domains = [
            'mailinator.com',
            'guerrillamail.com',
            '10minutemail.com',
            'tempmail.com',
            'yopmail.com',
            'throwaway.email',
            'getnada.com',
        ];

        foreach ($domains as $domain) {
            DisposableEmailDomain::query()->firstOrCreate(['domain' => $domain]);
        }

        $connectors = [
            ['type' => 'web_form', 'name' => 'Web Forms', 'status' => 'connected'],
            ['type' => 'portal_api', 'name' => 'OmicsLogic Portal', 'status' => 'disabled'],
            ['type' => 'csv_import', 'name' => 'CSV / Excel Import', 'status' => 'connected'],
        ];

        foreach ($connectors as $connector) {
            Connector::query()->firstOrCreate(
                ['type' => $connector['type']],
                $connector,
            );
        }
    }
}
