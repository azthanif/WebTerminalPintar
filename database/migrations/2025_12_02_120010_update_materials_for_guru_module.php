<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (!Schema::hasColumn('materials', 'status')) {
                $table->string('status', 30)->default('Terunggah')->after('download_url');
            }

            if (!Schema::hasColumn('materials', 'downloads_count')) {
                $table->unsignedInteger('downloads_count')->default(0)->after('status');
            }

            if (!Schema::hasColumn('materials', 'uploaded_by')) {
                $table->foreignId('uploaded_by')
                    ->nullable()
                    ->after('downloads_count')
                    ->constrained('users')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }

            if (!Schema::hasColumn('materials', 'visibility')) {
                $table->string('visibility', 20)->default('internal')->after('uploaded_by');
            }

            if (!Schema::hasColumn('materials', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('published_at');
            }

            if (!Schema::hasColumn('materials', 'labels')) {
                $table->json('labels')->nullable()->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (Schema::hasColumn('materials', 'labels')) {
                $table->dropColumn('labels');
            }

            if (Schema::hasColumn('materials', 'expires_at')) {
                $table->dropColumn('expires_at');
            }

            if (Schema::hasColumn('materials', 'visibility')) {
                $table->dropColumn('visibility');
            }

            if (Schema::hasColumn('materials', 'uploaded_by')) {
                $table->dropForeign(['uploaded_by']);
                $table->dropColumn('uploaded_by');
            }

            if (Schema::hasColumn('materials', 'downloads_count')) {
                $table->dropColumn('downloads_count');
            }

            if (Schema::hasColumn('materials', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
