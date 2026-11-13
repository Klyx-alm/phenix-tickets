@php
    $editing = $video->exists;
@endphp
<x-dashboard-layout :title="$editing ? 'Modifier une vidéo' : 'Ajouter une vidéo'" area="admin">
    <div class="max-w-2xl">
        <a href="{{ route('admin.videos') }}" class="text-sm underline">Retour aux vidéos</a>
        <h1 class="font-display mt-3 text-3xl font-bold">{{ $editing ? 'Modifier la vidéo' : 'Ajouter une vidéo' }}</h1>

        @if ($errors->any())
            <p class="mt-6 rounded-xl border border-red-300 bg-red-50 p-4 text-sm">Enregistrement impossible : corrigez les champs signalés en rouge ci-dessous.</p>
        @endif

        <form method="POST" action="{{ $editing ? route('admin.videos.update', $video) : route('admin.videos.store') }}" enctype="multipart/form-data" class="mt-8 space-y-6">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div>
                <label class="field-label" for="title">Titre affiché sur la vidéo</label>
                <input class="field" id="title" name="title" value="{{ old('title', $video->title) }}" placeholder="Ex. Concert de Dopelym" required>
                @error('title') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="field-label" for="link">Lien TikTok</label>
                <input class="field" id="link" name="link" value="{{ old('link', $video->source_url) }}" placeholder="https://www.tiktok.com/@compte/video/..." required>
                <p class="mt-1 text-sm text-stone-500">Le plus sûr : ouvrez la vidéo sur TikTok dans votre navigateur et copiez l'adresse complète. Les liens courts (vt.tiktok.com) marchent seulement si le serveur arrive à joindre TikTok.</p>
                @error('link') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="field-label" for="video_file">Fichier vidéo (facultatif)</label>
                @if ($video->hasFile())
                    <video src="{{ $video->fileUrl() }}" class="mb-3 h-52 rounded-xl bg-black" controls preload="metadata" playsinline></video>
                @endif
                <input class="field" id="video_file" name="video_file" type="file" accept="video/mp4,video/quicktime,video/webm">
                <p class="mt-1 text-sm text-stone-500">MP4, MOV ou WebM, 100 Mo maximum. Si vous envoyez le fichier, la vidéo se lance directement sur le site, sur tous les téléphones et navigateurs. Sans fichier, on utilise le lecteur TikTok.</p>
                @error('video_file') <p class="field-error">{{ $message }}</p> @enderror
                @if ($video->hasFile())
                    <label class="mt-3 flex items-center gap-3 text-sm">
                        <input type="hidden" name="remove_file" value="0">
                        <input type="checkbox" name="remove_file" value="1" class="rounded">
                        <span>Supprimer le fichier vidéo actuel</span>
                    </label>
                @endif
            </div>

            <div>
                <label class="field-label" for="cover">Miniature (facultatif)</label>
                @if ($video->coverUrl())
                    <img src="{{ $video->coverUrl() }}" alt="" class="mb-3 h-44 w-24 rounded-xl object-cover">
                @endif
                <input class="field" id="cover" name="cover" type="file" accept="image/*">
                <p class="mt-1 text-sm text-stone-500">Image verticale, 2 Mo maximum. Laissez vide pour utiliser celle de TikTok ou, avec un fichier vidéo, une image de la vidéo.</p>
                @error('cover') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="field-label" for="position">Ordre d'affichage</label>
                <input class="field" id="position" name="position" type="number" min="0" max="999" value="{{ old('position', $video->position) }}" required>
                <p class="mt-1 text-sm text-stone-500">Le plus petit numéro s'affiche en premier.</p>
                @error('position') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-3">
                <input type="hidden" name="is_visible" value="0">
                <input type="checkbox" name="is_visible" value="1" class="rounded" @checked(old('is_visible', $video->is_visible))>
                <span>Afficher sur le site</span>
            </label>

            <button type="submit" class="btn-flame">{{ $editing ? 'Enregistrer' : 'Ajouter la vidéo' }}</button>
        </form>
    </div>
</x-dashboard-layout>
