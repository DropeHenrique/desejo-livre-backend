# -------------------------
# Stage 0: PHP + dependências do sistema
# -------------------------
FROM php:8.3-fpm

# Define o diretório de trabalho
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
    libboost-dev \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP necessárias (exemplo)
RUN docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl bcmath gd

# -------------------------
# Stage 1: Composer
# -------------------------
FROM composer:latest

WORKDIR /var/www

# Copia os arquivos do projeto (ajuste se quiser excluir arquivos específicos)
COPY --from=0 /var/www /var/www

# Instalar dependências Python adicionais se necessário
# RUN python3 -m pip install --no-cache-dir nome-do-pacote

# -------------------------
# Entrypoint (opcional)
# -------------------------
CMD ["php-fpm"]
