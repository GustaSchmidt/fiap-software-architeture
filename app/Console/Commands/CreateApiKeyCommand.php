<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\ApiKey;
use Illuminate\Support\Facades\Validator; // Para validação opcional

class CreateApiKeyCommand extends Command
{
    // ... (signature e description como antes) ...
    protected $signature = 'apikey:create 
                            {name : O nome legível para a API key (ex: "Integração Serviço X")} 
                            {--r|role= : A role associada à API key (opcional)} 
                            {--c|client-id= : O ID (inteiro) do cliente/loja associado à role (opcional)} 
                            {--inactive : Cria a API key como inativa (opcional)}';

    protected $description = 'Cria uma nova API key no sistema';


    public function handle(): int
    {
        $name = $this->argument('name');
        $role = $this->option('role');
        $clientIdOption = $this->option('client-id'); // Pega a opção como string
        $isActive = !$this->option('inactive');

        $clientId = null;
        if ($clientIdOption !== null) {
            // Valida se é numérico antes de converter para inteiro
            if (!is_numeric($clientIdOption)) {
                $this->error('O client-id fornecido não é um número válido.');
                return Command::FAILURE;
            }
            $clientId = (int) $clientIdOption; // Converte para inteiro
        }

        do {
            $generatedKey = Str::random(60);
        } while (ApiKey::where('key', $generatedKey)->exists());

        try {
            $apiKey = ApiKey::create([
                'name' => $name,
                'key' => $generatedKey,
                'role' => $role,
                'role_id_loja_cliente' => $clientId, // Agora $clientId é inteiro ou null
                'is_active' => $isActive,
            ]);

            $this->info('API Key criada com sucesso!');
            $this->line('--------------------------------------------------');
            $this->line("Nome       : <options=bold>{$apiKey->name}</>");
            if ($apiKey->role) {
                $this->line("Role       : <options=bold>{$apiKey->role}</>");
            }
            // Verifica se role_id_loja_cliente não é null antes de exibir
            if ($apiKey->role_id_loja_cliente !== null) {
                $this->line("Client/Loja ID : <options=bold>{$apiKey->role_id_loja_cliente}</>");
            }
            $this->line("Status     : <options=bold>" . ($apiKey->is_active ? 'Ativa' : 'Inativa') . "</>");
            $this->line("API Key    : <options=bold;fg=yellow>{$apiKey->key}</>");
            $this->line('--------------------------------------------------');
            $this->comment('Guarde esta chave em um local seguro. Ela não será exibida novamente.');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Erro ao criar a API key: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}