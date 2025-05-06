<?php

use Illuminate\Support\Facades\Route;

# Docs
Route::get('/', function () {
    return view('swagger');
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

