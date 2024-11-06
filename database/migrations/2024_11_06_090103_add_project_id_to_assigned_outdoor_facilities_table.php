<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assigned_outdoor_facilities', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->after('facility_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assigned_outdoor_facilities', function (Blueprint $table) {
            $table->dropColumn('project_id');
        });
    }
};
