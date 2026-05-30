FROM php:8.2-cli

RUN docker-php-ext-install mysqli

WORKDIR /app
COPY . .

# Use the PORT environment variable provided by Railway
CMD php -S 0.0.0.0:${PORT:-8080} -t .
