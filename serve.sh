#!/bin/sh
# Serveur local avec une limite d'envoi de 100 Mo (artisan serve ne permet pas de la changer)
cd "$(dirname "$0")/public" && php -d upload_max_filesize=100M -d post_max_size=110M -S 127.0.0.1:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
