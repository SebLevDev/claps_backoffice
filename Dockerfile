FROM dunglas/frankenphp

# Installation des extensions nécessaires
RUN install-php-extensions pdo_mysql intl zip opcache bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copie des fichiers
COPY . .

# Dépendances Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction --optimize-autoloader

# Permissions Symfony propres (FrankenPHP s'exécute sous www-data par défaut en prod)
RUN mkdir -p var/cache var/log && chown -R www-data:www-data var

USER www-data

# Pré-chauffage du cache
RUN php bin/console cache:warmup --env=prod

USER root