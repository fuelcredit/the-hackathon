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
        Schema::create(
            'merchants', function (Blueprint $table) {
                $table->id();
                $table->string('merchantName', 100);
                $table->string('customer_tier')->nullable();
                $table->string('reference')->nullable();
                $table->string('account_no')->nullable();
                $table->string('director_bvn');
                $table->string('tin', 100)->nullable();
                $table->string('user_name', 100)->nullable();
                $table->string('city', 100);
                $table->string('state', 100);
                $table->string('wallet_category', 100)->nullable();
                $table->string('password', 100);
                $table->string('email', 150)->unique();
                $table->string('phoneNumber', 15);
                $table->text('bankName')->nullable();
                $table->string('address', 150);
                $table->string('status')->nullable();
                $table->timestamps();

            }
        );

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('merchants');
    }
};
