<?php

namespace App\Support;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Code à 6 chiffres envoyé par e-mail : création, envoi, contrôle.
 * Sert à vérifier l'adresse à l'inscription et à confirmer chaque connexion.
 * Le code n'est jamais stocké en clair (seulement une empreinte).
 */
class EmailVerificationCode
{
    public const VALID_MINUTES = 15;

    public const MAX_ATTEMPTS = 5;

    public const RESEND_SECONDS = 60;

    /** Crée un nouveau code, l'enregistre et l'envoie par e-mail. */
    public static function issue(User $user): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'verification_code' => self::fingerprint($user, $code),
            'verification_code_expires_at' => now()->addMinutes(self::VALID_MINUTES),
            'verification_code_sent_at' => now(),
            'verification_attempts' => 0,
        ])->save();

        Mail::to($user->email)->send(new VerificationCodeMail($user, $code, self::VALID_MINUTES));

        return $code;
    }

    /** Comme issue(), mais sans jamais planter : renvoie false si l'e-mail n'a pas pu partir. */
    public static function send(User $user): bool
    {
        try {
            self::issue($user);

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    public static function canResend(User $user): bool
    {
        return ! $user->verification_code_sent_at
            || $user->verification_code_sent_at->lte(now()->subSeconds(self::RESEND_SECONDS));
    }

    /**
     * Contrôle le code saisi. Renvoie : ok, invalid, expired, locked ou missing.
     * En cas de succès, le code est effacé (il ne sert qu'une fois) et l'adresse
     * est marquée comme vérifiée si elle ne l'était pas encore.
     */
    public static function check(User $user, string $input): string
    {
        if (! $user->verification_code || ! $user->verification_code_expires_at) {
            return 'missing';
        }

        if ($user->verification_code_expires_at->isPast()) {
            return 'expired';
        }

        if ($user->verification_attempts >= self::MAX_ATTEMPTS) {
            return 'locked';
        }

        if (! hash_equals($user->verification_code, self::fingerprint($user, $input))) {
            $user->forceFill(['verification_attempts' => $user->verification_attempts + 1])->save();

            return 'invalid';
        }

        $user->forceFill([
            'verification_code' => null,
            'verification_code_expires_at' => null,
            'verification_code_sent_at' => null,
            'verification_attempts' => 0,
        ])->save();

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return 'ok';
    }

    private static function fingerprint(User $user, string $code): string
    {
        return hash_hmac('sha256', $user->id.'|'.$code, (string) config('app.key'));
    }
}
