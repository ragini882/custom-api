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
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('from_account_id')->unsigned();
            $table->bigInteger('to_account_id')->unsigned();
            $table->enum('status', ['PENDING', 'COMPLETED', 'CANCELLED'])->default('PENDING');
            $table->string('note');
            $table->decimal('amount', 10, 2, true)->default(0);
            $table->timestamps();
            $table->foreign('from_account_id')->references('id')->on('user_accounts');
            $table->foreign('to_account_id')->references('id')->on('user_accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_requests');
    }
};
