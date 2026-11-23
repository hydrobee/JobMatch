<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_listing_id')->constrained()->cascadeOnDelete();
            $table->string('stage')->default('saved');
            $table->date('applied_at')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('resume_version')->nullable();
            $table->text('notes')->nullable();
            $table->date('follow_up_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'job_listing_id']);
            $table->index(['user_id', 'stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
