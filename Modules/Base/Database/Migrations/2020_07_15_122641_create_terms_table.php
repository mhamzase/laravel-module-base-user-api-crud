<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terms', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('slug');
            $table->nestedSet();
            $table->json('extra')->nullable();
            $table->string('module_name');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::dropIfExists('terms');
        Schema::table('terms', function (Blueprint $table) {
            $table->dropNestedSet();
        });
    }
}
