FROM php:8.3-fpm

WORKDIR /var/www

ENV DEBIAN_FRONTEND=noninteractive

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    supervisor \
    cron \
    build-essential \
    cmake \
    pkg-config \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    ffmpeg \
    python3 \
    python3-pip \
    python3-dev \
    libblas-dev \
    liblapack-dev \
    libatlas3-base \
    libx11-dev \
    libgtk-3-dev \
    libboost-python3-dev \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# Configurar e instalar extensões PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip

# Instalar Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Instalar pacotes Python
COPY requirements-python.txt /tmp/requirements-python.txt
RUN pip3 install --no-cache-dir --break-system-packages -r /tmp/requirements-python.txt

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar aplicação
COPY . /var/www
COPY --chown=www-data:www-data . /var/www

USER www-data
EXPOSE 9000
CMD ["php-fpm"]
