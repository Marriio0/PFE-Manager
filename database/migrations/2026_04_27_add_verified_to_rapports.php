<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rapports', function (Blueprint $table) {
            $table->boolean('verified_by_encadrant')->default(false)->after('statut');
            $table->timestamp('verified_at')->nullable()->after('verified_by_encadrant');
        });
    }

    public function down(): void
    {
        Schema::table('rapports', function (Blueprint $table) {
            $table->dropColumn(['verified_by_encadrant', 'verified_at']);
        });
    }
};