<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sacola;
use App\Models\Client;
use App\Models\Product;

class SacolaSeeder extends Seeder
{
    public function run(): void
    {
        // Busca todos os clientes
        $clientes = Client::all();
        $produto = Product::first(); // Usa o primeiro produto como exemplo

        foreach ($clientes as $cliente) {
            // Cria uma sacola para o cliente se ele não tiver
            $sacola = Sacola::create([
                'client_id' => $cliente->id,
                'status' => 'aberta',
                'total' => 0,
            ]);

            if ($produto) {
                // Adiciona o produto à sacola com quantidade 1
                $sacola->products()->attach($produto->id, ['quantidade' => 1]);

                // Atualiza o total
                $sacola->total = $produto->preco;
                $sacola->save();
            }
        }
    }
}
