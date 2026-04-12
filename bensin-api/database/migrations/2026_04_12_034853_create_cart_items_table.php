<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('cart_items', function (Blueprint $table) {
        $table->id();

        // 🔥 SAMAKAN TIPE DENGAN carts.id
        $table->unsignedBigInteger('cart_id');

        // 🔥 SAMAKAN TIPE DENGAN warungs.id (biasanya INT)
        $table->integer('warung_id');

        $table->string('jenis_bbm');
        $table->integer('qty');
        $table->integer('harga');

        $table->timestamps();

        $table->foreign('cart_id')
              ->references('id')
              ->on('carts')
              ->onDelete('cascade');

        $table->foreign('warung_id')
              ->references('id')
              ->on('warungs')
              ->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
