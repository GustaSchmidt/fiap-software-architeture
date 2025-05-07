<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

# Docs
Route::get('/', function () {
    return view('swagger');
});

Route::middleware([])->get('/swagger', function () {
    $path = public_path('swagger.json');

    if (!file_exists($path)) {
        abort(404);
    }

    return Response::file($path, [
        'Content-Type' => 'application/json',
        'Content-Disposition' => 'inline; filename="swagger.json"',
    ]);
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

