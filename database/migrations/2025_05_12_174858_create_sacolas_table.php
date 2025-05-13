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
        Schema::create('sacolas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id'); // Chave estrangeira para a tabela clients
            $table->string('status')->default('aberta'); // Status da sacola, por exemplo: aberta ou finalizada
            $table->decimal('total', 10, 2)->default(0); // Total da sacola
            $table->timestamps();

            // Definindo a chave estrangeira para a tabela clients
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sacolas');
    }
};
