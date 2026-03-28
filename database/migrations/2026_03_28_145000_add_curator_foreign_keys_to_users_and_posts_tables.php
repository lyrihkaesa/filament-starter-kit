<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('avatar_curator_id')
                ->nullable()
                ->after('avatar')
                ->constrained('curator')
                ->nullOnDelete();
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->foreignId('thumbnail_curator_id')
                ->nullable()
                ->after('thumbnail')
                ->constrained('curator')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('thumbnail_curator_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('avatar_curator_id');
        });
    }
};
