# WordPress + WooCommerce 跨境电商后端
# 支持 ARM64 和 AMD64 架构

FROM --platform=$TARGETPLATFORM wordpress:6.8-php8.2-apache

WORKDIR /var/www/html

# 安装必要的 PHP 扩展和工具
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    curl \
    default-mysql-client \
    && docker-php-ext-install zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 安装 WP-CLI
RUN curl -fsSL -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    || curl -fsSL -o /usr/local/bin/wp https://github.com/wp-cli/wp-cli/releases/download/v2.9.0/wp-cli-2.9.0.phar \
    || (echo "WP-CLI installation failed" && exit 1) ; \
    chmod +x /usr/local/bin/wp && \
    wp --info --allow-root || (echo "WP-CLI verification failed" && exit 1)

# 复制自定义配置
COPY wp-config-docker.php /usr/src/wordpress/wp-config-docker.php
COPY uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# 复制自定义插件到 WordPress 源目录（entrypoint 会复制到 /var/www/html）
COPY plugins/cross-border-commerce/ /usr/src/wordpress/wp-content/plugins/cross-border-commerce/

# 复制初始化脚本
COPY docker-entrypoint-init.sh /usr/local/bin/custom-init.sh
RUN chmod +x /usr/local/bin/custom-init.sh

# 创建自定义 entrypoint：先执行 WordPress 官方 entrypoint，再运行初始化
# WordPress entrypoint 需要 $1 = apache2-foreground 才会复制文件
# 我们的策略：包装 entrypoint，在 WordPress entrypoint 执行后、Apache 启动前运行初始化
RUN mv /usr/local/bin/docker-entrypoint.sh /usr/local/bin/docker-entrypoint-original.sh
COPY <<'WRAPPER' /usr/local/bin/docker-entrypoint.sh
#!/bin/bash
set -e
# 执行 WordPress 官方 entrypoint 的文件复制逻辑
# 但不让它 exec apache，而是先运行我们的初始化
# 方法：在后台运行初始化脚本，然后启动 Apache
/usr/local/bin/custom-init.sh &
exec /usr/local/bin/docker-entrypoint-original.sh "$@"
WRAPPER
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

CMD ["apache2-foreground"]
