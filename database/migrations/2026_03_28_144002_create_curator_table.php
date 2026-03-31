<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curator', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('disk');
            $table->string('directory')->nullable();
            $table->string('visibility')->default('private');
            $table->string('name');
            $table->string('path')->index();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->string('type');
            $table->string('ext');
            $table->string('alt')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('caption')->nullable();
            $table->text('pretty_name')->nullable();
            $table->text('exif')->nullable();
            $table->longText('curations')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();

            // Custom Columns
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('privacy')->default('private');

            $table->timestamps();

            // Soft Deletes & Blameable
            $table->softDeletes();
            $table->foreignUuid('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('curator_media_usages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('curator_media_id')->constrained('curator')->cascadeOnDelete();
            $table->uuidMorphs('model');
            $table->string('field_name')->index();
            $table->timestamps();

            $table->unique(['curator_media_id', 'model_id', 'model_type', 'field_name'], 'curator_usage_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curator_media_usages');
        Schema::dropIfExists('curator');
    }
};
