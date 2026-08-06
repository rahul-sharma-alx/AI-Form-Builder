<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->json('settings')->nullable()->after('schema');
            $table->json('metadata')->nullable()->after('settings');
            $table->unsignedInteger('version')->default(1)->after('metadata');
            $table->timestamp('published_at')->nullable()->after('version');
            $table->timestamp('last_saved_at')->nullable()->after('published_at');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['settings', 'metadata', 'version', 'published_at', 'last_saved_at']);
        });
    }
};
