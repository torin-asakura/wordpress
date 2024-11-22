#!/usr/bin/env sh

set -e

# Проверяем доступность MySQL
while ! mysqladmin ping -h"$WORDPRESS_DB_HOST" --silent; do
  echo 'Waiting for database...'
  sleep 1
done

# Проверяем, установлен ли WordPress
if wp core is-installed; then
  echo "WordPress is already installed, exiting."
  exit 0
fi

# Скачиваем WordPress
wp core download --force --version="${WORDPRESS_VERSION:-latest}"

# Создаём конфигурацию, если её нет
[ -f wp-config.php ] || wp config create \
  --dbhost="$WORDPRESS_DB_HOST" \
  --dbname="$WORDPRESS_DB_NAME" \
  --dbuser="$WORDPRESS_DB_USER" \
  --dbpass="$WORDPRESS_DB_PASSWORD"

# Устанавливаем WordPress
wp core install \
  --url="$WORDPRESS_URL" \
  --title="$WORDPRESS_TITLE" \
  --admin_user="$WORDPRESS_ADMIN_USER" \
  --admin_password="$WORDPRESS_ADMIN_PASSWORD" \
  --admin_email="$WORDPRESS_ADMIN_EMAIL" \
  --skip-email

# Копируем темы, если они существуют
[ -d /opt/yootheme ] && cp -r /opt/yootheme /var/www/html/wp-content/themes
[ -d /opt/default-theme ] && cp -r /opt/default-theme /var/www/html/wp-content/themes

# Активируем тему
wp theme activate yootheme

# Удаляем стандартные темы
wp theme list --field=name | grep -E 'twenty' | xargs -I {} wp theme delete {}

# Отключаем автообновления
wp config set WP_AUTO_UPDATE_CORE false

# Удаляем стандартные плагины и устанавливаем свои
wp plugin delete akismet hello
wp plugin install --force --activate polylang
wp plugin install --force --activate wordpress-seo

# Удаляем стандартную категорию и пост
wp term get category 1 && wp term delete category 1 || true
wp post get 1 && wp post delete 1 || true

# Выводим сообщение об успешной установке
echo "Great. You can now log into WordPress at: $WORDPRESS_URL/wp-admin"
