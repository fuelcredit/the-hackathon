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
                $table->unsignedBigInteger('walletId');
                $table->unsignedBigInteger('userId')->nullable();
                $table->decimal('amount', 10, 2)->default(0.00);
                $table->string('transactionType', 150);
                $table->string('description', 50)->nullable();
                $table->string('ref', 20)->nullable();
                $table->string('status', 20)->nullable();
                $table->string('trans', 20)->nullable();
                $table->dateTime('transactionDate')->nullable();
                
                $table->index(['walletId', 'userId']);
                $table->foreign('walletId')->references('id')
                    ->on('wallets')->cascadeOnDelete();
                $table->foreign('userId')->references('id')
                    ->on('merchants')->cascadeOnDelete();
                $table->foreign('userId')->references('id')
                    ->on('admin_users')->cascadeOnDelete();
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
