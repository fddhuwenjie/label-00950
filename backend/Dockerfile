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

# 安装 WP-CLI (确保安装成功，否则构建失败)
RUN curl -fsSL -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    || curl -fsSL -o /usr/local/bin/wp https://github.com/wp-cli/wp-cli/releases/download/v2.9.0/wp-cli-2.9.0.phar \
    || (echo "WP-CLI installation failed" && exit 1) ; \
    chmod +x /usr/local/bin/wp && \
    wp --info --allow-root || (echo "WP-CLI verification failed" && exit 1)

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
