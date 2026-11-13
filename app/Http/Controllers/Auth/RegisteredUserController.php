<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EmailVerificationCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'conditions' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Le compte n'est PAS connecté : il le sera après la saisie du code reçu par e-mail
        $sent = EmailVerificationCode::send($user);
        $request->session()->put('verification_email', $user->email);

        $redirect = redirect()->route('verification.code');

        return $sent
            ? $redirect->with('status', 'Compte créé. Un code à 6 chiffres vient d’être envoyé à votre adresse e-mail.')
            : $redirect->withErrors(['code' => 'Compte créé, mais l’e-mail n’a pas pu être envoyé. Cliquez sur « renvoyer le code ».']);
    }
}
