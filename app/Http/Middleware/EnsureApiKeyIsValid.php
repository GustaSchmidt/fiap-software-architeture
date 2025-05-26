<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiKey; // Certifique-se de que este namespace está correto para o seu modelo ApiKey

class EnsureApiKeyIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function __invoke(Request $request, Closure $next): Response
    {
        // Obtém a chave do header (ex: Authorization: Bearer API_KEY)
        $apiKey = $request->bearerToken();

        if (!$apiKey) {
            return response()->json(['error' => 'API key ausente'], 401);
        }

        $key = ApiKey::where('key', $apiKey)->where('is_active', true)->first();

        if (!$key) {
            return response()->json(['error' => 'API key inválida ou inativa'], 403);
        }

        $request->merge([
            'api_key_info' => $key->only(['id', 'name', 'role', 'role_id_loja_cliente']),
        ]);

        return $next($request);
    }
}