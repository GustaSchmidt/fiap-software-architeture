<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Rota pública simples para verificar o status da API
Route::get('/status', function () {
    return response()->json(['status' => 'API Funcionando!', 'timestamp' => now()]);
});

# Clients
Route::get('/client/{id}', function ($id) {
    return "Buscar o cliente com o id (validamos o CPF no payload):  " . $id;
});

Route::post('/client/create', function () {
    return "Criar um novo cliente no app";
});

Route::post('/client/update', function () {
    return "Atualizar o cliente";
});

Route::post('/client/delete', function () {
    return "Deletar o cliente";
});


# Falback se tudo der errado cai aqui
Route::fallback(function(){
    return response()->json([
        'message' => 'Endpoint não encontrado. Verifique a URL e o método HTTP.'], 404);
});