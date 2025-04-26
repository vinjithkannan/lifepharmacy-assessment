FROM php:8.2-fpm
ENV APP_DIR=/var/www/html
# Set working directory
WORKDIR $APP_DIR

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    libtool \
    libxml2-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    git \
    unzip \
    curl

# Install PECL extensions
RUN pecl install redis xdebug

# Enable PECL extensions
RUN docker-php-ext-enable redis xdebug

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install \
    bcmath \
    calendar \
    curl \
    exif \
    gd \
    iconv \
    intl \
    mbstring \
    pdo \
    pdo_mysql \
    pcntl \
    soap \
    xml \
    zip

# Install Composer globally
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy existing application directory contents
COPY .. $APP_DIR
#RUN chmod -R 777 $APP_DIR/storage
#RUN chmod -R 777 $APP_DIR/bootstrap/cache
# Expose port 9000 for php-fpm
EXPOSE 9000

# Start php-fpm server
CMD ["php-fpm"]
