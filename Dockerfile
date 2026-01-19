FROM php:8.4-alpine

# Copy the custom INI file into the PHP configuration directory
COPY ./php-overrides.ini /usr/local/etc/php/conf.d/

# ... other Dockerfile instructions ...

RUN echo "upload_max_filesize = 300M" >> /usr/local/etc/php/conf.d/0-upload_large_dumps.ini \
&& echo "post_max_size = 500M" >> /usr/local/etc/php/conf.d/0-upload_large_dumps.ini \
&& echo "memory_limit = 4G" >> /usr/local/etc/php/conf.d/0-upload_large_dumps.ini \
&& echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/0-upload_large_dumps.ini \
&& echo "max_input_vars = 10000" >> /usr/local/etc/php/conf.d/0-upload_large_dumps.ini
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli
COPY ./src/ /var/www/html/
RUN a2enmod rewrite && a2enmod ssl && a2enmod socache_shmcb
RUN sed -i '/SSLCertificateFile.*snakeoil\.pem/c\SSLCertificateFile \/etc\/ssl\/certs\/mycert.crt' /etc/apache2/sites-available/default-ssl.conf
RUN sed -i '/SSLCertificateKeyFile.*snakeoil\.key/cSSLCertificateKeyFile /etc/ssl/private/mycert.key\' /etc/apache2/sites-available/default-ssl.conf
RUN a2ensite default-ssl
