FROM php:8.2-fpm

# Installer les dépendances d'OS nécessaires pour l'extension pdo_pgsql
RUN apt-get update && apt-get install -y libpq-dev zip unzip \
    && docker-php-ext-install pdo pdo_pgsql opcache

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Utilisateur non-root (Gestion de l'utilisateur existant www-data)
RUN id -u www-data &>/dev/null && usermod -u 1000 www-data || useradd -u 1000 -m www-data
USER www-data

WORKDIR /var/www

CMD ["php-fpm"]
