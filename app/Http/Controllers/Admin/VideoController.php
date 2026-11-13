<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Support\TikTok;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(): View
    {
        return view('admin.videos.index', [
            'videos' => Video::ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.videos.form', [
            'video' => new Video([
                'is_visible' => true,
                'position' => ((int) Video::max('position')) + 1,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $tiktok = $this->resolveLink($data['link']);

        if (Video::where('tiktok_id', $tiktok['id'])->exists()) {
            throw ValidationException::withMessages(['link' => 'Cette publication est déjà dans la liste.']);
        }

        $cover = $request->hasFile('cover')
            ? $request->file('cover')->store('covers', 'public')
            : $this->downloadCover($tiktok);

        $file = $request->hasFile('video_file')
            ? $request->file('video_file')->store('videos', 'public')
            : null;

        Video::create([
            'title' => $data['title'],
            'source_url' => $tiktok['url'],
            'tiktok_id' => $tiktok['id'],
            'type' => $tiktok['type'],
            'cover' => $cover,
            'file' => $file,
            'position' => $data['position'],
            'is_visible' => $request->boolean('is_visible'),
        ]);

        return redirect()->route('admin.videos')->with(
            'status',
            ($cover || $file)
                ? 'Vidéo ajoutée.'
                : 'Vidéo ajoutée, mais la miniature n’a pas pu être récupérée automatiquement. Envoyez-en une depuis « Modifier ».'
        );
    }

    public function edit(Video $video): View
    {
        return view('admin.videos.form', ['video' => $video]);
    }

    public function update(Request $request, Video $video): RedirectResponse
    {
        $data = $this->validated($request);

        $attributes = [
            'title' => $data['title'],
            'position' => $data['position'],
            'is_visible' => $request->boolean('is_visible'),
        ];

        $tiktok = null;
        $otherPost = false;
        if (trim($data['link']) !== $video->source_url) {
            $tiktok = $this->resolveLink($data['link']);
            $otherPost = $tiktok['id'] !== $video->tiktok_id;

            if ($otherPost && Video::where('tiktok_id', $tiktok['id'])->exists()) {
                throw ValidationException::withMessages(['link' => 'Cette publication est déjà dans la liste.']);
            }

            $attributes['source_url'] = $tiktok['url'];
            $attributes['tiktok_id'] = $tiktok['id'];
            $attributes['type'] = $tiktok['type'];
        }

        if ($request->hasFile('cover')) {
            $video->deleteCoverFile();
            $attributes['cover'] = $request->file('cover')->store('covers', 'public');
        } elseif ($otherPost) {
            // Autre publication : on essaie de récupérer sa miniature (sinon on garde l'actuelle si c'est le même lien)
            $video->deleteCoverFile();
            $attributes['cover'] = $this->downloadCover($tiktok);
        }

        if ($request->hasFile('video_file')) {
            $video->deleteVideoFile();
            $attributes['file'] = $request->file('video_file')->store('videos', 'public');
        } elseif ($request->boolean('remove_file')) {
            $video->deleteVideoFile();
            $attributes['file'] = null;
        }

        $video->update($attributes);

        return redirect()->route('admin.videos')->with('status', 'Vidéo mise à jour.');
    }

    public function refreshCover(Video $video): RedirectResponse
    {
        $path = $this->downloadCover([
            'id' => $video->tiktok_id,
            'url' => $video->source_url,
        ]);

        if (! $path) {
            return back()->with('status', 'Impossible de récupérer la miniature sur TikTok (détails dans storage/logs/laravel.log). Envoyez une image depuis « Modifier ».');
        }

        $video->deleteCoverFile();
        $video->update(['cover' => $path]);

        return back()->with('status', 'Miniature mise à jour.');
    }

    public function destroy(Video $video): RedirectResponse
    {
        $video->deleteCoverFile();
        $video->deleteVideoFile();
        $video->delete();

        return redirect()->route('admin.videos')->with('status', 'Vidéo retirée.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'link' => ['required', 'string', 'max:500'],
            'cover' => ['nullable', 'image', 'max:2048'],
            'video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm', 'max:102400'],
            'position' => ['required', 'integer', 'min:0', 'max:999'],
            'is_visible' => ['nullable', 'boolean'],
            'remove_file' => ['nullable', 'boolean'],
        ], [
            'cover.image' => 'La miniature doit être une image (JPG, PNG ou WebP).',
            'cover.max' => 'La miniature doit faire moins de 2 Mo.',
            'cover.uploaded' => 'La miniature n’a pas pu être envoyée : elle doit faire moins de 2 Mo.',
            'video_file.mimetypes' => 'Le fichier doit être une vidéo MP4, MOV ou WebM.',
            'video_file.max' => 'La vidéo doit faire moins de 100 Mo.',
            'video_file.uploaded' => 'La vidéo n’a pas pu être envoyée : elle dépasse la taille autorisée par le serveur PHP (upload_max_filesize et post_max_size dans php.ini).',
        ]);
    }

    /** @return array{id: string, type: string, url: string} */
    private function resolveLink(string $link): array
    {
        $tiktok = TikTok::resolve($link);

        if (! $tiktok) {
            throw ValidationException::withMessages([
                'link' => 'Lien TikTok non reconnu. Ouvrez la publication dans votre navigateur et collez l’adresse complète (tiktok.com/@compte/video/…). Les liens courts demandent une connexion à TikTok depuis le serveur.',
            ]);
        }

        return $tiktok;
    }

    private function downloadCover(array $tiktok): ?string
    {
        $image = TikTok::coverImage($tiktok['url']);

        if (! $image) {
            return null;
        }

        $path = 'covers/'.$tiktok['id'].'-'.substr(md5((string) microtime(true)), 0, 6).'.'.$image['ext'];
        Storage::disk('public')->put($path, $image['body']);

        return $path;
    }
}
