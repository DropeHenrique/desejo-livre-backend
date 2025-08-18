FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    supervisor \
    cron \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    ffmpeg \
    python3 \
    python3-pip \
    python3-dev \
    build-essential \
    cmake \
    libblas-dev \
    liblapack-dev \
    libatlas-base-dev \
    libx11-dev \
    libgtk-3-dev \
    libboost-python-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Python packages for facial recognition
COPY requirements-python.txt /tmp/requirements-python.txt
RUN pip3 install --no-cache-dir -r /tmp/requirements-python.txt

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www

# Change current user to www-data
USER www-data

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
