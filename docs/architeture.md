# Estrutura do Projeto Laravel com Arquitetura Hexagonal

Este projeto segue a arquitetura hexagonal (Ports and Adapters), com o objetivo de separar claramente as regras de negócio da infraestrutura, facilitando testes, manutenção e evolução.

---

## 📁 Estrutura de Pastas

Abaixo está a descrição das principais pastas utilizadas no projeto, organizadas por responsabilidade.

### `app/Domain/`
Contém a **lógica de negócio central** da aplicação.

- `Entities/` – Classes que representam os modelos do domínio (ex: `Client.php`).
- `Repositories/` – Interfaces que definem os contratos para persistência (ex: `ClientRepositoryInterface.php`).

### `app/Adapters/`
Implementações concretas das portas de entrada/saída (adapters externos).

- `Repositories/` – Repositórios que implementam as interfaces definidas no domínio, utilizando Eloquent ou outra tecnologia (ex: `EloquentClientRepository.php`).

### `app/Services/`
Contém os **casos de uso da aplicação**. Esses serviços orquestram o fluxo entre o domínio e os adaptadores, sem depender de frameworks.

- Ex: `ClientService.php` trata a lógica para criação e listagem de clientes.

### `app/Http/`
Camada responsável pela **entrada via HTTP**.

- `Controllers/` – Recebem as requisições HTTP e delegam a lógica para os serviços (ex: `ClientController.php`).
- `Requests/` – Validam os dados recebidos via HTTP (ex: `StoreClientRequest.php`).

### `routes/`
Define os **pontos de entrada da aplicação via HTTP**.

- `api.php` – Arquivo onde são registradas as rotas da API REST (ex: POST `/clients`, GET `/clients`).

---

## 🔄 Fluxo da Requisição

1. A requisição chega via rota definida em `routes/api.php`.
2. A rota aciona um controlador localizado em `Http/Controllers/`.
3. O controlador chama um serviço em `Services/`, passando os dados validados da `Request/`.
4. O serviço executa a lógica de negócio usando uma entidade e um repositório do `Domain/`.
5. O repositório é uma interface, implementada por um adapter em `Adapters/Repositories/`.

---

## ✅ Boas Práticas

- **Domínio deve ser puro**: sem dependências externas (Laravel, Eloquent, etc).
- **Use interfaces** no domínio e implemente-as nos adaptadores.
- **Validações** devem ficar nas classes de `Http/Requests/`.
- **Serviços** não devem conhecer HTTP ou detalhes de implementação.
- **Controladores** devem ser simples: apenas receber, validar e repassar.

---

## 🔌 Bind de Repositórios

O binding entre interfaces do domínio e suas implementações deve ser feito no `AppServiceProvider`:

```php
use App\Domain\Repositories\ClientRepositoryInterface;
use App\Adapters\Repositories\EloquentClientRepository;

public function register()
{
    $this->app->bind(ClientRepositoryInterface::class, EloquentClientRepository::class);
}
```