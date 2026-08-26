FROM dunglas/frankenphp:1-php8.4-alpine

# 1. Extensions PHP indispensables
RUN install-php-extensions pdo_mysql intl zip opcache bcmath

# 2. Ingestion de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 3. Copie du code source
COPY . .

# 4. Variables d'environnement factices nécessaires pendant le build
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=prod
ENV APP_SECRET=ad8cf8588af84194be6905c7165352e2
ENV APP_NAME="Claps"
ENV MAILER_SENDER_NAME="Claps"
ENV MAILER_SENDER_ADDR="no-reply@claps.be"
ENV MAILER_DSN="null://null"
ENV DATABASE_URL="mysql://dummy:dummy@127.0.0.1:3306/dummy"
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

# 9. Pré-chauffage du cache sous l'utilisateur www-data
USER www-data
RUN php bin/console cache:warmup --env=prod

USER root