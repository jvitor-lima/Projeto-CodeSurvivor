#!/usr/bin/env bash
set -e

cd /var/www/html

# Garante que o arquivo .env exista (usa o .env.docker como base para producao)
if [ ! -f .env ]; then
    echo "[entrypoint] .env nao encontrado, criando a partir de .env.docker"
    cp .env.docker .env
fi

# Garante uma APP_KEY valida
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "[entrypoint] Gerando APP_KEY"
    php artisan key:generate --force
fi

# Garante permissoes corretas nos diretorios graváveis
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Limpa e reconstroi os caches (com as variaveis de ambiente ja presentes)
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[entrypoint] Aplicacao pronta. Iniciando servicos..."

exec "$@"
