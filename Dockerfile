# Usamos la imagen oficial de PHP 8 con Apache
FROM php:8.2-apache

# Instalar extensiones necesarias y utilidades
RUN apt-get update && apt-get install -y \
        git unzip zip curl \
    && docker-php-ext-install pdo pdo_mysql

# Instalar Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copiar todo el proyecto al directorio de Apache
COPY . /var/www/html/

# Dar permisos adecuados
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Ir al directorio del proyecto
WORKDIR /var/www/html

# Instalar dependencias de Composer
RUN composer install --no-dev --optimize-autoloader

# Exponer el puerto 80 para HTTP
EXPOSE 80

# Iniciar Apache en primer plano
CMD ["apache2-foreground"]

