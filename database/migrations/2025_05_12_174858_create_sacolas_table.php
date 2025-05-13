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
            $table->unsignedBigInteger('client_id')->nullable(false);
            $table->string('status')->default('aberta'); // Status da sacola, por exemplo: aberta ou finalizada
            $table->float('total')->default(0);
            $table->timestamps();

            // Definindo a chave estrangeira para a tabela clients
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sacolas');
    }
};
