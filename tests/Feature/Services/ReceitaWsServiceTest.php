<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\ReceitaWsService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class ReceitaWsServiceTest extends TestCase
{
    private ReceitaWsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.receitaws_url' => 'https://receitaws.test/v1/cnpj/',
            'cache.default' => 'array',
        ]);

        $this->service = new ReceitaWsService();
    }

    public function test_lookup(): void
    {
        $company = ['status' => 'OK', 'nome' => 'Empresa Exemplo'];
        Http::fake([
            config('services.receitaws_url').'04252011000110' => Http::response($company),
        ]);

        $result = $this->service->lookup('04252011000110');

        $this->assertSame($company, $result);
        $this->assertSame($company, Cache::get('receitaws:04252011000110'));
        Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
            && $request->url() === config('services.receitaws_url').'04252011000110');
    }

    public function test_lookup_failed(): void
    {
        Cache::spy();
        Http::fake([
            config('services.receitaws_url').'04252011000110' => Http::response(['status' => 'ERROR'], 503),
        ]);

        Cache::shouldReceive('put')->never();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('O serviço de consulta de CNPJ está indisponível.');

        $this->service->lookup('04252011000110');
    }
}
