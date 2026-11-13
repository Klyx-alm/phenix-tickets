<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeamMemberController extends Controller
{
    public function index(): View
    {
        return view('admin.team.index', [
            'members' => TeamMember::ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.team.form', [
            'member' => new TeamMember([
                'focus' => '50% 25%',
                'is_visible' => true,
                'position' => ((int) TeamMember::max('position')) + 1,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, photoRequired: true);

        TeamMember::create([
            'name' => $data['name'],
            'role' => $data['role'],
            'note' => $data['note'] ?? null,
            'photo' => $request->file('photo')->store('team', 'public'),
            'focus' => $data['focus'],
            'position' => $data['position'],
            'is_visible' => $request->boolean('is_visible'),
        ]);

        return redirect()->route('admin.team')->with('status', 'Membre ajouté à l’équipe.');
    }

    public function edit(TeamMember $member): View
    {
        return view('admin.team.form', ['member' => $member]);
    }

    public function update(Request $request, TeamMember $member): RedirectResponse
    {
        $data = $this->validated($request, photoRequired: false);

        $attributes = [
            'name' => $data['name'],
            'role' => $data['role'],
            'note' => $data['note'] ?? null,
            'focus' => $data['focus'],
            'position' => $data['position'],
            'is_visible' => $request->boolean('is_visible'),
        ];

        if ($request->hasFile('photo')) {
            $member->deletePhotoFile();
            $attributes['photo'] = $request->file('photo')->store('team', 'public');
        }

        $member->update($attributes);

        return redirect()->route('admin.team')->with('status', 'Membre mis à jour.');
    }

    public function destroy(TeamMember $member): RedirectResponse
    {
        $member->deletePhotoFile();
        $member->delete();

        return redirect()->route('admin.team')->with('status', 'Membre retiré de l’équipe.');
    }

    private function validated(Request $request, bool $photoRequired): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'role' => ['required', 'string', 'max:160'],
            'note' => ['nullable', 'string', 'max:160'],
            'photo' => [$photoRequired ? 'required' : 'nullable', 'image', 'max:2048'],
            'focus' => ['required', Rule::in(array_keys(TeamMember::FOCUS))],
            'position' => ['required', 'integer', 'min:0', 'max:999'],
            'is_visible' => ['nullable', 'boolean'],
        ], [
            'photo.required' => 'Ajoutez une photo du membre.',
            'photo.image' => 'Le fichier doit être une image (JPG, PNG ou WebP).',
            'photo.max' => 'La photo doit faire moins de 2 Mo.',
            'photo.uploaded' => 'La photo n’a pas pu être envoyée : elle doit faire moins de 2 Mo.',
        ]);
    }
}
