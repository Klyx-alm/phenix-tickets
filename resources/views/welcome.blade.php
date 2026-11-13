@php
    $agency = config('agency');
    $sellUrl = auth()->check() ? route('organizer.become') : route('organizer.register');
    $contact = config('billetterie.contact');
    $payoutDelay = config('billetterie.payout_delay_days');

    // Icônes des prestations (traits simples, même style que l'espace admin)
    $icons = [
        'megaphone' => '<path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'video' => '<path d="m16 13 5.2 3.5a.5.5 0 0 0 .8-.4V7.9a.5.5 0 0 0-.8-.4L16 10.5"/><rect x="2" y="6" width="14" height="12" rx="2"/>',
        'ticket' => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v2M13 17v2M13 11v2"/>',
    ];
@endphp
<x-public-layout title="Agence événementielle">
    {{-- Accueil --}}
    <section class="hero hero-photo" style="--hero-photo: url('{{ asset('images/hero-phenix.jpeg') }}')">
        <div class="mx-auto grid max-w-6xl items-center gap-12 px-6 py-16 md:py-24 {{ $featured ? 'md:grid-cols-2' : '' }}">
            <div>
                <h1 class="font-display text-4xl font-extrabold leading-[1.05] md:text-6xl">
                    {{ $agency['hero']['title'] }}
                </h1>
                <p class="mt-6 max-w-md text-lg text-white/80">{{ $agency['hero']['text'] }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#realisations" class="btn-flame">Voir nos réalisations</a>
                    <a href="#evenements" class="btn-ghost">Acheter un billet</a>
                </div>
            </div>
            @if ($featured)
                <div class="md:rotate-2">
                    <p class="mb-3 text-sm font-medium text-white/70">Prochain événement</p>
                    <x-event-ticket :event="$featured" />
                </div>
            @endif
        </div>
    </section>

    {{-- L'agence --}}
    <section id="agence" class="mx-auto max-w-6xl scroll-mt-20 px-6 py-16 md:py-24">
        <div class="grid gap-8 md:grid-cols-5 md:gap-12">
            <h2 class="font-display text-3xl font-bold leading-tight md:col-span-2 md:text-4xl">{{ $agency['about']['title'] }}</h2>
            <div class="space-y-5 text-lg text-stone-700 md:col-span-3">
                @foreach ($agency['about']['paragraphs'] as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        </div>

        <ul class="mt-14 grid gap-px overflow-hidden rounded-3xl bg-stone-300 md:grid-cols-3">
            @foreach ($agency['goals'] as $goal)
                <li class="bg-white p-7">
                    <h3 class="font-display text-xl font-bold">{{ $goal['title'] }}</h3>
                    <p class="mt-3 text-stone-600">{{ $goal['text'] }}</p>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Prestations --}}
    <section id="prestations" class="scroll-mt-20 bg-white">
        <div class="mx-auto grid max-w-6xl gap-10 px-6 py-16 md:grid-cols-5 md:gap-16 md:py-24">
            <div class="md:col-span-2">
                <div class="md:sticky md:top-24">
                    <h2 class="font-display text-3xl font-bold leading-tight md:text-4xl">Ce que nous faisons pour vous</h2>
                    <p class="mt-4 text-stone-600">Une offre complète pour les organisateurs et les lieux : on peut tout prendre en charge, ou seulement la partie dont vous avez besoin.</p>
                    <a href="#contact" class="btn-outline mt-6">Parler de votre projet</a>
                </div>
            </div>
            <div class="md:col-span-3">
                @foreach ($agency['services'] as $service)
                    <article class="flex gap-5 border-t border-stone-200 py-8 first:border-t-0 first:pt-0">
                        <span class="service-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">{!! $icons[$service['icon']] ?? '' !!}</svg>
                        </span>
                        <div>
                            <h3 class="font-display text-xl font-bold">{{ $service['title'] }}</h3>
                            <p class="mt-2 text-stone-600">{{ $service['text'] }}</p>
                            <ul class="mt-4 space-y-2 text-stone-700">
                                @foreach ($service['items'] as $item)
                                    <li class="flex gap-3">
                                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-[var(--braise)]" aria-hidden="true"></span>
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Méthode de travail --}}
    <section id="methode" class="mx-auto max-w-6xl scroll-mt-20 px-6 py-16 md:py-24">
        <h2 class="font-display text-3xl font-bold md:text-4xl">Comment on travaille</h2>
        <p class="mt-3 max-w-lg text-stone-600">Quatre étapes, du premier échange au lendemain de l’événement.</p>
        <ol class="mt-10 grid gap-8 md:grid-cols-4">
            @foreach ($agency['process'] as $step)
                <li>
                    <span class="step-badge font-display" aria-hidden="true">{{ $loop->iteration }}</span>
                    <h3 class="font-display mt-4 text-lg font-bold">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-stone-600">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Réalisations : vidéos --}}
    <x-video-reels :videos="$videos" />

    {{-- Zohar Agency --}}
    <section class="mx-auto max-w-6xl px-6 pt-16 md:pt-24">
        <div class="rounded-3xl bg-white p-8 md:p-12">
            <h2 class="font-display max-w-2xl text-2xl font-bold leading-tight md:text-3xl">{{ $agency['zohar']['title'] }}</h2>
            <p class="mt-4 max-w-2xl text-lg text-stone-700">{{ $agency['zohar']['text'] }}</p>
        </div>
    </section>

    {{-- L'équipe --}}
    @if ($team->isNotEmpty())
        <section id="equipe" class="mx-auto max-w-6xl scroll-mt-20 px-6 py-16 md:py-24">
            <h2 class="font-display text-3xl font-bold md:text-4xl">{{ $agency['team']['title'] }}</h2>
            <p class="mt-3 max-w-lg text-stone-600">{{ $agency['team']['text'] }}</p>

            <ul class="team-grid mt-12">
                @foreach ($team as $member)
                    <li class="team-member">
                        <div class="team-card">
                            @if ($member->photoUrl())
                                <img
                                    src="{{ $member->photoUrl() }}"
                                    alt="Photo de {{ $member->name }}"
                                    loading="lazy"
                                    style="object-position: {{ $member->focus }}">
                            @else
                                <span class="team-initial font-display" aria-hidden="true">{{ mb_substr($member->name, 0, 1) }}</span>
                            @endif
                            <div class="team-info">
                                <p class="font-display text-base font-bold leading-tight md:text-lg">{{ $member->name }}</p>
                                <p class="mt-1 text-sm leading-snug text-white/85">{{ $member->role }}</p>
                                @if ($member->note)
                                    <p class="mt-1 text-xs text-white/70">{{ $member->note }}</p>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Billetterie : événements à venir --}}
    <section id="evenements" class="scroll-mt-20 bg-white">
        <div class="mx-auto max-w-6xl px-6 py-16 md:py-24">
            <h2 class="font-display text-3xl font-bold md:text-4xl">Billetterie en ligne</h2>
            <p class="mt-3 max-w-xl text-stone-600">
                Choisissez votre événement, payez en Mobile Money (MTN MoMo, Moov Money) ou par carte, et recevez votre billet avec QR code. Il suffit de le présenter à l’entrée, sur téléphone ou imprimé.
            </p>
            <div class="mt-10 grid gap-8 md:grid-cols-2">
                @forelse ($events as $event)
                    <x-event-ticket :event="$event" />
                @empty
                    <p class="text-stone-600">Aucun événement publié pour le moment. Les prochains billets arrivent bientôt.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Questions fréquentes --}}
    <section id="faq" class="mx-auto max-w-6xl scroll-mt-20 px-6 py-16 md:py-24">
        <div class="grid gap-10 md:grid-cols-5 md:gap-16">
            <div class="md:col-span-2">
                <h2 class="font-display text-3xl font-bold leading-tight md:text-4xl">Questions fréquentes</h2>
                <p class="mt-4 text-stone-600">Les réponses les plus demandées sur les billets, les paiements et l’organisation d’un événement. Le reste est dans <a href="{{ route('help') }}" class="underline">la page d’aide</a>.</p>
            </div>
            <div class="divide-y divide-stone-300 md:col-span-3">
                @foreach ($agency['faq'] as $item)
                    <details class="group py-5 first:pt-0">
                        <summary class="flex cursor-pointer items-start justify-between gap-4 font-semibold">
                            <span>{{ $item['q'] }}</span>
                            <span class="faq-mark" aria-hidden="true"></span>
                        </summary>
                        <p class="mt-3 text-stone-600">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Organisateurs --}}
    <section class="mx-auto max-w-6xl px-6">
        <div class="rounded-3xl bg-white p-8 md:p-12">
            <h2 class="font-display text-3xl font-bold">Vous organisez un événement ?</h2>
            <p class="mt-4 max-w-xl text-stone-600">
                Créez votre page, fixez vos prix et vendez vos billets en ligne. Une commission de 10 % est prélevée sur chaque billet vendu. Le reste vous est reversé après l’événement (fin de l’événement plus {{ $payoutDelay }} jour(s)), en Mobile Money.
            </p>
            <a href="{{ $sellUrl }}" class="btn-outline mt-6">Créer mon espace organisateur</a>
        </div>
    </section>

    {{-- Contact --}}
    <section id="contact" class="mx-auto max-w-6xl scroll-mt-20 px-6 py-16 md:py-24">
        <div class="hero rounded-3xl p-8 md:p-12">
            <h2 class="font-display max-w-xl text-3xl font-bold md:text-4xl">Parlons de votre événement ou de votre lieu.</h2>
            <p class="mt-4 max-w-md text-white/80">Dites-nous ce que vous préparez, la date visée et le lieu. Nous vous répondons rapidement.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="https://wa.me/{{ $contact['whatsapp_number'] }}" target="_blank" rel="noopener" class="btn-flame">Écrire sur WhatsApp</a>
                <a href="{{ route('contact') }}" class="btn-ghost">Envoyer un message</a>
            </div>
        </div>
    </section>
</x-public-layout>
