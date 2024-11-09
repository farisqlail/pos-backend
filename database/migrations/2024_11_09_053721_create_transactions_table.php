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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_menu')->constrained('menus')->onDelete('cascade');
            $table->foreignId('id_promo')->nullable()->constrained('promos')->onDelete('set null');
            $table->integer('quantity');
            $table->decimal('grand_total', 10, 2);
            $table->enum('status_transaction', ['pending', 'completed', 'canceled'])->default('pending');
            $table->enum('status_payment', ['unpaid', 'paid'])->default('unpaid');
            $table->decimal('discount_amount', 10, 2)->nullable();
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
        Schema::dropIfExists('transactions');
    }
};
