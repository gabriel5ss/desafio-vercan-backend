<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\ReceitaWsService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Tests\TestCase;

final class ReceitaWsServiceTest extends TestCase
{
    private ReceitaWsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.receitaws_url' => 'https://receitaws.test/v1/cnpj/',
            'cache.default' => 'array',
        ]);

        $this->service = new ReceitaWsService;
    }

    public function test_lookup(): void
    {
        $company = ['status' => 'OK', 'nome' => 'Empresa Exemplo'];
        Http::fake([
            config('services.receitaws_url').'04252011000110' => Http::response($company),
        ]);

        $result = $this->service->lookup('04252011000110');

        $this->assertSame($company, $result);
        Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
            && $request->url() === config('services.receitaws_url').'04252011000110');
    }

    public function test_throws_service_unavailable_for_http_error(): void
    {
        Http::fake([
            config('services.receitaws_url').'04252011000110' => Http::response([], 500),
        ]);

        $this->expectException(ServiceUnavailableHttpException::class);
        $this->expectExceptionMessage('O serviço de consulta de CNPJ está indisponível.');

        $this->service->lookup('04252011000110');
    }

    public function test_throws_service_unavailable_for_connection_failure(): void
    {
        Http::fake([
            config('services.receitaws_url').'04252011000110' => Http::failedConnection(),
        ]);

        $this->expectException(ServiceUnavailableHttpException::class);
        $this->expectExceptionMessage('O serviço de consulta de CNPJ está indisponível.');

        $this->service->lookup('04252011000110');
    }

    public function test_throws_service_unavailable_for_invalid_json_response(): void
    {
        Http::fake([
            config('services.receitaws_url').'04252011000110' => Http::response('invalid json'),
        ]);

        $this->expectException(ServiceUnavailableHttpException::class);
        $this->expectExceptionMessage('O serviço de consulta de CNPJ retornou uma resposta inválida.');

        $this->service->lookup('04252011000110');
    }
}
