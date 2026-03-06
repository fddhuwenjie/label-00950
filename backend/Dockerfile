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

# 预下载 WooCommerce 插件包（避免运行时 TLS 下载失败）
RUN mkdir -p /tmp/wc-download && \
    curl -fSL -o /tmp/wc-download/woocommerce.zip https://downloads.wordpress.org/plugin/woocommerce.10.5.3.zip && \
    unzip -q /tmp/wc-download/woocommerce.zip -d /usr/src/wordpress/wp-content/plugins/ && \
    rm -rf /tmp/wc-download

# 安装 Composer 并安装 firebase/php-jwt
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY plugins/cross-border-commerce/composer.json /tmp/jwt-deps/composer.json
RUN cd /tmp/jwt-deps && composer install --no-dev --optimize-autoloader \
    && mkdir -p /usr/src/wordpress/wp-content/plugins/cross-border-commerce/vendor \
    && cp -r /tmp/jwt-deps/vendor/* /usr/src/wordpress/wp-content/plugins/cross-border-commerce/vendor/ \
    && rm -rf /tmp/jwt-deps

# 复制自定义配置
COPY wp-config-docker.php /usr/src/wordpress/wp-config-docker.php
COPY uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# 复制自定义插件到 WordPress 源目录（entrypoint 会复制到 /var/www/html）
COPY plugins/cross-border-commerce/ /usr/src/wordpress/wp-content/plugins/cross-border-commerce/

# 复制商品图片到 WordPress web 根目录（WordPress entrypoint 不会自动复制自定义目录）
COPY images/products/ /opt/product-images/

# 复制初始化脚本
COPY docker-entrypoint-init.sh /usr/local/bin/custom-init.sh
RUN chmod +x /usr/local/bin/custom-init.sh

# 创建自定义 entrypoint：先执行 WordPress 官方 entrypoint 复制文件，再运行初始化，最后启动 Apache
# WordPress entrypoint 需要 $1 = apache2-foreground 才会复制文件
# 策略：用自定义 apache2-foreground 脚本替换，先运行初始化再启动真正的 Apache
RUN mv /usr/local/bin/docker-entrypoint.sh /usr/local/bin/docker-entrypoint-original.sh

# 创建一个假的 apache2-foreground，WordPress entrypoint 会 exec 它
# 在这个脚本里先运行初始化，再 exec 真正的 apache2-foreground
COPY <<'WRAPPER' /usr/local/bin/custom-apache2-foreground
#!/bin/bash
set -e
echo "=== WordPress 文件复制完成，开始自定义初始化 ==="
# 运行初始化脚本（同步执行，确保完成后再启动 Apache）
/usr/local/bin/custom-init.sh || echo "初始化脚本出错，继续启动 Apache"
echo "=== 启动 Apache ==="
exec /usr/local/bin/real-apache2-foreground
WRAPPER
RUN chmod +x /usr/local/bin/custom-apache2-foreground

# 备份真正的 apache2-foreground
RUN cp /usr/local/bin/apache2-foreground /usr/local/bin/real-apache2-foreground

# 替换 apache2-foreground 为我们的自定义版本
RUN cp /usr/local/bin/custom-apache2-foreground /usr/local/bin/apache2-foreground

# 恢复原始 entrypoint
RUN cp /usr/local/bin/docker-entrypoint-original.sh /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

CMD ["apache2-foreground"]
