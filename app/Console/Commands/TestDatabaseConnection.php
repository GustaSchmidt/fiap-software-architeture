<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

class TestDatabaseConnection extends Command
{
    protected $signature = 'db:test';
    protected $description = 'Testa a conexão com o banco de dados';

    public function handle()
    {
        // Remove
        $this->info('--- LISTANDO TODAS AS ENVS NO CONTAINER ---');

        // Pega todas as variáveis de ambiente
        $envs = getenv();

        foreach ($envs as $key => $value) {
            $this->line("{$key}={$value}");
        }

        $this->info('-------------------------------------------');

        $this->info('🔍 Testando conexão com o banco de dados...');
        $this->info('--- Variáveis de Ambiente Identificadas ---');
        $this->line("DB_CONNECTION: " . env('DB_CONNECTION'));
        $this->line("DB_HOST: " . env('DB_HOST'));
        $this->line("DB_PORT: " . env('DB_PORT'));
        $this->line("DB_DATABASE: " . env('DB_DATABASE'));
        $this->line("DB_USERNAME: " . env('DB_USERNAME'));
        $this->info('------------------------------------------');

        $this->info('🔍 Testando conexão...');

        try {
            DB::connection()->getPdo();
            $database = DB::connection()->getDatabaseName();
            $this->info("✅ Conexão bem-sucedida com o banco de dados: {$database}");
        } catch (Exception $e) {
            $this->error("❌ Falha na conexão com o banco de dados.");
            $this->error("Erro: " . $e->getMessage());
            return 1; // retorna código de erro
        }

        return 0; // sucesso
    }
}
