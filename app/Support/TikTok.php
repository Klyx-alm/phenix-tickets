<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Petits outils pour les liens TikTok collés dans l'admin :
 * retrouver l'identifiant d'une publication et récupérer sa miniature.
 * Les échecs sont écrits dans storage/logs/laravel.log.
 */
class TikTok
{
    private const PATTERN = '~tiktok\.com/(@[\w.\-]+)/(video|photo)/(\d+)~i';

    private const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15';

    /**
     * Accepte un lien complet (marche sans internet) ou un lien court (vt.tiktok.com/...).
     *
     * @return array{id: string, type: string, url: string}|null
     */
    public static function resolve(string $link): ?array
    {
        $url = trim($link);

        for ($hop = 0; $hop < 6; $hop++) {
            if ($found = self::parse($url)) {
                return $found;
            }

            // On ne suit que des adresses TikTok
            if (! preg_match('~^https?://([\w-]+\.)*tiktok\.com(/|$)~i', $url)) {
                return null;
            }

            try {
                $response = Http::withoutRedirecting()
                    ->withUserAgent(self::USER_AGENT)
                    ->timeout(10)
                    ->get($url);
            } catch (\Throwable $e) {
                Log::warning('TikTok : lien court impossible à ouvrir', ['url' => $url, 'erreur' => $e->getMessage()]);

                return null;
            }

            $location = (string) $response->header('Location');
            if ($response->redirect() && $location !== '') {
                $url = self::absolute($location, $url);

                continue;
            }

            // Pas de redirection : l'adresse complète est parfois dans la page (balise canonical, og:url)
            $body = str_replace('\\/', '/', html_entity_decode((string) $response->body()));
            if ($found = self::parse($body)) {
                return $found;
            }

            Log::warning('TikTok : adresse complète introuvable', ['url' => $url, 'statut' => $response->status()]);

            return null;
        }

        return null;
    }

    /**
     * Télécharge la miniature officielle de la publication (oEmbed TikTok).
     *
     * @return array{body: string, ext: string}|null
     */
    public static function coverImage(string $canonicalUrl): ?array
    {
        try {
            $oembed = Http::withUserAgent(self::USER_AGENT)
                ->timeout(10)
                ->get('https://www.tiktok.com/oembed', ['url' => $canonicalUrl]);

            if (! $oembed->successful()) {
                Log::warning('TikTok : oEmbed refusé', ['url' => $canonicalUrl, 'statut' => $oembed->status()]);

                return null;
            }

            $thumbnail = $oembed->json('thumbnail_url');
            if (! $thumbnail) {
                Log::warning('TikTok : pas de miniature dans la réponse oEmbed', ['url' => $canonicalUrl]);

                return null;
            }

            $image = Http::withUserAgent(self::USER_AGENT)->timeout(15)->get($thumbnail);
            if (! $image->successful() || strlen($image->body()) < 1000) {
                Log::warning('TikTok : miniature impossible à télécharger', ['url' => $canonicalUrl, 'statut' => $image->status()]);

                return null;
            }

            $info = @getimagesizefromstring($image->body());
            if ($info === false) {
                Log::warning('TikTok : la miniature reçue n\'est pas une image', ['url' => $canonicalUrl]);

                return null;
            }

            return [
                'body' => $image->body(),
                'ext' => match ($info['mime'] ?? '') {
                    'image/webp' => 'webp',
                    'image/png' => 'png',
                    default => 'jpg',
                },
            ];
        } catch (\Throwable $e) {
            Log::warning('TikTok : miniature, erreur réseau', ['url' => $canonicalUrl, 'erreur' => $e->getMessage()]);

            return null;
        }
    }

    private static function parse(string $url): ?array
    {
        if (! preg_match(self::PATTERN, $url, $m)) {
            return null;
        }

        $type = strtolower($m[2]);

        return [
            'id' => $m[3],
            'type' => $type,
            'url' => 'https://www.tiktok.com/'.$m[1].'/'.$type.'/'.$m[3],
        ];
    }

    private static function absolute(string $location, string $base): string
    {
        if (preg_match('~^https?://~i', $location)) {
            return $location;
        }

        $parts = parse_url($base);

        return ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? 'www.tiktok.com').'/'.ltrim($location, '/');
    }
}
