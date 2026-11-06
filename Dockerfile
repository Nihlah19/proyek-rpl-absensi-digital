# Production FrankenPHP image with PHP 8.2 Alpine
FROM dunglas/frankenphp:1-php8.2-alpine AS runtime

# Install production PHP extensions needed for AbsensiRFID
RUN install-php-extensions \
    pdo_mysql \
    gd \
    intl \
    zip \
    opcache \
    curl \
    mbstring

WORKDIR /app

# Copy application source code
COPY . /app

# Copy Caddy configuration to FrankenPHP
COPY Caddyfile /etc/frankenphp/Caddyfile

# Ensure writable directories for uploads and logs
RUN mkdir -p /app/web/public/uploads/pkl_masuk \
    && mkdir -p /app/web/public/uploads/pkl_pulang \
    && mkdir -p /app/web/storage/logs \
    && chown -R www-data:www-data /app \
    && chmod -R 775 /app/web/public/uploads \
    && chmod -R 775 /app/web/storage

ENV PORT=80 \
    APP_ENV=production

USER www-data

# Start FrankenPHP using custom Caddyfile
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
