FROM dunglas/frankenphp:1-php8.4-alpine

# 1. Extensions PHP indispensables
RUN install-php-extensions pdo_mysql intl zip opcache bcmath

# 2. Ingestion de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 3. Copie du code source
COPY . .

# Coolify passe déjà certaines valeurs réelles (ex. DATABASE_URL) en --build-arg ;
# on les récupère ici pour qu'elles soient utilisées à la place des valeurs
# factices ci-dessous quand elles sont disponibles.
ARG DATABASE_URL=mysql://dummy:dummy@127.0.0.1:3306/dummy

# 4. Variables d'environnement factices nécessaires pendant le build
# ⚠️ Ce sont des valeurs de repli UNIQUEMENT pour que composer/bin console
# puissent tourner pendant le build. Les vraies valeurs de prod DOIVENT être
# définies comme variables d'environnement réelles dans Coolify (dashboard de
# l'app) — Coolify les injecte au démarrage du conteneur et elles remplacent
# automatiquement tout ce qui est baked ici.
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=prod
ENV APP_SECRET=a03b61e2abb6ed896a22139bad3732f0
ENV APP_NAME="Claps"
ENV MAILER_SENDER_NAME="Claps"
ENV MAILER_SENDER_ADDR="no-reply@claps.be"
ENV MAILER_DSN="null://null"
ENV DATABASE_URL=$DATABASE_URL
ENV TRUSTED_PROXIES="127.0.0.1,REMOTE_ADDR"
ENV TRUSTED_HOSTS="^beta\.claps\.be$"
ENV SERVER_NAME=":80"

# 5. Création d'un fichier .env minimal si absent pour éviter tout crash de Dotenv
RUN if [ ! -f .env ]; then echo "APP_ENV=prod" > .env; fi

# 6. Installation des dépendances Composer
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --optimize-autoloader --no-scripts

# 7. Dump des variables d'environnement en PHP compilé (.env.local.php)
RUN composer dump-env prod

# 8. Répertoires et attribution complète des droits à www-data
RUN mkdir -p var/cache var/log public/build \
    && chown -R www-data:www-data /app

# 9. Pré-chauffage du cache + publication des assets des bundles (EasyAdmin...)
# sous l'utilisateur www-data. --no-scripts en étape 6 a désactivé les
# auto-scripts Composer (cache:clear + assets:install) : on les refait ici
# explicitement, dans l'ordre, avec des erreurs qui font échouer le build au
# lieu de livrer silencieusement une image sans assets.
USER www-data
RUN php bin/console cache:warmup --env=prod
RUN php bin/console assets:install public --env=prod

USER root