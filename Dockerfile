FROM php:8.5-apache

# Install ekstensi pdo_mysql untuk keamanan query (Prepared Statements)
RUN docker-php-ext-install pdo pdo_mysql

# Aktifkan mod_rewrite untuk mendukung URL yang rapi
RUN a2enmod rewrite

# Tentukan working directory
WORKDIR /var/www/html

# Mengubah kepemilikan folder ke user www-data (Apache) agar bisa melakukan upload file
RUN chown -R www-data:www-data /var/www/html