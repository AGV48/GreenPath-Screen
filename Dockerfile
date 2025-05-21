# Usar la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Habilitar el módulo rewrite de Apache para URLs amigables
RUN a2enmod rewrite

# Copiar los archivos de la aplicación al contenedor
COPY app/ /var/www/html/

# Establecer los permisos adecuados
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto 80
EXPOSE 80