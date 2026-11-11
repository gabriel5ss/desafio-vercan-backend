<div class="table-responsive">
    <table class="table table-striped align-middle w-100">
        <thead>
            <tr>
                <th scope="col">Nome</th>
                <th scope="col">E-mail</th>
                <th scope="col" class="text-end">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('user.show', $user) }}" class="btn btn-sm btn-outline-info" title="Ver usuário" aria-label="Visualizar {{ $user->name }}">
                                <i class="mdi mdi-eye-outline" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('user.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Editar usuário" aria-label="Editar {{ $user->name }}">
                                <i class="mdi mdi-pencil-outline" aria-hidden="true"></i>
                            </a>
                            @unless (auth()->user()->is($user))
                                <form action="{{ route('user.destroy', $user) }}" method="POST" onsubmit="return confirm('Deseja excluir este usuário?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir usuário" aria-label="Excluir {{ $user->name }}">
                                        <i class="mdi mdi-delete-outline" aria-hidden="true"></i>
                                    </button>
                                </form>
                            @endunless
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">Nenhum usuário cadastrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
