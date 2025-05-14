<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();

            // Relacionamentos
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('sacola_id');

            // Campos principais
            $table->string('status')->default('aguardando_pagamento'); // Ex: aguardando_pagamento, pago, cancelado
            $table->decimal('total', 10, 2);
            $table->string('mercado_pago_id')->nullable(); // ID do pagamento (simulado)

            $table->timestamps();

            // FKs
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('sacola_id')->references('id')->on('sacolas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
