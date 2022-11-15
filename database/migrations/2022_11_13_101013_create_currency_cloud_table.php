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
        Schema::table('user_accounts', function (Blueprint $table) {
            $table->uuid('cc_account_uuid')->after('balance_amount');
            $table->uuid('cc_contact_uuid')->after('cc_account_uuid');
            $table->uuid('sub_account_uuid')->after('cc_contact_uuid');
        });

        Schema::create('beneficiary', function (Blueprint $table) {
            $table->id();
            $table->uuid('cc_contact_uuid');
            $table->uuid('beneficiary_uuid');
            $table->string('name');
            $table->string('bank_account_holder_name');
            $table->string('bank_country');
            $table->string('currency');
            $table->string('account_number');
            $table->string('routing_code_type_1');
            $table->string('routing_code_value_1');
            $table->string('iban');
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
        Schema::dropIfExists('beneficiary');
    }
};
