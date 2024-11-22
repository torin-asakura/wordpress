FROM wordpress:php8.2-fpm

# Настройка PHP
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" && \
    sed -i.bak '/^ *post_max_size/s/=.*/= 64M/' "$PHP_INI_DIR/php.ini" && \
    sed -i.bak '/^ *upload_max_filesize/s/=.*/= 64M/' "$PHP_INI_DIR/php.ini"

# Установка зависимостей
RUN apt-get update && \
    apt-get install -yq mariadb-client netcat-traditional sudo less unzip git wget && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка WP-CLI
RUN curl -sL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp && \
    chmod +x /usr/local/bin/wp && \
    mkdir /var/www/.wp-cli && \
    chown www-data:www-data /var/www/.wp-cli

# Копирование скриптов
COPY ./install.sh /usr/local/bin/install_wordpress
RUN chmod +x /usr/local/bin/install_wordpress

# Запуск
CMD ["php-fpm"]
