# Etapa 0: PHP FPM
FROM php:8.3-fpm

# Definir diretório de trabalho
WORKDIR /var/www

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
    libboost-python3-1.81-dev \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# Etapa 1: Composer
FROM composer:latest

# Definir diretório de trabalho
WORKDIR /var/www

# Copiar arquivos do projeto para dentro do container
COPY --from=0 /var/www /var/www

# Expor porta do PHP-FPM
EXPOSE 9000

# Comando padrão ao iniciar container
CMD ["php-fpm"]
