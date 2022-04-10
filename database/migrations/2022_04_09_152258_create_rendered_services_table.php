<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRenderedServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rendered_services', function (Blueprint $table) {
            $table->id();
             $table->integer('service_id');
            $table->integer('service_group_id');
            $table->integer('request_id');
            $table->integer('price');
            $table->integer('quantity');
            $table->integer('total');
            $table->integer('status')->default(0)->comment('0 - New, 1 - In-Progress, 2 - Pending, 3 - Completed');
            $table->integer('created_by_id');
            $table->integer('updated_by_id');
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
        Schema::dropIfExists('rendered_services');
    }
}
