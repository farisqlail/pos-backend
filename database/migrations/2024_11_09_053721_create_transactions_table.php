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
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
            $table->foreignId('id_menu')->constrained('menus')->onDelete('cascade');
            $table->foreignId('id_promo')->nullable()->constrained('promos')->onDelete('set null');
            $table->string('no_nota')->unique();
            $table->integer('quantity');
            $table->decimal('grand_total', 10, 2);
            $table->string("payment");
            $table->string('type_transaction');
            $table->enum('status_transaction', ['pending', 'completed', 'proses'])->default('pending');
            $table->enum('status_payment', ['unpaid', 'paid'])->default('unpaid');
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->integer('pay_amount');
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
