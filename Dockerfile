FROM php:8.3.10-fpm

# Install system dependencies
RUN apt-get update -y && apt-get install -y \
    curl zip unzip git \
    libpq-dev \
    nginx \
    supervisor

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql

# Set working directory
WORKDIR /var/www/html

# Copy Laravel app (semua isi project Laravel kamu)
COPY . /var/www/html

# Copy Nginx config
COPY nginx/default.conf /etc/nginx/sites-available/default

# Copy Supervisor config
COPY supervisord.conf /etc/supervisord.conf

# Copy startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
