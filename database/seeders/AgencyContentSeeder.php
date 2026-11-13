<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use App\Models\Video;
use App\Support\TikTok;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Remplit l'équipe et les vidéos de départ (une seule fois : ne fait rien si les tables ne sont pas vides).
 * Lancer avec : php artisan db:seed --class=AgencyContentSeeder
 */
class AgencyContentSeeder extends Seeder
{
    public function run(): void
    {
        if (TeamMember::count() === 0) {
            $team = [
                ['Kelly ALONOMBA', 'Chef de projet, directeur général et informaticien', null, 'images/team/kelly.jpg', '50% 25%'],
                ['Waris OLAONIKPEKOUN', 'PDG et promoteur de l’agence', null, 'images/team/waris.jpg', '50% 25%'],
                ['Kenneth KOUBLANOUN', 'Chargé de communication et chef commercial', null, 'images/team/kenneth.jpg', '50% 20%'],
                ['Consécraël', 'Monteur, vidéaste et animateur', null, 'images/team/consecrael.jpg', '50% 15%'],
                ['Irena TCHIBOZO', 'Cheffe hôtesse et créatrice de contenu vedette', 'Promotrice de Zohar Agency', 'images/team/irena.jpg', '50% 35%'],
            ];

            foreach ($team as $i => [$name, $role, $note, $photo, $focus]) {
                TeamMember::create([
                    'name' => $name,
                    'role' => $role,
                    'note' => $note,
                    'photo' => $photo,
                    'focus' => $focus,
                    'position' => $i + 1,
                ]);
            }
        }

        if (Video::count() === 0) {
            $videos = [
                ['7676061327500119329', 'video', 'Ekortipaaa'],
                ['7677309295838481696', 'photo', 'Concert de Emma’a'],
                ['7692143912776895776', 'video', 'Concert de Dopelym'],
                ['7705238914990116128', 'video', 'Screen and Chill'],
            ];

            foreach ($videos as $i => [$id, $type, $title]) {
                $url = 'https://www.tiktok.com/@phenixagency229/'.$type.'/'.$id;

                // Miniature récupérée sur TikTok (si ça échoue, on pourra la renvoyer depuis l'admin)
                $cover = null;
                if ($image = TikTok::coverImage($url)) {
                    $cover = 'covers/'.$id.'.'.$image['ext'];
                    Storage::disk('public')->put($cover, $image['body']);
                }

                Video::create([
                    'title' => $title,
                    'source_url' => $url,
                    'tiktok_id' => $id,
                    'type' => $type,
                    'cover' => $cover,
                    'position' => $i + 1,
                ]);
            }
        }
    }
}
