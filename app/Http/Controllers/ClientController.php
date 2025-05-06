<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function __construct(private ClientService $service) {}

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->service->createClient($request->validated());

        return response()->json([
            'id' => $client->id,
            'message' => 'Cliente criado com sucesso',
        ], 201);
    }
}

