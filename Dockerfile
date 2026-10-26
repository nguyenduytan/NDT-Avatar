FROM node:24-alpine AS web-build
WORKDIR /build
ENV NEXT_TELEMETRY_DISABLED=1
COPY web/package*.json ./
RUN npm ci
COPY web/ ./
RUN npm run build

FROM php:8.4-fpm-bookworm AS api
RUN apt-get update && apt-get install -y --no-install-recommends libfreetype6-dev libpng-dev libwebp-dev libicu-dev libonig-dev libfcgi-bin \
    && docker-php-ext-configure gd --with-freetype --with-webp \
    && docker-php-ext-install -j$(nproc) gd intl mbstring opcache \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY api/ /app/
COPY docker/php.ini /usr/local/etc/php/conf.d/ndt.ini
COPY docker/pool.conf /usr/local/etc/php-fpm.d/zz-ndt.conf
USER www-data
EXPOSE 9000
HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD SCRIPT_FILENAME=/app/public/index.php REQUEST_METHOD=GET REQUEST_URI=/health cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -q '"status":"ok"' || exit 1
CMD ["php-fpm"]

FROM nginx:stable-alpine AS gateway
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=web-build /build/out /usr/share/nginx/html
HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD wget -q -O /dev/null http://127.0.0.1:8080/health || exit 1
EXPOSE 8080
