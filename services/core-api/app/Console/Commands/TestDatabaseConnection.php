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
        $this->info('🔍 Testando conexão...');

        try {
            DB::connection()->getPdo();
            $database = DB::connection()->getDatabaseName();
            $this->info("✅ Conexão bem-sucedida com o banco de dados: {$database}");
        } catch (Exception $e) {
            $this->error("❌ Falha na conexão com o banco de dados.");
            $this->error("Erro: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
