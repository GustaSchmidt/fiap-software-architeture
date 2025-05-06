<?php

use Illuminate\Support\Facades\Route;

# Docs
Route::get('/', function () {
    return view('swagger');
});

# Clients
Route::get('/client/{id}', function ($id) {
    return "Buscar o cliente com o id (validamos o CPF no payload):  " . $id;
});

Route::post('/client/create', function ($id) {
    return "Criar um novo cliente no app";
});

Route::post('/client/update', function ($id) {
    return "Atualizar o cliente";
});

Route::post('/client/delete', function ($id) {
    return "Deletar o cliente";
});

# Produtos
Route::post('/product/create', function () {
    return "criando produtos por loja requer API key de loja";
});

Route::post('/product/update', function () {
    return "Atualiza produtos por loja requer API key de loja";
});

Route::post('/product/delete', function () {
    return "deleta produtos por loja requer API key de loja";
});

Route::post('/product/search', function () {
    return "busca produtor em todo o APP por categoria";
});

