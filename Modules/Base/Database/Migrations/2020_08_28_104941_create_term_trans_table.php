<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTermTransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('term_trans', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('title');
            $table->string('lang');
            $table->bigInteger('term_id')->unsigned();

            $table->foreign('term_id')
                  ->references('id')
                  ->on('terms')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('term_trans');
    }
}
