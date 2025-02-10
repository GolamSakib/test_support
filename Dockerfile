FROM php:7.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html/support

# Copy project files
COPY . .

# Install dependencies
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# Set permissions
RUN chown -R www-data:www-data /var/www/html/support

CMD ["php-fpm"]
