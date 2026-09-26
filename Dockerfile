FROM php:8.2-apache

# Salin seluruh file proyek ke web root Apache
COPY . /var/www/html/

# Ubah port default Apache agar mengikuti port yang diberikan oleh Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

RUN chown -R www-data:www-data /var/www/html
