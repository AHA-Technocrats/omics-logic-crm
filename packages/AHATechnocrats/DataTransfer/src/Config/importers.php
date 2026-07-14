<?php

return [
    'persons' => [
        'title' => 'data_transfer::app.importers.persons.title',
        'importer' => 'AHATechnocrats\DataTransfer\Helpers\Importers\Persons\Importer',
        'sample_path' => 'data-transfer/samples/persons.csv',
    ],

    'leads' => [
        'title' => 'data_transfer::app.importers.leads.title',
        'importer' => 'AHATechnocrats\DataTransfer\Helpers\Importers\Leads\Importer',
        'sample_path' => 'data-transfer/samples/leads.csv',
    ],
];
