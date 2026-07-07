<?php

namespace AHATechnocrats\EmailTemplate\Models;

use AHATechnocrats\EmailTemplate\Contracts\EmailTemplate as EmailTemplateContract;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model implements EmailTemplateContract
{
    protected $fillable = [
        'name',
        'subject',
        'content',
    ];
}
