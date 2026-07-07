<?php

namespace AHATechnocrats\OmicsLogic\Enums;

enum ConnectorType: string
{
    case WebForm = 'web_form';
    case PortalApi = 'portal_api';
    case CsvImport = 'csv_import';
    case Webhook = 'webhook';
}
