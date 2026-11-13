<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\EmailVerificationCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Atteinte quand un compte connecté mais non vérifié ouvre une page réservée
 * (middleware « verified ») : on le déconnecte et on lui envoie un code.
 */
class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        Auth::guard('web')->logout();
        $request->session()->regenerate();

        $sent = EmailVerificationCode::send($user);
        $request->session()->put('verification_email', $user->email);

        $redirect = redirect()->route('verification.code');

        return $sent
            ? $redirect->with('status', 'Validez votre adresse e-mail avec le code à 6 chiffres que nous venons de vous envoyer.')
            : $redirect->withErrors(['code' => 'L’e-mail n’a pas pu être envoyé. Cliquez sur « renvoyer le code ».']);
    }
}
