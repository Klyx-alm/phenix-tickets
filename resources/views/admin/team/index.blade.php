<x-dashboard-layout title="Équipe" area="admin">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">Équipe</h1>
            <p class="mt-1 max-w-xl text-stone-600">Les membres affichés sur la page d'accueil du site. Ajoutez, modifiez ou retirez une personne quand l'équipe change.</p>
        </div>
        <a href="{{ route('admin.team.create') }}" class="btn-flame">Ajouter un membre</a>
    </div>

    @if (session('status'))
        <p class="mt-6 rounded-xl border border-green-300 bg-green-50 p-4 text-sm">{{ session('status') }}</p>
    @endif

    <div class="panel mt-8 overflow-x-auto">
        <table class="table-clean w-full text-left">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Nom et rôle</th>
                    <th>Ordre</th>
                    <th>Affichage</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    <tr>
                        <td>
                            @if ($member->photoUrl())
                                <img src="{{ $member->photoUrl() }}" alt="" class="h-16 w-12 rounded-lg object-cover" style="object-position: {{ $member->focus }}">
                            @else
                                <span class="grid h-16 w-12 place-items-center rounded-lg bg-stone-200 text-stone-500">?</span>
                            @endif
                        </td>
                        <td>
                            <p class="font-semibold">{{ $member->name }}</p>
                            <p class="text-sm text-stone-600">{{ $member->role }}</p>
                            @if ($member->note)
                                <p class="text-sm text-stone-500">{{ $member->note }}</p>
                            @endif
                        </td>
                        <td>{{ $member->position }}</td>
                        <td><span class="pill {{ $member->is_visible ? 'pill-ok' : 'pill-muted' }}">{{ $member->is_visible ? 'Visible' : 'Masqué' }}</span></td>
                        <td>
                            <div class="flex flex-wrap justify-end gap-2">
                                <a href="{{ route('admin.team.edit', $member) }}" class="btn-outline btn-sm">Modifier</a>
                                <form method="POST" action="{{ route('admin.team.destroy', $member) }}" onsubmit="return confirm('Retirer {{ addslashes($member->name) }} de l\'équipe ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-outline btn-sm">Retirer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-stone-600">Aucun membre pour le moment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-dashboard-layout>
