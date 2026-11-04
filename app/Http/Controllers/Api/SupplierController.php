<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierIndexRequest;
use App\Http\Requests\SupplierRequest;
use App\Models\City;
use App\Models\Company;
use App\Models\Individual;
use App\Models\State;
use App\Models\Supplier;
use App\Services\ReceitaWsService;
use App\Services\SupplierService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    public function index(SupplierIndexRequest $request): View
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
        $suppliers = $query->paginate($pageSize)->withQueryString();

        return view('supplier.index', compact('filters', 'suppliers'));
    }

    public function create(): View
    {
        return view('supplier.create', $this->formOptions());
    }

    public function store(SupplierRequest $request, SupplierService $service): RedirectResponse
    {
        $supplier = $service->save($request->validated());

        return redirect()->route('suppliers.show', $supplier)->with('status', 'Fornecedor cadastrado com sucesso.');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['supplierable', 'stateInfo', 'cityInfo', 'contacts.methods']);

        return view('supplier.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        $supplier->load(['supplierable', 'stateInfo', 'cityInfo', 'contacts.methods']);

        return view('supplier.create', [...$this->formOptions(), 'supplier' => $supplier]);
    }

    public function update(SupplierRequest $request, Supplier $supplier, SupplierService $service): RedirectResponse
    {
        $supplier = $service->save($request->validated(), $supplier);

        return redirect()->route('suppliers.show', $supplier)->with('status', 'Fornecedor atualizado com sucesso.');
    }

    public function destroy(Supplier $supplier, SupplierService $service): RedirectResponse
    {
        $service->delete($supplier);

        return redirect()->route('suppliers.index')->with('status', 'Fornecedor removido com sucesso.');
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

    /** @return array{states: Collection<int, State>, cities: Collection<int, City>} */
    private function formOptions(): array
    {
        return [
            'states' => State::query()->orderBy('name')->get(['id', 'code', 'name']),
            'cities' => City::query()->orderBy('name')->get(['id', 'state_id', 'name']),
        ];
    }
}
