<?php

declare(strict_types=1);

namespace Modules\Supplier\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ReceitaWsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Modules\Supplier\Http\Requests\SupplierIndexRequest;
use Modules\Supplier\Http\Requests\SupplierRequest;
use Modules\Supplier\Models\Supplier;
use Modules\Supplier\Services\SupplierService;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index(): View
    {
        return view('supplier::index');
    }

    public function indexApi(SupplierIndexRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $search = mb_trim((string) ($filters['search']['value'] ?? ''));
        $order = $filters['order'][0] ?? null;
        $sortBy = $order === null ? null : $filters['columns'][(int) $order['column']]['name'];

        $query = Supplier::with('supplierable')->select('suppliers.*');

        return DataTables::eloquent($query)
            ->filter(function () use ($query, $search): void {
                $query->searchTerm($search);
            })
            ->order(function () use ($query, $order, $sortBy): void {
                $query->applySorting(
                    $sortBy,
                    $order['dir'] ?? 'desc',
                );
            })
            ->addColumn('name', fn (Supplier $supplier): ?string => $supplier->name)
            ->addColumn('trade_name', fn (Supplier $supplier): string => $supplier->trade_name)
            ->addColumn('document', fn (Supplier $supplier): ?string => $supplier->document)
            ->addColumn('show_url', fn (Supplier $supplier): string => route('suppliers.show', $supplier))
            ->addColumn('edit_url', fn (Supplier $supplier): string => route('suppliers.edit', $supplier))
            ->addColumn('delete_url', fn (Supplier $supplier): string => route('suppliers.destroy', $supplier))
            ->only(['name', 'trade_name', 'document', 'active', 'show_url', 'edit_url', 'delete_url'])
            ->escapeColumns([])
            ->toJson();
    }

    public function create(): View
    {
        ['states' => $states, 'cities' => $cities] = Supplier::formOptions();

        $supplier = new Supplier();
        $formData = $supplier->present()->formData();

        return view('supplier::create', compact('states', 'cities', 'formData'));
    }

    public function store(SupplierRequest $request, SupplierService $service): RedirectResponse
    {
        $service->save($request->validated());

        return redirect()->route('suppliers.index')->with('status', 'Fornecedor cadastrado com sucesso.');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['supplierable', 'stateInfo', 'cityInfo', 'contacts.methods']);

        ['states' => $states, 'cities' => $cities] = Supplier::formOptions();
        $formData = $supplier->present()->formData();

        return view('supplier::show', compact('states', 'cities', 'supplier', 'formData'));
    }

    public function edit(Supplier $supplier): View
    {
        $supplier->load(['supplierable', 'stateInfo', 'cityInfo', 'contacts.methods']);

        ['states' => $states, 'cities' => $cities] = Supplier::formOptions();
        $formData = $supplier->present()->formData();

        return view('supplier::create', compact('states', 'cities', 'supplier', 'formData'));
    }

    public function update(SupplierRequest $request, Supplier $supplier, SupplierService $service): RedirectResponse
    {
        $service->save($request->validated(), $supplier);

        return redirect()->route('suppliers.index')->with('status', 'Fornecedor atualizado com sucesso.');
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

        return response()->json([
            'data' => [
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
            ],
        ]);
    }
}
