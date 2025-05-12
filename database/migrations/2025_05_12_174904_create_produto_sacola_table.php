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
        Schema::create('produto_sacola', function (Blueprint $table) {
            $table->id(); // Chave primária da tabela de relacionamento
            $table->unsignedBigInteger('sacola_id'); // Chave estrangeira para a tabela sacolas
            $table->unsignedBigInteger('produto_id'); // Chave estrangeira para a tabela products
            $table->integer('quantidade')->default(1); // Quantidade do produto na sacola
            $table->timestamps(); // Timestamps para created_at e updated_at

            // Definindo as chaves estrangeiras
            $table->foreign('sacola_id')->references('id')->on('sacolas')->onDelete('cascade');
            $table->foreign('produto_id')->references('id')->on('products')->onDelete('cascade');

            // Adicionando índices para as chaves estrangeiras
            $table->index('sacola_id');
            $table->index('produto_id');
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