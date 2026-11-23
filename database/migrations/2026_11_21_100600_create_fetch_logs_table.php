<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fetch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_source_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->unsignedInteger('jobs_found')->default(0);
            $table->unsignedInteger('jobs_created')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fetch_logs');
    }
};
