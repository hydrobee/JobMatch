<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // job_listing_id null = the user's master resume; otherwise a version tailored to that job.
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_listing_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->json('ai_notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'job_listing_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
