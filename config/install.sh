#!/usr/bin/env sh

set -e

mysql_ready="nc -z $WORDPRESS_DB_HOST 3306"

if ! $mysql_ready
then
    printf 'Waiting for MySQL.'
    while ! $mysql_ready
    do
        printf '.'
        sleep 1
    done
    echo
fi

if wp core is-installed
then
    echo "WordPress is already installed, exiting."
    exit
fi

wp core download --force --version=5.4

[ -f wp-config.php ] || wp config create \
    --dbhost="$WORDPRESS_DB_HOST" \
    --dbname="$WORDPRESS_DB_NAME" \
    --dbuser="$WORDPRESS_DB_USER" \
    --dbpass="$WORDPRESS_DB_PASSWORD"

wp core install \
    --url="$WORDPRESS_URL" \
    --title="$WORDPRESS_TITLE" \
    --admin_user="$WORDPRESS_ADMIN_USER" \
    --admin_password="$WORDPRESS_ADMIN_PASSWORD" \
    --admin_email="$WORDPRESS_ADMIN_EMAIL" \
    --skip-email

cp -r /opt/yootheme /var/www/html/wp-content/themes
cp -r /opt/default-theme /var/www/html/wp-content/themes

wp theme activate yootheme
wp theme delete twentysixteen twentyseventeen twentynineteen twentytwenty

wp config set WP_AUTO_UPDATE_CORE false

wp plugin delete akismet hello
wp plugin install --force --activate https://github.com/polylang/polylang/archive/2.7.1.zip
wp plugin install --force --activate https://downloads.wordpress.org/plugin/2fas-light.zip
wp plugin install --force --activate https://github.com/Yoast/wordpress-seo/archive/13.4.zip

wp term delete category 1
wp post delete 1

echo "Great. You can now log into WordPress at: $WORDPRESS_URL/wp-admin"
