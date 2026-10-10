<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierIndexRequest;
use App\Http\Requests\SupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Company;
use App\Models\Individual;
use App\Models\Supplier;
use App\Services\ReceitaWsService;
use App\Services\SupplierService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    public function index(SupplierIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $search = trim((string) ($filters['search'] ?? ''));
        $pageSize = (int) ($filters['per_page'] ?? 15);
        $query = Supplier::with(['supplierable', 'stateInfo', 'cityInfo', 'contacts.methods']);

        if ($search !== '') {
            $query->whereHasMorph('supplierable', [Individual::class], function ($personQuery) use ($search): void {
                $personQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%");
            })->orWhereHasMorph('supplierable', [Company::class], function ($personQuery) use ($search): void {
                $personQuery->where('corporate_name', 'like', "%{$search}%")
                    ->orWhere('trade_name', 'like', "%{$search}%")
                    ->orWhere('cnpj', 'like', "%{$search}%");
            });
        }

        $this->applySorting($query, $filters['sort_by'] ?? null, $filters['sort_direction'] ?? 'desc');
        $suppliers = $query->paginate($pageSize);

        return SupplierResource::collection($suppliers)->response();
    }

    /** @param Builder<Supplier> $query */
    private function applySorting(Builder $query, ?string $sortBy, string $direction): void
    {
        if ($sortBy === null) {
            $query->latest();

            return;
        }

        if ($sortBy === 'active') {
            $query->orderBy('suppliers.active', $direction);
        } else {
            $fields = [
                'name' => ['corporate_name', 'name'],
                'trade_name' => ['trade_name', 'nickname'],
                'document' => ['cnpj', 'cpf'],
            ];
            [$companyField, $individualField] = $fields[$sortBy] ?? $fields['name'];

            $query->orderByRaw(
                "CASE WHEN suppliers.supplierable_type = ? THEN (SELECT companies.{$companyField} FROM companies WHERE companies.id = suppliers.supplierable_id) ELSE (SELECT individuals.{$individualField} FROM individuals WHERE individuals.id = suppliers.supplierable_id) END {$direction}",
                ['company'],
            );
        }

        $query->orderBy('suppliers.id');
    }

    public function store(SupplierRequest $request, SupplierService $service): JsonResponse
    {
        $supplier = $service->save($request->validated());

        return (new SupplierResource($supplier))->response()->setStatusCode(201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return (new SupplierResource($supplier->load(['supplierable', 'stateInfo', 'cityInfo', 'contacts.methods'])))->response();
    }

    public function update(SupplierRequest $request, Supplier $supplier, SupplierService $service): JsonResponse
    {
        $supplier = $service->save($request->validated(), $supplier);

        return (new SupplierResource($supplier))->response();
    }

    public function destroy(Supplier $supplier, SupplierService $service): JsonResponse
    {
        $service->delete($supplier);

        return response()->json(['data' => ['message' => 'Fornecedor removido com sucesso.']]);
    }

    public function lookupCnpj(string $cnpj, ReceitaWsService $service): JsonResponse
    {
        Validator::make(['cnpj' => $cnpj], ['cnpj' => ['required', 'regex:/^\d{14}$/']])->validate();
        $company = $service->lookup($cnpj);

        if (! $company) {
            throw ValidationException::withMessages(['cnpj' => ['O CNPJ informado não pôde ser validado. Verifique o número e tente novamente.']]);
        }

        return response()->json(['data' => [
            'corporate_name' => $company['nome'] ?? '',
            'trade_name' => $company['fantasia'] ?? ($company['nome'] ?? ''),
            'cnpj_status' => $company['situacao'] ?? '',
            'postal_code' => $company['cep'] ?? '',
            'street' => $company['logradouro'] ?? '',
            'number' => $company['numero'] ?? '',
            'complement' => $company['complemento'] ?? '',
            'district' => $company['bairro'] ?? '',
            'city' => $company['municipio'] ?? '',
            'state' => $company['uf'] ?? '',
        ]]);
    }
}
