FROM dunglas/frankenphp:1-php8.4-alpine

# 1. Installation des extensions PHP pré-compilées (instantané, évite la compilation C++)
RUN install-php-extensions pdo_mysql intl zip opcache bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copie des fichiers
COPY . .

# Dépendances Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_NAME="Claps"
ENV APP_ENV=prod
ENV APP_SECRET=ad8cf8588af84194be6905c7165352e2
ENV MAILER_SENDER_NAME="Claps"
ENV MAILER_SENDER_ADDR="no-reply@claps.be"
ENV MAILER_DSN="null://null"
ENV DATABASE_URL="mysql://dummy:dummy@127.0.0.1:3306/dummy"
ENV TRUSTED_PROXIES="46.224.115.2, 127.0.0.1,REMOTE_ADDR"

# 5. Installation des dépendances SANS exécuter les scripts post-install
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --optimize-autoloader --no-scripts

# 6. Création des répertoires et permissions pour www-data
RUN mkdir -p var/cache var/log public/build \
    && chown -R www-data:www-data /app
USER www-data

RUN composer dump-env prod
# Pré-chauffage du cache
RUN php bin/console cache:warmup --env=prod

ENV SERVER_NAME=":80"
USER root