#!/bin/bash
# Отладка
echo "🔍 Entrypoint started at $(date)"
echo "🔍 Current directory: $(pwd)"
echo "🔍 User: $(whoami)"
echo "🔍 db User: $DB_USERNAME"
echo "🔍 db pass: $DB_PASSWORD"

until nc -z -v $DB_HOST $DB_PORT; do
          >&2 echo "Conteiner_A is unavailable - sleeping"
            sleep 1
done
php artisan migrate

echo "✅ Entrypoint finished, launching main process..."
exec "$@"
