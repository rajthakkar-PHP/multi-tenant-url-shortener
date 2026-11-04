<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->restrictOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->restrictOnDelete();
            $table->unique(['id', 'company_id']);
            $table->index(['company_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_id_company_id_unique');
            $table->dropIndex(['company_id', 'role_id']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn(['company_id', 'role_id']);
        });
    }
};
