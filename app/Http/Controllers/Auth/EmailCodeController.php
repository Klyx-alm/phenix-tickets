<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EmailVerificationCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmailCodeController extends Controller
{
    /** Page « saisissez le code reçu par e-mail » (inscription ou connexion). */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        return view('auth.verify-code', ['email' => $this->maskEmail($user->email)]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        // On accepte « 123 456 » ou « 123-456 » : seuls les chiffres comptent
        $request->merge(['code' => preg_replace('/\D+/', '', (string) $request->input('code'))]);

        $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'Saisissez le code reçu par e-mail.',
            'code.digits' => 'Le code contient 6 chiffres.',
        ]);

        $wasVerified = $user->hasVerifiedEmail();

        $result = EmailVerificationCode::check($user, (string) $request->input('code'));

        if ($result !== 'ok') {
            throw ValidationException::withMessages(['code' => match ($result) {
                'expired' => 'Ce code a expiré. Demandez-en un nouveau.',
                'locked' => 'Trop d’essais. Demandez un nouveau code.',
                'missing' => 'Aucun code actif. Demandez-en un nouveau.',
                default => 'Code incorrect. Vérifiez le dernier e-mail reçu.',
            }]);
        }

        if (! $wasVerified) {
            event(new Verified($user));
        }

        // La case « Rester connecté » cochée sur la page de connexion est appliquée ici
        Auth::login($user, (bool) $request->session()->pull('login_remember', false));
        $request->session()->forget('verification_email');
        $request->session()->regenerate();

        return redirect()->intended($user->isOrganizer() ? route('organizer.dashboard') : route('dashboard'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);

        if (! $user) {
            return redirect()->route('login');
        }

        if (! EmailVerificationCode::canResend($user)) {
            return back()->withErrors(['code' => 'Patientez une minute avant de demander un nouveau code.']);
        }

        if (! EmailVerificationCode::send($user)) {
            return back()->withErrors(['code' => 'L’e-mail n’a pas pu être envoyé. Réessayez dans un instant ou contactez-nous.']);
        }

        return back()->with('status', 'Un nouveau code vient de vous être envoyé.');
    }

    /** Le compte qui attend son code, mémorisé en session après l'inscription ou la saisie du mot de passe. */
    private function pendingUser(Request $request): ?User
    {
        $email = $request->session()->get('verification_email');

        if (! $email) {
            return null;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $request->session()->forget('verification_email');

            return null;
        }

        return $user;
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($name, 0, 2).str_repeat('*', max(mb_strlen($name) - 2, 1)).'@'.$domain;
    }
}
