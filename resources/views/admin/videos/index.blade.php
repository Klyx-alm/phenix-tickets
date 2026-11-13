<x-dashboard-layout title="Vidéos" area="admin">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold">Vidéos</h1>
            <p class="mt-1 max-w-xl text-stone-600">Les vidéos du carrousel « Nos réalisations ». Collez un lien TikTok et, si vous le souhaitez, envoyez aussi le fichier vidéo pour une lecture garantie sur le site.</p>
        </div>
        <a href="{{ route('admin.videos.create') }}" class="btn-flame">Ajouter une vidéo</a>
    </div>

    @if (session('status'))
        <p class="mt-6 rounded-xl border border-green-300 bg-green-50 p-4 text-sm">{{ session('status') }}</p>
    @endif

    <div class="panel mt-8 overflow-x-auto">
        <table class="table-clean w-full text-left">
            <thead>
                <tr>
                    <th>Miniature</th>
                    <th>Titre</th>
                    <th>Lecture</th>
                    <th>Ordre</th>
                    <th>Affichage</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($videos as $video)
                    <tr>
                        <td>
                            @if ($video->coverUrl())
                                <img src="{{ $video->coverUrl() }}" alt="" class="h-20 w-12 rounded-lg object-cover">
                            @elseif ($video->hasFile())
                                <video src="{{ $video->fileUrl() }}#t=0.1" class="h-20 w-12 rounded-lg bg-black object-cover" preload="metadata" muted playsinline></video>
                            @else
                                <span class="grid h-20 w-12 place-items-center rounded-lg bg-stone-200 text-xs text-stone-500">Aucune</span>
                            @endif
                        </td>
                        <td>
                            <p class="font-semibold">{{ $video->title }}</p>
                            <a href="{{ $video->source_url }}" target="_blank" rel="noopener" class="text-sm text-stone-600 underline">
                                {{ $video->type === 'photo' ? 'Publication photo' : 'Vidéo' }} sur TikTok
                            </a>
                        </td>
                        <td>
                            <span class="pill {{ $video->hasFile() ? 'pill-ok' : 'pill-muted' }}">{{ $video->hasFile() ? 'Fichier du site' : 'Lecteur TikTok' }}</span>
                        </td>
                        <td>{{ $video->position }}</td>
                        <td><span class="pill {{ $video->is_visible ? 'pill-ok' : 'pill-muted' }}">{{ $video->is_visible ? 'Visible' : 'Masquée' }}</span></td>
                        <td>
                            <div class="flex flex-wrap justify-end gap-2">
                                <a href="{{ route('admin.videos.edit', $video) }}" class="btn-outline btn-sm">Modifier</a>
                                <form method="POST" action="{{ route('admin.videos.cover', $video) }}">
                                    @csrf
                                    <button type="submit" class="btn-outline btn-sm">Actualiser la miniature</button>
                                </form>
                                <form method="POST" action="{{ route('admin.videos.destroy', $video) }}" onsubmit="return confirm('Retirer cette vidéo du site ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-outline btn-sm">Retirer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-stone-600">Aucune vidéo pour le moment.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-dashboard-layout>
