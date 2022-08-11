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
                $table->bigInteger('userId');
                $table->bigInteger('merchantId');
                $table->decimal('purchaseAmount', 10, 2)->default(0.00);
                $table->string('status');
                $table->string('trans_ref');
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
        Schema::dropIfExists('fuel_purchases');
    }
};
