#!/bin/bash
set -e

# 等待数据库就绪
until mysql -h"$WORDPRESS_DB_HOST" -u"$WORDPRESS_DB_USER" -p"$WORDPRESS_DB_PASSWORD" -e "SELECT 1" > /dev/null 2>&1; do
    echo "等待数据库连接..."
    sleep 2
done

echo "数据库已就绪！"

# 检查 WP-CLI 是否可用
if ! command -v wp &> /dev/null; then
    echo "错误: WP-CLI 未安装，无法继续初始化"
    exit 1
fi

# 检查 WordPress 是否已安装
if ! wp core is-installed --allow-root 2>/dev/null; then
    echo "正在安装 WordPress..."
    
    # 安装 WordPress（使用环境变量，无默认回退值确保配置一致性）
    wp core install \
        --url="${WP_HOME}" \
        --title="跨境电商商城" \
        --admin_user="${WP_ADMIN_USER}" \
        --admin_password="${WP_ADMIN_PASSWORD}" \
        --admin_email="${WP_ADMIN_EMAIL}" \
        --skip-email \
        --allow-root

    # 设置中文语言
    wp language core install zh_CN --allow-root
    wp site switch-language zh_CN --allow-root

    # 安装 WooCommerce
    wp plugin install woocommerce --activate --allow-root
    
    # 安装 WooCommerce 多语言支持
    wp plugin install woocommerce-multilingual --allow-root
    
    # 安装 JWT 认证插件（用于 REST API）
    wp plugin install jwt-authentication-for-wp-rest-api --activate --allow-root
    
    # 安装跨境电商相关插件
    wp plugin install woo-multi-currency --allow-root
    
    # 设置永久链接
    wp rewrite structure '/%postname%/' --allow-root
    wp rewrite flush --allow-root
    
    # 创建 WooCommerce 页面
    wp wc --user=admin tool run install_pages --allow-root 2>/dev/null || true

    echo "WordPress 安装完成！"
else
    echo "WordPress 已安装，跳过安装步骤。"
fi

# 启动 Apache
exec apache2-foreground
