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
                $table->string('location', 30);
                $table->string('email', 150)->unique()->nullable();
                $table->string('phoneNumber', 15)->unique();
                $table->string('password', 15);
                $table->text('bankName')->nullable();
                $table->text('accountName')->nullable();
                $table->text('accountNumber')->nullable();
                $table->string('address', 150);
                $table->unsignedBigInteger('status')
                    ->default(Merchant::ACCOUNT_ACTIVE);
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
