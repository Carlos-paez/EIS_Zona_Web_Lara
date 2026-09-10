# ============================================================
# EIS System — Contenedor web (PHP 8.3 + Apache 2.4)
# ============================================================
FROM php:8.3-apache

# Dependencias + extensiones requeridas por la app:
#   - pdo_mysql : conexión PDO a MySQL (obligatoria)
#   - mbstring  : usada por Validator, Model, PdfBuilder, controllers
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Copia el vhost personalizado (DocumentRoot = src/ + URLs limpias)
# Define el DocumentRoot de Apache dentro del contenedor
COPY docker/apache/000-default-eis.conf /etc/apache2/sites-available/000-default.conf

# Código de la aplicación
COPY . /var/www/html

# Permisos: Apache (www-data) debe poder escribir logs y sesiones
RUN mkdir -p /var/www/html/src/logs \
    && chown -R www-data:www-data /var/www/html

# Directorio del documento raíz configurado
WORKDIR /var/www/html/src

EXPOSE 80