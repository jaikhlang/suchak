#!/bin/sh
set -e

ROLE=${CONTAINER_ROLE:-web}

echo "Starting container with role: ${ROLE}..."

# Cache configuration, routes, and views if in production
if [ "${APP_ENV}" = "production" ]; then
    echo "Caching Laravel configuration and routes..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
    php artisan event:cache || true
fi

if [ "$ROLE" = "web" ]; then
    # Run database migrations automatically if enabled
    if [ "${AUTO_MIGRATE:-false}" = "true" ]; then
        echo "Running database migrations..."
        php artisan migrate --force
    fi

    echo "Starting PHP-FPM and Nginx..."
    php-fpm -D
    exec nginx -g "daemon off;"

elif [ "$ROLE" = "worker" ]; then
    echo "Starting Laravel Queue Worker..."
    exec php artisan queue:work redis \
        --queue=high,extraction,ocr,crawl,default,low \
        --sleep=3 \
        --tries=3 \
        --timeout=300 \
        --max-time=3600 \
        --max-jobs=1000

elif [ "$ROLE" = "horizon" ]; then
    echo "Starting Laravel Horizon..."
    exec php artisan horizon

elif [ "$ROLE" = "scheduler" ]; then
    echo "Starting Laravel Scheduler daemon..."
    while [ true ]; do
        php artisan schedule:run --verbose --no-interaction &
        sleep 60
    done

else
    echo "Executing custom command: $@"
    exec "$@"
fi
