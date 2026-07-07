<?php

namespace AHATechnocrats\Automation\Models;

use AHATechnocrats\Automation\Contracts\Webhook as ContractsWebhook;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model implements ContractsWebhook
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'entity_type',
        'description',
        'method',
        'end_point',
        'query_params',
        'headers',
        'payload_type',
        'raw_payload_type',
        'payload',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'query_params' => 'array',
        'headers' => 'array',
        'payload' => 'array',
    ];
}
