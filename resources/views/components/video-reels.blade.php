@props(['videos'])
@php
    $agency = config('agency');
@endphp
@if ($videos->isNotEmpty())
    <section id="realisations" class="reels-section scroll-mt-20">
        <div x-data="{
            playing: null,
            go(dir) {
                const t = this.$refs.track;
                const calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                t.scrollBy({ left: dir * t.clientWidth * 0.8, behavior: calm ? 'auto' : 'smooth' });
            }
        }">
            <div class="mx-auto flex max-w-6xl flex-wrap items-end justify-between gap-6 px-6 pb-8 pt-16 md:pt-24">
                <div>
                    <h2 class="font-display text-3xl font-bold md:text-4xl">{{ $agency['realisations']['title'] }}</h2>
                    <p class="mt-3 max-w-lg text-white/75">{{ $agency['realisations']['text'] }}</p>
                </div>
                <div class="hidden gap-2 md:flex">
                    <button type="button" class="reel-arrow" @click="go(-1)" aria-label="Vidéos précédentes">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
                    </button>
                    <button type="button" class="reel-arrow" @click="go(1)" aria-label="Vidéos suivantes">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <div x-ref="track" class="reel-track" tabindex="0" role="region" aria-label="Vidéos de Phénix Agency">
                @foreach ($videos as $video)
                    <article class="reel">
                        <div class="reel-frame">
                            <template x-if="playing === {{ $video->id }}">
                                @if ($video->hasFile())
                                    <video
                                        src="{{ $video->fileUrl() }}"
                                        class="reel-player"
                                        controls
                                        autoplay
                                        playsinline
                                        x-init="$el.play().catch(() => {})"></video>
                                @else
                                    <iframe
                                        src="{{ $video->embedUrl() }}"
                                        title="{{ $video->title }}"
                                        allow="autoplay; fullscreen; encrypted-media"
                                        allowfullscreen
                                        class="reel-player"></iframe>
                                @endif
                            </template>
                            <button
                                type="button"
                                class="reel-poster"
                                x-show="playing !== {{ $video->id }}"
                                @click="playing = {{ $video->id }}"
                                aria-label="Lire la vidéo : {{ $video->title }}">
                                @if ($video->coverUrl())
                                    <img src="{{ $video->coverUrl() }}" alt="" class="reel-cover" loading="lazy">
                                @elseif ($video->hasFile())
                                    <video src="{{ $video->fileUrl() }}#t=0.1" class="reel-cover" preload="metadata" muted playsinline tabindex="-1"></video>
                                @endif
                                <span class="reel-shade" aria-hidden="true"></span>
                                <span class="reel-play" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor" class="ml-1 h-7 w-7"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5z"/></svg>
                                </span>
                                <span class="reel-title font-display">{{ $video->title }}</span>
                            </button>
                        </div>
                        <a href="{{ $video->source_url }}" target="_blank" rel="noopener" class="reel-link">Ouvrir sur TikTok</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
