<?php
namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\SacolaRepositoryInterface;
use App\Models\Sacola as EloquentSacola; // Alias para o Model Eloquent
use App\Domain\Entities\Sacola as DomainSacola; // Alias para a Entidade de Domínio
use App\Models\Product;
use App\Models\Pedido;
use Illuminate\Support\Facades\Log;
use App\Adapters\Gateways\MercadoPagoClient;
use Illuminate\Database\Eloquent\ModelNotFoundException; // Para lançar exceção

class SacolaRepository implements SacolaRepositoryInterface
{
    /**
     * Adiciona um item à sacola do cliente.
     * Se a sacola não existir, uma nova é criada.
     * Se o produto já existir na sacola, a quantidade é atualizada.
     *
     * @param int $clienteId O ID do cliente.
     * @param int $produtoId O ID do produto.
     * @param int $quantidade A quantidade do produto.
     * @return void
     */
    public function adicionarItem(int $clienteId, int $produtoId, int $quantidade): void
    {
        // Verificar se o cliente já tem uma sacola existente
        $sacola = EloquentSacola::where('client_id', $clienteId)->first();

        // Se não existir uma sacola para o cliente, cria uma nova
        if (!$sacola) {
            $sacola = EloquentSacola::create([
                'client_id' => $clienteId,
                'status' => 'aberta', // Status inicial da sacola
                'total' => 0,
            ]);
        }

        // Encontrar o produto
        $produto = Product::findOrFail($produtoId);

        // Verificar se o produto já está na sacola
        $produtoSacola = $sacola->products()->where('product_id', $produtoId)->first();

        if ($produtoSacola) {
            // Se o produto já estiver na sacola, atualizar a quantidade
            $produtoSacola->pivot->quantidade += $quantidade;
            $produtoSacola->pivot->save();
        } else {
            // Caso o produto não esteja na sacola, adicionar o produto
            $sacola->products()->attach($produto->id, ['quantidade' => $quantidade]);
        }

        // Atualizar o total da sacola
        $sacola->total += $produto->preco * $quantidade;
        $sacola->save();
    }

    /**
     * Lista os produtos na sacola de um cliente.
     *
     * @param int $clientId O ID do cliente.
     * @return array Detalhes da sacola, incluindo produtos e valor total.
     */
    public function listarPorCliente(int $clientId): array
    {
        $sacola = EloquentSacola::where('client_id', $clientId)
            ->where('status', '!=', 'em_pagamento') // Não lista sacolas que estão em processo de pagamento
            ->first();

        if (!$sacola) {
            return [
                'client_id' => $clientId,
                'produtos' => [],
                'valor_total' => 0
            ];
        }

        return [
            'client_id' => $sacola->client_id,
            'produtos' => $sacola->products->map(function ($produto) {
                return [
                    'id_produto' => $produto->id,
                    'nome' => $produto->nome,
                    'quantidade' => $produto->pivot->quantidade,
                    'preco' => number_format($produto->preco, 2, ',', '.') // Formata o preço
                ];
            })->toArray(),
            'valor_total' => $sacola->total
        ];
    }

    /**
     * Remove um item da sacola do cliente.
     *
     * @param int $clientId O ID do cliente.
     * @param int $produtoId O ID do produto a ser removido.
     * @return void
     * @throws \Exception Se o produto não for encontrado na sacola.
     */
    public function removerItem(int $clientId, int $produtoId): void
    {
        $sacola = EloquentSacola::where('client_id', $clientId)->firstOrFail();

        $produto = $sacola->products()->where('product_id', $produtoId)->first();

        if ($produto) {
            $totalRemovido = $produto->preco * $produto->pivot->quantidade;
            $sacola->products()->detach($produtoId);
            $sacola->total -= $totalRemovido;
            if ($sacola->total < 0) { // Garante que o total não seja negativo
                $sacola->total = 0;
            }
            $sacola->save();
        }else{
            // Considerar usar uma exceção mais específica, ex: ProdutoNaoEncontradoNaSacolaException
            throw new \Exception("Produto não encontrado na sacola do cliente.");
        }
    }

