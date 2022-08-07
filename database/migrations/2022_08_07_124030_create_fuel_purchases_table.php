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
            'fuel_purchases', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('userId');
                $table->unsignedBigInteger('merchantId');
                $table->decimal('purchaseAmount', 10, 2)->default(0.00);
                $table->string('paymentOTP', 10)->nullable();
                $table->string('dispenseOTP', 10)->nullable();
                $table->unsignedInteger('paymentStatus')
                    ->default(FuelPurchase::STATUS_PENDING);
                $table->unsignedInteger('status')
                    ->default(FuelPurchase::STATUS_PENDING);
                $table->dateTime('dateCreated')->nullable();
                $table->string('trans', 20)->nullable();

                // $table->index(['userId', 'attendantId', 'merchantId']);
                // $table->foreign('userId')->references('id')
                //     ->on('users')->cascadeOnDelete();
                // $table->foreign('attendantId')->references('id')
                //     ->on('admin_users')->cascadeOnDelete();
                // $table->foreign('merchantId')->references('id')
                //     ->on('merchants')->cascadeOnDelete();
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
        Schema::dropIfExists('fuel_purchases');
    }
};
