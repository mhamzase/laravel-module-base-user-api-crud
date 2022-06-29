<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUsernameLangToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 255)->unique()->nullable(false)->after('id');
            $table->string('lang', 2)->nullable(true)->default(null)->after('remember_token');

        });

        /* drop name column as its not required */
        if (Schema::hasColumn('users', 'name'))
        {
            Schema::table('users', function (Blueprint $table)
            {
                $table->dropColumn('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('lang');
            $table->dropColumn('username');
        });
    }
}
