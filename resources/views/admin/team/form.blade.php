@php
    $editing = $member->exists;
@endphp
<x-dashboard-layout :title="$editing ? 'Modifier un membre' : 'Ajouter un membre'" area="admin">
    <div class="max-w-2xl">
        <a href="{{ route('admin.team') }}" class="text-sm underline">Retour à l'équipe</a>
        <h1 class="font-display mt-3 text-3xl font-bold">{{ $editing ? 'Modifier ' . $member->name : 'Ajouter un membre' }}</h1>

        <form method="POST" action="{{ $editing ? route('admin.team.update', $member) : route('admin.team.store') }}" enctype="multipart/form-data" class="mt-8 space-y-6">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div>
                <label class="field-label" for="name">Nom</label>
                <input class="field" id="name" name="name" value="{{ old('name', $member->name) }}" required>
                @error('name') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="field-label" for="role">Rôle</label>
                <input class="field" id="role" name="role" value="{{ old('role', $member->role) }}" placeholder="Ex. Chargé de communication" required>
                @error('role') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="field-label" for="note">Précision (facultatif)</label>
                <input class="field" id="note" name="note" value="{{ old('note', $member->note) }}" placeholder="Ex. Promotrice de Zohar Agency">
                @error('note') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="field-label" for="photo">Photo{{ $editing ? ' (laisser vide pour garder l\'actuelle)' : '' }}</label>
                @if ($member->photoUrl())
                    <img src="{{ $member->photoUrl() }}" alt="" class="mb-3 h-40 w-32 rounded-xl object-cover" style="object-position: {{ $member->focus }}">
                @endif
                <input class="field" id="photo" name="photo" type="file" accept="image/*" {{ $editing ? '' : 'required' }}>
                <p class="mt-1 text-sm text-stone-500">JPG, PNG ou WebP, 2 Mo maximum. Une photo verticale donne le meilleur rendu.</p>
                @error('photo') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="field-label" for="focus">Cadrage de la photo</label>
                    <select class="field" id="focus" name="focus">
                        @foreach (\App\Models\TeamMember::FOCUS as $value => $label)
                            <option value="{{ $value }}" @selected(old('focus', $member->focus) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('focus') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="field-label" for="position">Ordre d'affichage</label>
                    <input class="field" id="position" name="position" type="number" min="0" max="999" value="{{ old('position', $member->position) }}" required>
                    <p class="mt-1 text-sm text-stone-500">Le plus petit numéro s'affiche en premier.</p>
                    @error('position') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <label class="flex items-center gap-3">
                <input type="hidden" name="is_visible" value="0">
                <input type="checkbox" name="is_visible" value="1" class="rounded" @checked(old('is_visible', $member->is_visible))>
                <span>Afficher sur le site</span>
            </label>

            <button type="submit" class="btn-flame">{{ $editing ? 'Enregistrer' : 'Ajouter à l\'équipe' }}</button>
        </form>
    </div>
</x-dashboard-layout>
