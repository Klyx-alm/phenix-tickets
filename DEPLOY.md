# Deploiement VPS - Phenix Tickets

## Prerequis VPS

- Ubuntu 22.04+ / Debian 12+
- 1 Go RAM minimum (2 Go recommande)
- Nom de domaine pointe vers l'IP du VPS

## 1. Installation des paquets

```bash
sudo apt update && sudo apt upgrade -y

# PHP 8.3+ et extensions
sudo apt install -y php php-cli php-fpm php-mysql php-mbstring php-xml \
    php-curl php-zip php-gd php-intl php-bcmath php-redis unzip git curl

# Nginx
sudo apt install -y nginx

# MySQL
sudo apt install -y mysql-server

# Node.js (pour le build des assets)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Supervisor (pour le queue worker)
sudo apt install -y supervisor

# Certbot (SSL gratuit)
sudo apt install -y certbot python3-certbot-nginx
```

## 2. Base de donnees MySQL

```bash
sudo mysql -u root <<SQL
CREATE DATABASE phenix_tickets CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'phenix'@'localhost' IDENTIFIED BY 'MOT_DE_PASSE_FORT';
GRANT ALL PRIVILEGES ON phenix_tickets.* TO 'phenix'@'localhost';
FLUSH PRIVILEGES;
SQL
```

## 3. Deploiement du code

```bash
# Creer l'utilisateur systeme
sudo useradd -m -s /bin/bash phenix

# Cloner le repo
sudo -u phenix git clone https://github.com/Klyx-alm/phenix-tickets.git /home/phenix/phenix-tickets
cd /home/phenix/phenix-tickets

# Installer les dependances PHP
sudo -u phenix composer install --no-dev --optimize-autoloader

# Installer et build les assets front
sudo -u phenix npm ci
sudo -u phenix npm run build

# Configurer l'environnement
sudo -u phenix cp .env.example .env
sudo -u phenix php artisan key:generate
```

## 4. Configurer le .env

Editer `/home/phenix/phenix-tickets/.env` :

```env
APP_NAME="Phenix Tickets"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ton-domaine.com

APP_LOCALE=fr

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=phenix_tickets
DB_USERNAME=phenix
DB_PASSWORD=MOT_DE_PASSE_FORT

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

# SMTP (exemple avec Brevo/Sendinblue, fonctionne aussi avec Mailgun, etc.)
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=ton-email@brevo.com
MAIL_PASSWORD=ta-cle-smtp
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tickets@ton-domaine.com
MAIL_FROM_NAME="Phenix Tickets"

# FedaPay
FEDAPAY_SECRET_KEY=sk_live_XXXXX
FEDAPAY_ENV=live
FEDAPAY_WEBHOOK_SECRET=whsec_XXXXX

# Admin initial
ADMIN_EMAIL=admin@ton-domaine.com
ADMIN_NAME="Admin Phenix"
ADMIN_PASSWORD=UnVraiMotDePasseFort!
```

## 5. Finaliser l'installation

```bash
cd /home/phenix/phenix-tickets

# Migrations
sudo -u phenix php artisan migrate --force

# Creer le lien symbolique pour les uploads (posters d'evenements)
sudo -u phenix php artisan storage:link

# Creer le compte admin
sudo -u phenix php artisan db:seed --class=AdminSeeder

# Cache de production
sudo -u phenix php artisan config:cache
sudo -u phenix php artisan route:cache
sudo -u phenix php artisan view:cache

# Permissions
sudo chown -R phenix:www-data /home/phenix/phenix-tickets/storage
sudo chown -R phenix:www-data /home/phenix/phenix-tickets/bootstrap/cache
sudo chmod -R 775 /home/phenix/phenix-tickets/storage
sudo chmod -R 775 /home/phenix/phenix-tickets/bootstrap/cache
```

## 6. Nginx

Creer `/etc/nginx/sites-available/phenix-tickets` :

```nginx
server {
    listen 80;
    server_name ton-domaine.com www.ton-domaine.com;
    root /home/phenix/phenix-tickets/public;

    index index.php;

    charset utf-8;
    client_max_body_size 10M;

    # Headers de securite
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Activer :

```bash
sudo ln -s /etc/nginx/sites-available/phenix-tickets /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

## 7. SSL avec Let's Encrypt

```bash
sudo certbot --nginx -d ton-domaine.com -d www.ton-domaine.com
```

## 8. Supervisor (queue worker)

Creer `/etc/supervisor/conf.d/phenix-worker.conf` :

```ini
[program:phenix-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/phenix/phenix-tickets/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=phenix
numprocs=1
redirect_stderr=true
stdout_logfile=/home/phenix/phenix-tickets/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start phenix-worker:*
```

## 9. Cron (scheduler pour expirer les commandes)

```bash
sudo crontab -u phenix -e
```

Ajouter cette ligne :

```
* * * * * cd /home/phenix/phenix-tickets && php artisan schedule:run >> /dev/null 2>&1
```

## 10. Webhook FedaPay

Dans le dashboard FedaPay, configurer le webhook :

- URL : `https://ton-domaine.com/webhooks/fedapay`
- Evenements : `transaction.approved`, `transaction.declined`, `transaction.canceled`

## 11. Mise a jour du site

```bash
cd /home/phenix/phenix-tickets
sudo -u phenix git pull origin main
sudo -u phenix composer install --no-dev --optimize-autoloader
sudo -u phenix npm ci && sudo -u phenix npm run build
sudo -u phenix php artisan migrate --force
sudo -u phenix php artisan config:cache
sudo -u phenix php artisan route:cache
sudo -u phenix php artisan view:cache
sudo supervisorctl restart phenix-worker:*
```

## Checklist avant mise en ligne

- [ ] Domaine pointe vers l'IP du VPS (DNS A record)
- [ ] `.env` configure avec les vrais identifiants
- [ ] APP_DEBUG=false
- [ ] FEDAPAY_ENV=live avec la vraie cle API
- [ ] SMTP configure et teste (envoyer un mail de test)
- [ ] Webhook FedaPay configure dans le dashboard FedaPay
- [ ] SSL actif (HTTPS)
- [ ] Compte admin cree
- [ ] Cron actif (scheduler)
- [ ] Supervisor actif (queue worker)
- [ ] `storage:link` fait (pour les affiches d'evenements)
- [ ] Faire un test d'achat complet en sandbox d'abord
