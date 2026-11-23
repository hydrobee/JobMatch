<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('target_roles')->nullable();
            $table->string('preferred_locations')->nullable();
            $table->boolean('willing_to_relocate')->default(false);
            $table->boolean('digest_enabled')->default(true);
            $table->unsignedTinyInteger('digest_min_score')->default(50);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'target_roles',
                'preferred_locations',
                'willing_to_relocate',
                'digest_enabled',
                'digest_min_score',
            ]);
        });
    }
};
