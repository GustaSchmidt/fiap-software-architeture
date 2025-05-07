<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Informações básicas
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->decimal('preco', 10, 2);
            $table->unsignedBigInteger('loja_id');

            // Adicionando a coluna 'categoria'
            $table->string('categoria')->nullable(); // Ex: "Confeitaria"

            // Informações alimentícias
            $table->text('ingredientes')->nullable();
            $table->json('informacoes_nutricionais')->nullable(); // Ex: {"calorias": 250, "proteinas": 10}
            $table->string('porcao')->nullable(); // Ex: "100g", "1 unidade", etc.
            $table->string('alergenicos')->nullable(); // Ex: "Contém glúten e ovos"

            $table->timestamps();

            // Foreign key loja_id -> lojas.id (se a tabela lojas existir)
            $table->foreign('loja_id')->references('id')->on('lojas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
