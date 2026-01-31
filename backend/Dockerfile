# WordPress + WooCommerce 跨境电商后端
# 支持 ARM64 和 AMD64 架构

FROM --platform=$TARGETPLATFORM wordpress:6.4-php8.2-apache

# 设置工作目录
WORKDIR /var/www/html

# 安装必要的 PHP 扩展和工具
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    curl \
    && docker-php-ext-install zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 安装 WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

# 复制自定义配置
COPY wp-config-docker.php /usr/src/wordpress/wp-config-docker.php
COPY uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# 复制初始化脚本
COPY docker-entrypoint-init.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint-init.sh

# 复制自定义主题和插件
COPY themes/ /var/www/html/wp-content/themes/
COPY plugins/ /var/www/html/wp-content/plugins/

# 设置权限
RUN chown -R www-data:www-data /var/www/html

# 暴露端口
EXPOSE 80

# 启动命令
CMD ["apache2-foreground"]
