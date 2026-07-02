<?php

namespace AHATechnocrats\EmailTemplate\Models;

use Illuminate\Database\Eloquent\Model;
use AHATechnocrats\EmailTemplate\Contracts\EmailTemplate as EmailTemplateContract;

class EmailTemplate extends Model implements EmailTemplateContract
{
    protected $fillable = [
        'name',
        'subject',
        'content',
    ];
}
