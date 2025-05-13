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
    public function up(): void
    {
        Schema::create('product_sacola', function (Blueprint $table) {
            $table->id();

            // Cria 'product_id' como unsignedBigInteger + foreign key para 'products'
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            // Cria 'sacola_id' como unsignedBigInteger + foreign key para 'sacolas'
            $table->foreignId('sacola_id')->constrained('sacolas')->onDelete('cascade');

            // Campo de quantidade
            $table->integer('quantidade')->default(1);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('produto_sacola');
    }
};