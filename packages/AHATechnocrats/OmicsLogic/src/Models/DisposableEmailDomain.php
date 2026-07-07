<?php

namespace AHATechnocrats\OmicsLogic\Models;

use Illuminate\Database\Eloquent\Model;

class DisposableEmailDomain extends Model
{
    protected $table = 'omics_disposable_email_domains';

    public $timestamps = false;

    protected $fillable = ['domain'];
}
