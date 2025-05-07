<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;


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

# Falback se tudo der errado cai aqui
Route::fallback(function(){
    return response()->json([
        'message' => 'Endpoint não encontrado. Verifique a URL e o método HTTP.'], 404);
});