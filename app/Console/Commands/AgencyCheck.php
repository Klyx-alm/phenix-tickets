<?php

namespace App\Console\Commands;

use App\Models\TeamMember;
use App\Models\Video;
use App\Support\TikTok;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AgencyCheck extends Command
{
    protected $signature = 'agency:check';

    protected $description = 'Vérifie la base de données, le stockage et la connexion à TikTok pour la page d’accueil';

    public function handle(): int
    {
        $this->info('1. Base de données');
        $connection = config('database.default');
        $this->line('   Connexion : '.$connection);

        if ($connection === 'sqlite') {
            $path = config('database.connections.sqlite.database');
            $this->line('   Fichier : '.$path);
            $this->line('   Fichier modifiable : '.(is_writable($path) ? 'oui' : 'NON'));
            $this->line('   Dossier modifiable : '.(is_writable(dirname($path)) ? 'oui' : 'NON'));
        }

        try {
            $this->line('   Équipe : '.TeamMember::count().' membre(s). Vidéos : '.Video::count().'.');

            DB::beginTransaction();
            $test = Video::create([
                'title' => 'test',
                'source_url' => 'https://www.tiktok.com/@test/video/1',
                'tiktok_id' => 'check-'.uniqid(),
                'type' => 'video',
            ]);
            $test->update(['title' => 'test modifié']);
            $ok = Video::find($test->id)?->title === 'test modifié';
            DB::rollBack();

            $this->line('   Test d’écriture et de modification : '.($ok ? 'OK (test annulé, rien n’est gardé)' : 'ÉCHEC'));
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $this->error('   Erreur : '.$e->getMessage());
        }

        $this->newLine();
        $this->info('2. Stockage des fichiers');
        $this->line('   Lien public/storage : '.((is_link(public_path('storage')) || is_dir(public_path('storage'))) ? 'présent' : 'ABSENT, lancez : php artisan storage:link'));
        $this->line('   Dossier storage/app/public modifiable : '.(is_writable(storage_path('app/public')) ? 'oui' : 'NON'));
        $this->line('   upload_max_filesize : '.ini_get('upload_max_filesize').', post_max_size : '.ini_get('post_max_size').' (valeurs de la ligne de commande, le serveur web peut en avoir d’autres)');

        $this->newLine();
        $this->info('3. Vidéos enregistrées');
        foreach (Video::ordered()->get() as $v) {
            $this->line(sprintf(
                '   #%d %s | %s | miniature : %s | fichier : %s | visible : %s',
                $v->id,
                $v->title,
                $v->source_url,
                $v->cover ?: 'aucune',
                $v->file ?: 'aucun',
                $v->is_visible ? 'oui' : 'non'
            ));
        }

        $this->newLine();
        $this->info('4. Connexion à TikTok depuis le serveur');
        $sample = Video::ordered()->value('source_url') ?: 'https://www.tiktok.com/@phenixagency229/video/7692143912776895776';

        try {
            $response = Http::timeout(10)->get('https://www.tiktok.com/oembed', ['url' => $sample]);
            $this->line('   oEmbed : HTTP '.$response->status().', miniature annoncée : '.($response->json('thumbnail_url') ? 'oui' : 'non'));
        } catch (\Throwable $e) {
            $this->error('   oEmbed impossible : '.$e->getMessage());
        }

        $short = TikTok::resolve('https://vt.tiktok.com/ZSpm7K6YT/');
        $this->line('   Lien court : '.($short ? 'résolu ('.$short['id'].')' : 'NON résolu'));

        $cover = TikTok::coverImage($sample);
        $this->line('   Téléchargement d’une miniature : '.($cover ? 'OK' : 'ÉCHEC'));

        $this->newLine();
        $this->line('Détails des échecs réseau : storage/logs/laravel.log');

        return self::SUCCESS;
    }
}
