<?php

use AHATechnocrats\Activity\Providers\ActivityServiceProvider;
use AHATechnocrats\Admin\Providers\AdminServiceProvider;
use AHATechnocrats\Attribute\Providers\AttributeServiceProvider;
use AHATechnocrats\Automation\Providers\WorkflowServiceProvider;
use AHATechnocrats\Contact\Providers\ContactServiceProvider;
use AHATechnocrats\Core\Providers\CoreServiceProvider;
use AHATechnocrats\DataGrid\Providers\DataGridServiceProvider;
use AHATechnocrats\DataTransfer\Providers\DataTransferServiceProvider;
use AHATechnocrats\Email\Providers\EmailServiceProvider;
use AHATechnocrats\EmailTemplate\Providers\EmailTemplateServiceProvider;
use AHATechnocrats\Installer\Providers\InstallerServiceProvider;
use AHATechnocrats\Lead\Providers\LeadServiceProvider;
use AHATechnocrats\Marketing\Providers\MarketingServiceProvider;
use AHATechnocrats\OmicsLogic\Providers\OmicsLogicServiceProvider;
use AHATechnocrats\Product\Providers\ProductServiceProvider;
use AHATechnocrats\Quote\Providers\QuoteServiceProvider;
use AHATechnocrats\Tag\Providers\TagServiceProvider;
use AHATechnocrats\User\Providers\UserServiceProvider;
use AHATechnocrats\Warehouse\Providers\WarehouseServiceProvider;
use AHATechnocrats\WebForm\Providers\WebFormServiceProvider;
use App\Providers\AppServiceProvider;
use Barryvdh\DomPDF\ServiceProvider;
use Konekt\Concord\ConcordServiceProvider;
use Prettus\Repository\Providers\RepositoryServiceProvider;

return [
    /*
     * Package Service Providers...
     */
    ServiceProvider::class,
    ConcordServiceProvider::class,
    RepositoryServiceProvider::class,

    /*
     * Application Service Providers...
     */
    AppServiceProvider::class,

    /*
     * Webkul Service Providers...
     */
    ActivityServiceProvider::class,
    AdminServiceProvider::class,
    AttributeServiceProvider::class,
    WorkflowServiceProvider::class,
    ContactServiceProvider::class,
    CoreServiceProvider::class,
    DataGridServiceProvider::class,
    DataTransferServiceProvider::class,
    EmailTemplateServiceProvider::class,
    EmailServiceProvider::class,
    MarketingServiceProvider::class,
    InstallerServiceProvider::class,
    LeadServiceProvider::class,
    ProductServiceProvider::class,
    QuoteServiceProvider::class,
    TagServiceProvider::class,
    UserServiceProvider::class,
    WarehouseServiceProvider::class,
    WebFormServiceProvider::class,
    OmicsLogicServiceProvider::class,
];
