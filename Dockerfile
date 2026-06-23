# syntax=docker/dockerfile:1

# ============================================================
# Stage 1 — Build dos assets de frontend (Vite + Tailwind)
# ============================================================
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources

RUN npm run build


# ============================================================
# Stage 2 — Dependencias PHP (Composer, sem dev)
# ============================================================
FROM composer:2 AS vendor

WORKDIR /app

# Copia o codigo necessario para o composer rodar os scripts (package:discover)
COPY composer.json composer.lock ./
COPY artisan ./artisan
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY routes ./routes

# --no-scripts: o package:discover roda no entrypoint, quando os diretorios
# de storage ja existem e gravaveis. O autoloader continua otimizado.
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress \
    --no-scripts


# ============================================================
# Stage 3 — Imagem final (PHP-FPM + Nginx + Supervisor)
# ============================================================
FROM php:8.3-fpm-alpine AS app

# Pacotes do sistema
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash

# Extensoes PHP via helper oficial
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    pdo_sqlite \
    mbstring \
    bcmath \
    opcache \
    pcntl \
    zip

WORKDIR /var/www/html

# Codigo da aplicacao
COPY . .

# Dependencias e assets vindos dos stages anteriores
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Configuracoes de runtime
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Permissoes do Laravel
RUN mkdir -p storage/framework/cache/data \
             storage/framework/sessions \
             storage/framework/views \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
