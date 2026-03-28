<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

final class TemporaryUpload extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'session_id',
        'disk',
        'path',
        'file_name',
        'mime_type',
        'size',
        'purpose',
        'status',
        'final_visibility',
    ];
}
