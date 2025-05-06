# Banco de Dados: Migrations, Seeders e Factories

Este documento descreve as práticas recomendadas para gerenciar o esquema do banco de dados e dados de exemplo em projetos Laravel com arquitetura hexagonal.

---

## 🛠️ Migrations

As migrations são scripts versionados que descrevem alterações no banco de dados, como criação de tabelas e adição de colunas.

### 📂 Localização
As migrations ficam em:
database/
    └── migrations/


### 📐 Boas práticas
- Mantenha a migration **simples** e focada na estrutura da tabela.
- **Não insira lógica de negócio** ou chamadas a serviços dentro da migration.
- Nomeie as migrations de forma descritiva: `create_clients_table`, `add_status_to_clients_table`, etc.

### ✅ Exemplo

```php
// database/migrations/2024_01_01_000000_create_clients_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('clients');
    }
};
```

## 🌱 Seeders
Seeders são usados para popular o banco de dados com dados iniciais ou de exemplo. Úteis em ambientes de desenvolvimento e testes.

### 📂 Localização
database/
└── seeders/
    └── ClientSeeder.php

### ✅ Exemplo
```php
// database/seeders/ClientSeeder.php

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder {
    public function run(): void {
        Client::factory()->count(10)->create();
    }
}
```

### 🧩 Registrando o seeder
Adicione no DatabaseSeeder.php:

``` php
public function run(): void {
    $this->call([
        ClientSeeder::class,
    ]);
}
```

## 🏭 Factories
Factories permitem gerar instâncias de modelos com dados aleatórios para testes ou seeders.

### 📂 Localização
database/
└── factories/
    └── ClientFactory.php

### ✅ Exemplo
```php
// database/factories/ClientFactory.php

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory {
    protected $model = Client::class;

    public function definition(): array {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
```

## ⚙️ Executando

Acessar o container da aplicação (ver README.md)
```bash
docker-compose exec app bash
```

Criar estrutura do banco de dados
```bash
php artisan migrate
```

Popular o banco com dados
```bash
php artisan db:seed
```

Recriar banco com dados
```bash
php artisan migrate:fresh --seed
```

# Redis: Uso e Boas Práticas no Projeto
Nesta primeira faze nao fazemos o uso de Redis e nenhum banco de dados em memoria.