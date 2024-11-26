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

# Создаем пользователя www-data
# Проверяем, существует ли пользователь www-data
if ! mysql -u root -h "$WORDPRESS_DB_HOST" -e "SELECT User FROM mysql.user WHERE User = 'www-data';" | grep -q www-data; then
  echo "User www-data does not exist. Creating..."
  mysql -u root -h "$WORDPRESS_DB_HOST" -e "CREATE USER 'www-data'@'%' IDENTIFIED BY '';"
else
  echo "User www-data already exists. Updating privileges..."
fi

mysql -u root -h "$WORDPRESS_DB_HOST" -e "GRANT ALL PRIVILEGES ON wordpress.* TO 'www-data'@'%'; FLUSH PRIVILEGES;"

# Создаем пользователя wordpress
# Проверяем, существует ли пользователь wordpress
if ! mysql -u root -h "$WORDPRESS_DB_HOST" -e "SELECT User FROM mysql.user WHERE User = '$WORDPRESS_DB_USER';" | grep -q wordpress; then
  echo "User $WORDPRESS_DB_USER does not exist. Creating..."
  mysql -u root -h "$WORDPRESS_DB_HOST" -e "CREATE USER '$WORDPRESS_DB_USER'@'%' IDENTIFIED BY '$WORDPRESS_DB_PASSWORD';"
else
  echo "User wordpress already exists. Updating privileges..."
fi

# Назначаем права для пользователя
mysql -u root -h "$WORDPRESS_DB_HOST" -e "GRANT ALL PRIVILEGES ON wordpress.* TO '$WORDPRESS_DB_USER'@'%'; FLUSH PRIVILEGES;"
echo "$WORDPRESS_DB_USER user in DB created"

wp config create --dbname="$WORDPRESS_DB_NAME" --dbuser="$WORDPRESS_DB_USER" --dbpass="$WORDPRESS_DB_PASSWORD" --dbhost="$WORDPRESS_DB_HOST" --allow-root
echo "WordPress config created"
wp core install --url="$WORDPRESS_URL" --title="$WORDPRESS_TITLE" --admin_user="$WORDPRESS_ADMIN_USER" --admin_password="$WORDPRESS_ADMIN_PASSWORD" --admin_email="$WORDPRESS_ADMIN_EMAIL" --skip-email --allow-root
echo "WordPress core installed"

# Копируем тему
cp -r /var/www/theme/* /var/www/html
echo "Copied initial theme"


# Удаляем стандартную категорию и пост
wp term get category 1 && wp term delete category 1
wp post get 1 && wp post delete 1

# Выводим сообщение об успешной установке
echo "Great. You can now log into WordPress at: $WORDPRESS_URL/wp-admin"
