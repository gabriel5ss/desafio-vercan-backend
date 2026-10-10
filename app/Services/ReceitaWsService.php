<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class ReceitaWsService
{
    /** @return array<string, mixed>|null */
    public function lookup(string $cnpj): ?array
    {
        $cnpj = preg_replace('/\D/', '', $cnpj) ?? '';

        try {
            return Cache::remember("receitaws:{$cnpj}", now()->addMinutes(6), function () use ($cnpj): ?array {
                $response = Http::acceptJson()->connectTimeout(3)->timeout(8)
                    ->get("https://receitaws.com.br/v1/cnpj/{$cnpj}");

                if (! $response->successful()) {
                    throw new ServiceUnavailableHttpException(null, 'O serviço de consulta de CNPJ está indisponível.');
                }

                $data = $response->json();
                if (! is_array($data)) {
                    throw new ServiceUnavailableHttpException(null, 'O serviço de consulta de CNPJ retornou uma resposta inválida.');
                }

                return ($data['status'] ?? null) === 'OK' ? $data : null;
            });
        } catch (ConnectionException) {
            throw new ServiceUnavailableHttpException(null, 'O serviço de consulta de CNPJ está indisponível.');
        }
    }
}
