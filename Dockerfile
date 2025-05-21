# Usar la imagen oficial de PHP con Apache
FROM php:8.2-apache

# Habilitar módulos necesarios
RUN a2enmod rewrite headers

# Copiar los archivos al directorio correcto
COPY ./app /var/www/html/

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html

# Configurar Apache para usar el puerto que Render espera (10000)
ENV APACHE_RUN_PORT 10000
EXPOSE 10000

# Health check para Render
HEALTHCHECK --interval=30s --timeout=30s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:10000/ || exit 1