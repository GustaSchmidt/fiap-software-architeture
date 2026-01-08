<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // nome identificador da chave
            $table->string('key')->unique(); // chave de autenticação
            $table->string('role'); // ex: "admin", "loja", "cliente"
            $table->unsignedBigInteger('role_id_loja_cliente')->nullable(); // ID da loja ou cliente, se aplicável
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};

