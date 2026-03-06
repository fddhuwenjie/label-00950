#!/bin/bash
set -e

echo "=== 跨境电商后端初始化 ==="

# 等待数据库就绪
until mysql -h"$WORDPRESS_DB_HOST" -u"$WORDPRESS_DB_USER" -p"$WORDPRESS_DB_PASSWORD" --skip-ssl -e "SELECT 1" > /dev/null 2>&1; do
    echo "等待数据库连接..."
    sleep 2
done
echo "数据库已就绪！"

# 标记文件，用于判断初始化是否完成
INIT_DONE_FLAG="/var/www/html/.init_done"

# 检查 WordPress 是否已安装
if ! wp core is-installed --allow-root 2>/dev/null; then
    echo "=== 安装 WordPress ==="
    wp core install \
        --url="${WP_HOME}" \
        --title="跨境电商商城" \
        --admin_user="${WP_ADMIN_USER}" \
        --admin_password="${WP_ADMIN_PASSWORD}" \
        --admin_email="${WP_ADMIN_EMAIL}" \
        --skip-email \
        --allow-root

    # 设置中文
    wp language core install zh_CN --allow-root 2>/dev/null || true
    wp site switch-language zh_CN --allow-root 2>/dev/null || true

    # 设置永久链接
    wp rewrite structure '/%postname%/' --allow-root
    wp rewrite flush --allow-root
fi

# 检查 WooCommerce 是否已安装并激活
if ! wp plugin is-active woocommerce --allow-root 2>/dev/null; then
    echo "=== 安装 WooCommerce ==="
    wp plugin install woocommerce --activate --allow-root 2>/dev/null || \
    wp plugin activate woocommerce --allow-root 2>/dev/null || true
fi

# 激活自定义插件
wp plugin activate cross-border-commerce --allow-root 2>/dev/null || true

