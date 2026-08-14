<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TemporaryUploadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
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
])]
#[WithoutIncrementing]
final class TemporaryUpload extends Model
{
    /** @use HasFactory<TemporaryUploadFactory> */
    use HasFactory;

    use HasUuids;

    protected $keyType = 'string';
}
