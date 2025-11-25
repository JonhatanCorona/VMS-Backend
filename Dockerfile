# Usamos la imagen oficial de PHP 8 con Apache
FROM php:8.2-apache

# Habilitar extensiones necesarias, por ejemplo PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copiar todo el proyecto al directorio de Apache
COPY . /var/www/html/

# Dar permisos adecuados (opcional, dependiendo del proyecto)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exponer el puerto 80 para HTTP
EXPOSE 80

# Iniciar Apache en primer plano
CMD ["apache2-foreground"]
