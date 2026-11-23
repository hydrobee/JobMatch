<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('company');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('salary')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->string('fingerprint', 40);
            $table->json('skills')->nullable();
            $table->unsignedTinyInteger('match_score')->nullable();
            $table->boolean('is_expired')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'fingerprint']);
            $table->index(['user_id', 'posted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
