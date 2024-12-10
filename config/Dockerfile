FROM wordpress:php7.3-fpm

RUN mv "$PHP_INI_DIR"/php.ini-development "$PHP_INI_DIR"/php.ini
RUN sed -i.bak '/^ *post_max_size/s/=.*/= 64M/' "$PHP_INI_DIR"/php.ini
RUN sed -i.bak '/^ *upload_max_filesize/s/=.*/= 64M/' "$PHP_INI_DIR"/php.ini

# install_wordpress.sh & misc. dependencies
RUN apt-get update; \
	apt-get install -yq mariadb-client netcat sudo less unzip git

# wp-cli
RUN curl -sL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o wp; \
	chmod +x wp; \
	mv wp /usr/local/bin/; \
	mkdir /var/www/.wp-cli; \
	chown www-data:www-data /var/www/.wp-cli

# composer
RUN curl -sL https://raw.githubusercontent.com/composer/getcomposer.org/master/web/installer | php; \
	mv composer.phar /usr/local/bin/composer; \
	mkdir /var/www/.composer; \
	chown www-data:www-data /var/www/.composer

RUN sudo -u www-data composer global config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true

# phpunit, phpcs, wpcs
RUN sudo -u www-data composer global require \
	phpunit/phpunit \
	dealerdirect/phpcodesniffer-composer-installer \
	phpcompatibility/phpcompatibility-wp \
	automattic/vipwpcs

# include composer-installed executables in $PATH
ENV PATH="/var/www/.composer/vendor/bin:${PATH}"

ADD --chown=www-data ./install.sh /usr/local/bin/install_wordpress
COPY --chown=www-data ./domain /opt/yootheme
COPY --chown=www-data ./theme /opt/default-theme
