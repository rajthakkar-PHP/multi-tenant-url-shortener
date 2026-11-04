<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('short_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id');
            $table->text('original_url');
            $table->string('short_code', 16)->unique();
            $table->timestamps();

            $table->foreign(['user_id', 'company_id'])
                ->references(['id', 'company_id'])
                ->on('users')
                ->restrictOnDelete();
            $table->index(['company_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_urls');
    }
};
