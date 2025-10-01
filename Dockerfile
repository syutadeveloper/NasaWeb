FROM php:8.1-apache

# Включаем mod_rewrite для ЧПУ
RUN a2enmod rewrite

# Устанавливаем расширения PHP для работы с MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Устанавливаем Composer напрямую (без composer:2)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/bin --filename=composer \
    && rm composer-setup.php

# Указываем рабочую директорию
WORKDIR /var/www/html

# Копируем проект внутрь контейнера
COPY . /var/www/html

# Даем права на папку (чтобы Apache имел доступ)
RUN chown -R www-data:www-data /var/www/html

# Настройка Apache для .htaccess
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
    </Directory>' > /etc/apache2/conf-available/override.conf \
    && a2enconf override
