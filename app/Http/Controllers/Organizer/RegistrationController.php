<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Organizer;
use App\Models\User;
use App\Support\EmailVerificationCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('organizer.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'structure_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'conditions' => ['accepted'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $user->forceFill(['role' => 'organizer', 'phone' => $data['phone']])->save();

            $slug = Str::slug($data['structure_name']) ?: 'structure';
            if (Organizer::where('slug', $slug)->exists()) {
                $slug .= '-'.Str::lower(Str::random(4));
            }

            $organizer = Organizer::create([
                'user_id' => $user->id,
                'name' => $data['structure_name'],
                'slug' => $slug,
                'phone' => $data['phone'],
            ]);
            $organizer->forceFill(['status' => 'pending'])->save();

            return $user;
        });

        // Le compte n'est PAS connecté : il le sera après la saisie du code reçu par e-mail
        $sent = EmailVerificationCode::send($user);
        $request->session()->put('verification_email', $user->email);

        $redirect = redirect()->route('verification.code');

        return $sent
            ? $redirect->with('status', 'Compte créé. Un code à 6 chiffres vient d’être envoyé à votre adresse e-mail.')
            : $redirect->withErrors(['code' => 'Compte créé, mais l’e-mail n’a pas pu être envoyé. Cliquez sur « renvoyer le code ».']);
    }
}