    /**
     * Realiza o checkout da sacola do cliente, gerando um pedido e informações de pagamento.
     *
     * @param int $clientId O ID do cliente.
     * @return array Detalhes do pedido e pagamento.
     * @throws \Exception Se a sacola estiver vazia.
     * @throws ModelNotFoundException Se a sacola 'aberta' não for encontrada.
     */
    public function checkout(int $clientId): array
    {
        // Busca a sacola 'aberta' do cliente
        $sacola = EloquentSacola::where('client_id', $clientId)->where('status', 'aberta')->firstOrFail();
        
        // Verifica se a sacola tem itens antes de prosseguir para o checkout
        if ($sacola->products()->count() === 0) {
            throw new \Exception("A sacola está vazia. Adicione produtos antes de fazer o checkout.");
        }
        
        $mercadoPago = new MercadoPagoClient(); // Supondo que esta classe esteja configurada para lidar com pagamentos

        // Garante que $sacola->total está corretamente calculado e disponível
        $pagamento = $mercadoPago->criarPagamentoPix($sacola->total, "Pagamento Sacola #{$sacola->id}");

        $pedido = Pedido::create([
            'client_id' => $clientId,
            'sacola_id' => $sacola->id,
            'status' => 'aguardando_pagamento', // Status inicial do pedido
            'total' => $sacola->total,
            'mercado_pago_id' => $pagamento['id'], // Garante que a chave 'id' existe em $pagamento
        ]);

        // Atualiza o status da sacola para 'em_pagamento'
        $sacola->status = 'em_pagamento';
        $sacola->save();

        return [
            'pedido_id' => $pedido->id,
            // Garante que a chave 'qr_code_base64' existe em $pagamento
            'qr_code' => $pagamento['qr_code_base64'] ?? null, 
            'status' => $pedido->status,
            'valor' => $pedido->total,
        ];
    }

    /**
     * Encontra uma Sacola (entidade de domínio) pelo seu ID.
     *
     * @param int $sacolaId O ID da Sacola a ser encontrada.
     * @return DomainSacola A entidade de domínio Sacola encontrada.
     * @throws ModelNotFoundException Se nenhuma Sacola (Eloquent model) for encontrada para o ID fornecido.
     */
    public function findById(int $sacolaId): DomainSacola
    {
        // Busca o model Eloquent, garantindo que a relação 'products' seja carregada
        $eloquentSacola = EloquentSacola::with('products')->find($sacolaId);
   
        // Se o model Eloquent não for encontrado, lança uma exceção
        // Isso está alinhado com a interface que espera um DomainSacola não nulo
        if (!$eloquentSacola) {
            throw (new ModelNotFoundException)->setModel(EloquentSacola::class, $sacolaId);
        }

        // Mapeia os dados do model Eloquent para a entidade DomainSacola
        // Garante que os argumentos do construtor estejam na ordem correta:
        // id, client_id, status, produtos (array), total (float)
        $produtosArray = $eloquentSacola->products->map(function ($produto) {
            return [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'preco' => (float) $produto->preco, // Garante que preco seja float
                'quantidade' => (int) $produto->pivot->quantidade, // Garante que quantidade seja int
            ];
        })->toArray();

        return new DomainSacola(
            $eloquentSacola->id,
            $eloquentSacola->client_id,
            $eloquentSacola->status,
            $produtosArray, // Ordem corrigida: array de produtos
            (float) $eloquentSacola->total // Ordem corrigida: total, garante que seja float
        );
    }

    /**
     * Fecha a sacola de um cliente, atualizando seu status para 'fechada'.
     * Procura por sacolas com status 'aberta' ou 'em_pagamento'.
     *
     * @param int $clienteId O ID do cliente.
     * @return void
     * @throws ModelNotFoundException Se nenhuma sacola 'aberta' ou 'em_pagamento' for encontrada para o cliente (opcional, dependendo da lógica de negócios).
     */
    public function fecharSacola(int $clienteId): void
    {
        // Busca a sacola do cliente que está 'aberta' ou 'em_pagamento'
        // É importante definir quais status de sacola podem ser "fechados"
        $sacola = EloquentSacola::where('client_id', $clienteId)
                                ->whereIn('status', ['aberta', 'em_pagamento', 'pago']) // Permite fechar sacolas abertas ou em pagamento
                                ->first();

        if (!$sacola) {
            // Se nenhuma sacola nessas condições for encontrada, loga um aviso.
            // Dependendo da regra de negócio, pode ser apropriado lançar uma exceção
            // ou simplesmente retornar se não há sacola ativa para fechar.
            Log::warning("Tentativa de fechar sacola para o cliente ID: {$clienteId}, mas nenhuma sacola 'aberta' ou 'em_pagamento' foi encontrada.");
            // Exemplo de como lançar exceção, se necessário:
            // throw (new ModelNotFoundException("Nenhuma sacola ativa encontrada para o cliente ID: {$clienteId}"))->setModel(EloquentSacola::class);
            return; 
        }

        // Define o status da sacola como 'fechada'
        $sacola->status = 'fechada';
        $sacola->save();

        Log::info("Sacola ID: {$sacola->id} do cliente ID: {$clienteId} foi fechada com sucesso.");
    }
}
