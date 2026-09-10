# ==============================================================================
# Stage 1: Frontend Asset Compilation (Svelte 5 + Tailwind v4 + Vite)
# ==============================================================================
FROM node:22-alpine AS frontend-builder
WORKDIR /app

# Install npm dependencies first for layer caching
COPY package.json package-lock.json* ./
RUN npm ci --prefer-offline --no-audit

# Copy frontend source files
COPY vite.config.js* vite.config.ts* svelte.config.js* tsconfig.json* tailwind.config.* ./
COPY resources ./resources

# Compile production bundles to public/build
ENV SKIP_WAYFINDER=1
RUN npm run build

# ==============================================================================
# Stage 2: Composer Dependency Installation
# ==============================================================================
FROM composer:2.8 AS backend-builder
WORKDIR /app

COPY composer.json composer.lock* ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-autoloader \
    --no-scripts \
    --prefer-dist \
    --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev

# ==============================================================================
# Stage 3: Hardened Production Runtime (PHP 8.4-FPM + Nginx on Alpine)
# ==============================================================================
FROM php:8.4-fpm-alpine AS production

LABEL org.opencontainers.image.title="Suchak - Core Web & Worker App"
LABEL org.opencontainers.image.description="Production web and worker runtime for Suchak (Laravel 13 + Inertia Svelte 5)"
LABEL org.opencontainers.image.source="https://github.com/org/suchak"

WORKDIR /var/www/html

# Install system runtime libraries, Nginx, and build dependencies for PECL
RUN apk add --no-cache \
    nginx \
    curl \
    git \
    libpq \
    libpq-dev \
    libzip-dev \
    icu-dev \
    icu-libs \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        intl \
        zip \
        bcmath \
        pcntl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del libpq-dev libzip-dev icu-dev oniguruma-dev $PHPIZE_DEPS \
    && rm -rf /tmp/pear

# Copy PHP and Nginx configurations
COPY docker/nginx/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-production.ini

# Copy application source code
COPY . /var/www/html

# Copy pre-compiled dependencies and frontend assets from previous stages
COPY --from=backend-builder /app/vendor /var/www/html/vendor
COPY --from=frontend-builder /app/public/build /var/www/html/public/build

# Setup storage and cache directories with proper permissions
RUN mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy entrypoint script and make executable
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Environment defaults
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV CONTAINER_ROLE=web

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://127.0.0.1/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
