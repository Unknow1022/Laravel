FROM php:8.2-apache

# Instalar dependencias del sistema requeridas
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    curl

# Limpiar caché de paquetes
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP necesarias para Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Copiar Composer desde la imagen oficial
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Directorio de trabajo en el contenedor
WORKDIR /var/www

# Copiar archivos del proyecto
COPY . /var/www

# Instalar dependencias de Composer (producción)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Configurar Apache para apuntar al directorio public de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Habilitar mod_rewrite de Apache para Laravel (.htaccess)
RUN a2enmod rewrite

# Ajustar permisos de almacenamiento y caché
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Puerto expuesto por Apache
EXPOSE 80

# Iniciar servidor Apache
CMD ["apache2-foreground"]
