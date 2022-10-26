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
        Schema::create('group', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->decimal('goal', 10, 2, true)->default(0);
            $table->decimal('amount', 10, 2, true)->default(0);
            $table->bigInteger('user_account_id')->unsigned();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('user_account_id')->references('id')->on('user_accounts');
        });

        Schema::create('user_group', function (Blueprint $table) {
            $table->bigInteger('user_account_id')->unsigned();
            $table->bigInteger('group_id')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group');
        Schema::dropIfExists('user_group');
    }
};
