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
                $table->string('customer_tier');
                $table->string('reference');
                $table->string('account_no');
                $table->string('director_bvn');
                $table->string('tin', 100);
                $table->string('user_name', 100);
                $table->string('city', 100);
                $table->string('state', 100);
                $table->string('wallet_category', 100);
                $table->string('password', 100);
                $table->string('location', 30);
                $table->string('email', 150)->unique()->nullable();
                $table->string('phoneNumber', 15)->unique();
                $table->text('bankName')->nullable();
                $table->string('address', 150);
                $table->unsignedBigInteger('status');
                $table->timestamps();

                // $table->foreign('addedBy')->references('id')->on('admin_users');
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
