<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LojaController;
use App\Http\Controllers\SacolaController;
use App\Http\Controllers\PedidoController;

// Rota pública simples para verificar o status da API
Route::get('/status', function () {
    return response()->json(['status' => 'API Funcionando!', 'timestamp' => now()]);
});

# Clients
Route::get('/client/{id}', [ClientController::class, 'show']);

Route::post('/client/create', [ClientController::class, 'store']);

Route::post('/client/search_cpf', [ClientController::class, 'searchByCpf']);

Route::post('/client/update', [ClientController::class, 'update']);

Route::delete('/client/delete', [ClientController::class, 'delete']);

# Loja
Route::post('/loja/create', [LojaController::class, 'store']);

Route::post('/loja/search', [LojaController::class, 'search']);

# Products
Route::post('/product/create', [ProductController::class, 'create']);

Route::get('/product/{id}', [ProductController::class, 'show']);

Route::post('/product/category_list', [ProductController::class, 'listByCategory']);

Route::post('/product/update', [ProductController::class, 'update']);

Route::delete('/product/delete', [ProductController::class, 'delete']);

# Sacola
Route::post('/sacola/add', [SacolaController::class, 'adicionarItem']);

Route::get('/sacola/client/{clientId}', [SacolaController::class, 'listarPorCliente']);

Route::post('/sacola/remove', [SacolaController::class, 'remove']);

Route::post('/sacola/checkout', [\App\Http\Controllers\SacolaController::class, 'checkout']);

Route::post('/pedido/list', [PedidoController::class, 'list']);

Route::get('/pedido/status/{id}', [PedidoController::class, 'status']);
