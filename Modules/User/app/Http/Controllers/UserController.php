<?php

declare(strict_types=1);

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\User\Http\Requests\UserRequest;
use Modules\User\Models\User;
use Modules\User\Services\UserService;

/** Gerencia as telas de usuários com acesso autenticado, como no módulo Supplier. */
final class UserController extends Controller
{
    /**
     * Exibe os usuários em uma listagem paginada.
     *
     * @return View Tela de listagem.
     */
    public function index(): View
    {
        $users = User::query()->orderBy('name')->orderBy('id')->paginate(15);

        return view('user::index', compact('users'));
    }

    /**
     * Exibe o formulário de cadastro.
     *
     * @return View Tela de cadastro.
     */
    public function create(): View
    {
        return view('user::create', ['user' => new User(), 'read_only' => false]);
    }

    /**
     * Salva os dados validados do novo usuário.
     *
     * @param UserRequest $request Dados validados do formulário.
     * @param UserService $service Persistência transacional do usuário e da observação.
     *
     * @return RedirectResponse Retorno à listagem.
     */
    public function store(UserRequest $request, UserService $service): RedirectResponse
    {
        $service->save($request->validated(), new User());

        return redirect()->route('user.index')->with('status', 'Usuário cadastrado com sucesso.');
    }

    /**
     * Exibe os dados do usuário sem permitir alterações.
     *
     * @param User $user Usuário consultado.
     *
     * @return View Tela de consulta.
     */
    public function show(User $user): View
    {
        $user->load('observation');

        return view('user::show', compact('user'));
    }

    /**
     * Exibe o formulário de edição.
     *
     * @param User $user Usuário editado.
     *
     * @return View Tela de edição.
     */
    public function edit(User $user): View
    {
        $user->load('observation');

        return view('user::create', ['user' => $user, 'read_only' => false]);
    }

    /**
     * Atualiza o usuário, mantendo a senha atual quando o campo estiver vazio.
     *
     * @param UserRequest $request Dados validados do formulário.
     * @param User $user Usuário editado.
     * @param UserService $service Persistência transacional do usuário e da observação.
     *
     * @return RedirectResponse Retorno à listagem.
     */
    public function update(UserRequest $request, User $user, UserService $service): RedirectResponse
    {
        $service->save($request->validated(), $user);

        return redirect()->route('user.index')->with('status', 'Usuário atualizado com sucesso.');
    }

    /**
     * Remove o usuário, impedindo a exclusão da própria conta em uso.
     *
     * @param Request $request Requisição autenticada.
     * @param User $user Usuário removido.
     * @param UserService $service Persistência transacional do usuário e da observação.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Quando houver tentativa de excluir a própria conta.
     *
     * @return RedirectResponse Retorno à listagem.
     */
    public function destroy(Request $request, User $user, UserService $service): RedirectResponse
    {
        abort_if($request->user()?->is($user), 403, 'Não é possível excluir sua própria conta.');

        $service->delete($user);

        return redirect()->route('user.index')->with('status', 'Usuário removido com sucesso.');
    }
}
