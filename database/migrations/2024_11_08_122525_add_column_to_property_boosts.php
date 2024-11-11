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
        Schema::table('property_boosts', function (Blueprint $table) {
            $table->boolean('is_active')->default(false); 
            $table->integer('impressions')->default(0); 
            $table->integer('views')->default(0); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_boosts', function (Blueprint $table) {
            //
        });
    }
};
