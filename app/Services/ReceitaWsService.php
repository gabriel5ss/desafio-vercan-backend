<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Throwable;

class ReceitaWsService
{
    /** @return array<string, mixed> */
    public function lookup(string $cnpj): array
    {
        $cnpj = preg_replace('/\D/', '', $cnpj) ?? '';

        $cacheKey = "receitaws:{$cnpj}";
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $response = Http::acceptJson()->connectTimeout(3)->timeout(10)
                ->get(config('services.receitaws_url')."{$cnpj}")
                ->throw();

            $data = $response->json();
            if (! $response->successful()) {
                throw new RuntimeException('A consulta de CNPJ não retornou sucesso.');
            }

            Cache::put($cacheKey, $data, now()->addMinutes(10));

            return $data;
        } catch (Throwable) {
            throw new ServiceUnavailableHttpException(null, 'O serviço de consulta de CNPJ está indisponível.');
        }
    }
}
