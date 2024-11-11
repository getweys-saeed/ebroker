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
        Schema::create('property_boosts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');    // Foreign key to properties table
            $table->unsignedBigInteger('customer_id');    // Foreign key to users table
            $table->datetime('start_date'); // Start date and time of advertisement
            $table->datetime('end_date');   // End date and time of advertisement            
            $table->decimal('price', 10, 2);   
            $table->integer('payment_getwey')->comment("easy-paisa/Jazz-cash/Bank-Account");    
            $table->integer('order_id');  
            $table->string('payment_screenshot')->nullable();     
            $table->string('payment_detail')->nullable();     
            $table->boolean('is_payed')->default(false); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_boosts');
    }
};
