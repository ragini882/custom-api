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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('code')->nullable();
            $table->string('phone', 20)->nullable();
            $table->unsignedInteger('otp')->nullable();
            $table->boolean('is_phone_verified')->default(0); /* 1=verified, 0=unverified */
            $table->boolean('is_account_verified')->default(0); /* 1=verified, 0=unverified */
            $table->enum('account_type', ['PERSONAL', 'BUSINESS']);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('user_accounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->uuid('customer_uuid');
            $table->string('legal_first_name');
            $table->string('legal_last_name');
            $table->date('dob');
            $table->string('ssn');
            $table->string('street_address');
            $table->string('address_type')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('user_banks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_account_id')->unsigned();
            $table->uuid('funding_source_uuid');
            $table->string('routing_number')->nullable();
            $table->string('account_number')->nullable();
            $table->string('bank_account_type')->nullable();
            $table->string('plaid_token')->nullable();
            $table->timestamps();
            $table->foreign('user_account_id')->references('id')->on('user_accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('user_accounts');
        Schema::dropIfExists('user_banks');
    }
};
