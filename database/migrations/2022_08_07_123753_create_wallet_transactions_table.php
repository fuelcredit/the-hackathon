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
            'wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('walletId');
                $table->bigInteger('userId')->nullable();
                $table->bigInteger('merchantId')->nullable();
                $table->decimal('amount', 10, 2)->default(0.00);
                $table->string('transactionType', 150);
                $table->string('description', 50);
                $table->string('ref', 20);
                $table->string('status', 20);
                $table->string('trans', 20);
                $table->dateTime('transactionDate');
                
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
        Schema::dropIfExists('wallet_transactions');
    }
};