# 检查是否已完成完整初始化（商品导入等）
if [ ! -f "$INIT_DONE_FLAG" ]; then
    echo "=== 配置 WooCommerce ==="
    # 创建 WooCommerce 页面
    wp wc --user="${WP_ADMIN_USER}" tool run install_pages --allow-root 2>/dev/null || true

    # 配置基本设置
    wp option update woocommerce_currency 'USD' --allow-root 2>/dev/null || true
    wp option update woocommerce_currency_pos 'left' --allow-root 2>/dev/null || true
    wp option update woocommerce_default_country 'US' --allow-root 2>/dev/null || true
    wp option update woocommerce_calc_taxes 'yes' --allow-root 2>/dev/null || true
    wp option update woocommerce_enable_signup_and_login_from_checkout 'yes' --allow-root 2>/dev/null || true
    wp option update woocommerce_enable_myaccount_registration 'yes' --allow-root 2>/dev/null || true

    echo "=== 创建商品分类 ==="
    wp wc --user="${WP_ADMIN_USER}" product_cat create --name="数码电子" --slug="electronics" --allow-root 2>/dev/null || true
    wp wc --user="${WP_ADMIN_USER}" product_cat create --name="时尚服饰" --slug="fashion" --allow-root 2>/dev/null || true
    wp wc --user="${WP_ADMIN_USER}" product_cat create --name="美妆护肤" --slug="beauty" --allow-root 2>/dev/null || true
    wp wc --user="${WP_ADMIN_USER}" product_cat create --name="家居生活" --slug="home" --allow-root 2>/dev/null || true

    echo "=== 导入商品数据 ==="
    wp eval '
    $categories = array(
        "数码电子" => get_term_by("slug", "electronics", "product_cat"),
        "时尚服饰" => get_term_by("slug", "fashion", "product_cat"),
        "美妆护肤" => get_term_by("slug", "beauty", "product_cat"),
        "家居生活" => get_term_by("slug", "home", "product_cat"),
    );

    $products = array(
        array("iPhone 15 Pro Max 256GB 深空黑", "IP15PM-256-BK", 1199, 1099, 50, "数码电子", "全新 iPhone 15 Pro Max，搭载 A17 Pro 芯片", "https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=400&h=400&fit=crop"),
        array("MacBook Pro 14 M3 Pro 芯片", "MBP14-M3PRO", 1999, null, 30, "数码电子", "MacBook Pro 14 英寸，M3 Pro 芯片", "https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400&h=400&fit=crop"),
        array("Sony WH-1000XM5 无线降噪耳机", "SONY-WH1000XM5", 399, 349, 80, "数码电子", "索尼旗舰降噪耳机", "https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?w=400&h=400&fit=crop"),
        array("Apple Watch Ultra 2 钛金属", "AW-ULTRA2-TI", 799, null, 25, "数码电子", "Apple Watch Ultra 2", "https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?w=400&h=400&fit=crop"),
        array("Dyson V15 Detect 智能吸尘器", "DYSON-V15", 749, 699, 15, "家居生活", "戴森智能吸尘器", "https://images.unsplash.com/photo-1558317374-067fb5f30001?w=400&h=400&fit=crop"),
        array("La Mer 海蓝之谜修护精华面霜", "LAMER-CREAM-60", 350, 320, 40, "美妆护肤", "海蓝之谜修护面霜 60ml", "https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=400&h=400&fit=crop"),
        array("Gucci GG Marmont 链条包", "GUCCI-MARMONT", 2300, null, 10, "时尚服饰", "Gucci 经典链条包", "https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=400&h=400&fit=crop"),
        array("Nintendo Switch OLED 白色款", "NSW-OLED-WH", 349, 299, 60, "数码电子", "Nintendo Switch OLED 版本", "https://images.unsplash.com/photo-1578303512597-81e6cc155b3e?w=400&h=400&fit=crop"),
        array("SK-II 神仙水护肤精华露 230ml", "SKII-FTE-230", 185, 165, 100, "美妆护肤", "SK-II 神仙水", "https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=400&h=400&fit=crop"),
        array("Nike Air Jordan 1 Retro High OG", "NIKE-AJ1-OG", 180, null, 45, "时尚服饰", "Nike Air Jordan 1", "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop"),
        array("Philips Sonicare 钻石智能电动牙刷", "PHILIPS-SONIC", 199, 149, 70, "家居生活", "飞利浦电动牙刷", "https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=400&h=400&fit=crop"),
        array("Coach Tabby 手提单肩包", "COACH-TABBY", 395, null, 20, "时尚服饰", "Coach Tabby 手提包", "https://images.unsplash.com/photo-1590874103328-eac38a683ce7?w=400&h=400&fit=crop"),
    );

    // 检查是否已有商品
    $existing = wc_get_products(array("limit" => 1));
    if (count($existing) > 0) {
        echo "商品已存在，跳过导入\n";
    } else {
        foreach ($products as $p) {
            $product = new WC_Product_Simple();
            $product->set_name($p[0]);
            $product->set_sku($p[1]);
            $product->set_regular_price($p[2]);
            if ($p[3]) $product->set_sale_price($p[3]);
            $product->set_stock_quantity($p[4]);
            $product->set_manage_stock(true);
            $product->set_description($p[6]);
            $product->set_short_description($p[6]);
            $product->set_status("publish");
            $cat = $categories[$p[5]] ?? null;
            if ($cat) $product->set_category_ids(array($cat->term_id));
            $product->update_meta_data("_external_image", $p[7]);
            $product->save();
            echo "Created: " . $p[0] . "\n";
        }
    }
    ' --allow-root 2>&1 || echo "商品导入出错"

    echo "=== 创建测试用户 ==="
    wp user create testuser test@example.com --role=customer --user_pass=test123 --display_name="测试用户" --allow-root 2>/dev/null || true

    # 标记初始化完成
    touch "$INIT_DONE_FLAG"
    echo "=== 初始化完成 ==="
else
    echo "初始化已完成，跳过。"
fi

echo "初始化脚本执行完毕"
