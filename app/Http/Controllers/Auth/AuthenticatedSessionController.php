<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\EmailVerificationCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Mot de passe contrôlé, mais l'utilisateur n'est pas encore connecté
        $user = $request->authenticate();

        // Connexion en deux temps : à chaque connexion, un code est envoyé par e-mail
        $request->session()->regenerate();
        $request->session()->put('login_remember', $request->boolean('remember'));

        $sent = EmailVerificationCode::send($user);
        $request->session()->put('verification_email', $user->email);

        $redirect = redirect()->route('verification.code');

        return $sent
            ? $redirect->with('status', 'Mot de passe correct. Un code à 6 chiffres vient d’être envoyé à votre adresse e-mail pour confirmer votre connexion.')
            : $redirect->withErrors(['code' => 'Mot de passe correct, mais l’e-mail n’a pas pu être envoyé. Cliquez sur « renvoyer le code ».']);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
