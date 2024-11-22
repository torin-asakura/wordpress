FROM wordpress:php8.2-fpm

# Настройка PHP
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" && \
    sed -i.bak '/^ *post_max_size/s/=.*/= 64M/' "$PHP_INI_DIR/php.ini" && \
    sed -i.bak '/^ *upload_max_filesize/s/=.*/= 64M/' "$PHP_INI_DIR/php.ini"

# Установка зависимостей
RUN apt-get update && \
    apt-get install -yq mariadb-client netcat sudo less unzip git && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка WP-CLI
RUN curl -sL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp && \
    chmod +x /usr/local/bin/wp && \
    mkdir /var/www/.wp-cli && \
    chown www-data:www-data /var/www/.wp-cli

# Установка Composer
RUN curl -sL https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && \
    mkdir /var/www/.composer && \
    chown www-data:www-data /var/www/.composer

# Установка инструментов разработки
RUN sudo -u www-data composer global require \
    phpunit/phpunit \
    dealerdirect/phpcodesniffer-composer-installer \
    phpcompatibility/phpcompatibility-wp \
    automattic/vipwpcs && \
    echo 'export PATH="/var/www/.composer/vendor/bin:$PATH"' >> /etc/bash.bashrc

# Копирование скриптов и тем
COPY --chown=www-data ./install.sh /usr/local/bin/install_wordpress
COPY --chown=www-data ./domain /opt/yootheme
COPY --chown=www-data ./theme /opt/default-theme

# Установка прав
RUN chmod +x /usr/local/bin/install_wordpress
