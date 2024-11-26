#!/bin/bash
set -e

# Ожидание запуска базы данных
while ! mysqladmin ping -h"$WORDPRESS_DB_HOST" --silent; do
  echo 'Waiting for database...'
  sleep 1
done

# Если WordPress уже установлен, выходим
if wp core is-installed --allow-root; then
  echo "WordPress is already installed."
  exit 0
fi

# Установка WordPress
wp core download --allow-root
wp config create --dbname="$WORDPRESS_DB_NAME" --dbuser="$WORDPRESS_DB_USER" --dbpass="$WORDPRESS_DB_PASSWORD" --dbhost="$WORDPRESS_DB_HOST" --allow-root
wp core install --url="$WORDPRESS_URL" --title="$WORDPRESS_TITLE" --admin_user="$WORDPRESS_ADMIN_USER" --admin_password="$WORDPRESS_ADMIN_PASSWORD" --admin_email="$WORDPRESS_ADMIN_EMAIL" --skip-email --allow-root

# Удаляем стандартную категорию и пост
wp term get category 1 && wp term delete category 1 || true
wp post get 1 && wp post delete 1 || true

# Выводим сообщение об успешной установке
echo "Great. You can now log into WordPress at: $WORDPRESS_URL/wp-admin"
