<x-guest-layout>
    <h1 class="font-display text-2xl font-bold">Entrez votre code</h1>
    <p class="mt-1 text-sm text-stone-600">
        Nous avons envoyé un code à 6 chiffres à <strong>{{ $email }}</strong>. Il est valable {{ \App\Support\EmailVerificationCode::VALID_MINUTES }} minutes et ne sert qu'une fois.
    </p>

    @if (session('status'))
        <p class="mt-4 rounded-xl border border-green-300 bg-green-50 p-3 text-sm">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('verification.code.verify') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label class="field-label" for="code">Code de vérification</label>
            <input
                class="field text-center font-display text-3xl tracking-[0.4em]"
                id="code"
                name="code"
                inputmode="numeric"
                autocomplete="one-time-code"
                maxlength="7"
                placeholder="000000"
                required
                autofocus>
            @error('code') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="btn-flame w-full">Valider</button>
    </form>

    <form method="POST" action="{{ route('verification.code.resend') }}" class="mt-5 text-center text-sm text-stone-600">
        @csrf
        Pas reçu ? Regardez dans vos courriers indésirables, puis
        <button type="submit" class="underline">renvoyer le code</button>.
    </form>

    <p class="mt-3 text-center text-sm text-stone-600">
        <a href="{{ route('login') }}" class="underline">Retour à la connexion</a>
        &nbsp;·&nbsp;
        <a href="{{ route('register') }}" class="underline">Recommencer l'inscription</a>
    </p>
</x-guest-layout>
