<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projectcategories', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('parameter_types');
            $table->text('image');
            $table->tinyInteger('status')->comment('0:DeActive 1:Active')->default(0);
            $table->tinyInteger('sequence')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projectcategories');
    }
};
