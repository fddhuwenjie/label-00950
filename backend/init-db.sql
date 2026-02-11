-- WordPress/WooCommerce 数据库初始化脚本
-- 此脚本在 MySQL 容器首次启动时自动执行

-- 设置字符集
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- 创建商品表 (WooCommerce 风格)
CREATE TABLE IF NOT EXISTS wp_products (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    sku VARCHAR(100) NOT NULL DEFAULT '',
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    short_description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    regular_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock_quantity INT NOT NULL DEFAULT 0,
    stock_status ENUM('instock', 'outofstock', 'onbackorder') DEFAULT 'instock',
    category_id BIGINT UNSIGNED DEFAULT NULL,
    image_url VARCHAR(500) DEFAULT '',
    gallery_images JSON DEFAULT NULL,
    status ENUM('publish', 'draft', 'pending', 'private') DEFAULT 'publish',
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY sku (sku),
    KEY slug (slug),
    KEY category_id (category_id),
    KEY status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建商品分类表
CREATE TABLE IF NOT EXISTS wp_product_categories (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    parent_id BIGINT UNSIGNED DEFAULT NULL,
    image_url VARCHAR(500) DEFAULT '',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY slug (slug),
    KEY parent_id (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建用户表
CREATE TABLE IF NOT EXISTS wp_customers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) DEFAULT '',
    last_name VARCHAR(100) DEFAULT '',
    phone VARCHAR(50) DEFAULT '',
    role ENUM('customer', 'admin') DEFAULT 'customer',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建用户地址表
CREATE TABLE IF NOT EXISTS wp_customer_addresses (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    customer_id BIGINT UNSIGNED NOT NULL,
    address_type ENUM('billing', 'shipping') DEFAULT 'shipping',
    first_name VARCHAR(100) DEFAULT '',
    last_name VARCHAR(100) DEFAULT '',
    company VARCHAR(255) DEFAULT '',
    address_1 VARCHAR(255) NOT NULL,
    address_2 VARCHAR(255) DEFAULT '',
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) DEFAULT '',
    postcode VARCHAR(20) DEFAULT '',
    country VARCHAR(2) NOT NULL DEFAULT 'CN',
    phone VARCHAR(50) DEFAULT '',
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY customer_id (customer_id),
    FOREIGN KEY (customer_id) REFERENCES wp_customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建订单表
CREATE TABLE IF NOT EXISTS wp_orders (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_number VARCHAR(50) NOT NULL,
    customer_id BIGINT UNSIGNED DEFAULT NULL,
    status ENUM('pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed') DEFAULT 'pending',
    currency VARCHAR(3) DEFAULT 'USD',
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    shipping_total DECIMAL(10,2) DEFAULT 0.00,
    tax_total DECIMAL(10,2) DEFAULT 0.00,
    discount_total DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_method VARCHAR(100) DEFAULT '',
    payment_method_title VARCHAR(255) DEFAULT '',
    transaction_id VARCHAR(255) DEFAULT '',
    billing_address JSON,
    shipping_address JSON,
    customer_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY order_number (order_number),
    KEY customer_id (customer_id),
    KEY status (status),
    KEY created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建订单商品表
CREATE TABLE IF NOT EXISTS wp_order_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(100) DEFAULT '',
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    KEY order_id (order_id),
    KEY product_id (product_id),
    FOREIGN KEY (order_id) REFERENCES wp_orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建购物车表 (用于持久化购物车)
CREATE TABLE IF NOT EXISTS wp_cart (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    session_id VARCHAR(255) NOT NULL,
    customer_id BIGINT UNSIGNED DEFAULT NULL,
    cart_data JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY session_id (session_id),
    KEY customer_id (customer_id),
    KEY expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 创建 CSRF Token 表
CREATE TABLE IF NOT EXISTS wp_csrf_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    token VARCHAR(64) NOT NULL,
    session_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY token (token),
    KEY session_id (session_id),
    KEY expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入默认分类
INSERT INTO wp_product_categories (name, slug, description, display_order) VALUES
('数码电子', 'electronics', '手机、电脑、数码配件等电子产品', 1),
('时尚服饰', 'fashion', '服装、鞋包、配饰等时尚单品', 2),
('美妆护肤', 'beauty', '护肤品、彩妆、香水等美妆产品', 3),
('家居生活', 'home', '家电、家具、生活用品', 4)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 插入示例商品
INSERT INTO wp_products (sku, name, slug, description, price, sale_price, regular_price, stock_quantity, category_id, image_url, gallery_images) VALUES
('IP15PM-256-BK', 'iPhone 15 Pro Max 256GB 深空黑', 'iphone-15-pro-max-256gb', '全新 iPhone 15 Pro Max，搭载 A17 Pro 芯片，钛金属设计', 1199.00, 1099.00, 1199.00, 50, 1, 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=400&h=400&fit=crop', '["https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=800&h=800&fit=crop"]'),
('MBP14-M3PRO', 'MacBook Pro 14" M3 Pro 芯片', 'macbook-pro-14-m3-pro', 'MacBook Pro 14 英寸，M3 Pro 芯片，18GB 内存', 1999.00, NULL, 1999.00, 30, 1, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400&h=400&fit=crop', '["https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&h=800&fit=crop"]'),
('SONY-WH1000XM5', 'Sony WH-1000XM5 无线降噪耳机', 'sony-wh-1000xm5', '索尼旗舰降噪耳机，30小时续航', 399.00, 349.00, 399.00, 80, 1, 'https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?w=400&h=400&fit=crop', '["https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?w=800&h=800&fit=crop"]'),
('AW-ULTRA2-TI', 'Apple Watch Ultra 2 钛金属', 'apple-watch-ultra-2', 'Apple Watch Ultra 2，钛金属表壳', 799.00, NULL, 799.00, 25, 1, 'https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?w=400&h=400&fit=crop', '["https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?w=800&h=800&fit=crop"]'),
('DYSON-V15', 'Dyson V15 Detect 智能吸尘器', 'dyson-v15-detect', '戴森智能吸尘器，激光探测灰尘', 749.00, 699.00, 749.00, 15, 4, 'https://images.unsplash.com/photo-1558317374-067fb5f30001?w=400&h=400&fit=crop', '["https://images.unsplash.com/photo-1558317374-067fb5f30001?w=800&h=800&fit=crop"]'),
('LAMER-CREAM-60', 'La Mer 海蓝之谜修护精华面霜', 'la-mer-cream-60ml', '海蓝之谜修护面霜 60ml', 350.00, 320.00, 350.00, 40, 3, 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=400&h=400&fit=crop', '["https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=800&h=800&fit=crop"]'),
('GUCCI-MARMONT', 'Gucci GG Marmont 链条包', 'gucci-gg-marmont', 'Gucci 经典链条包，小号', 2300.00, NULL, 2300.00, 10, 2, 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=400&h=400&fit=crop', '["https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=800&h=800&fit=crop"]'),
('NSW-OLED-WH', 'Nintendo Switch OLED 白色款', 'nintendo-switch-oled-white', 'Nintendo Switch OLED 版本', 349.00, 299.00, 349.00, 60, 1, 'https://images.unsplash.com/photo-1578303512597-81e6cc155b3e?w=400&h=400&fit=crop', '["https://images.unsplash.com/photo-1578303512597-81e6cc155b3e?w=800&h=800&fit=crop"]')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 创建管理员账户 (密码需要在应用层加密)
-- 默认密码: admin123 (实际部署时请修改)
INSERT INTO wp_customers (email, password_hash, first_name, last_name, role, status) VALUES
('admin@example.com', '$2y$10$placeholder_hash_replace_in_app', 'Admin', 'User', 'admin', 'active')
ON DUPLICATE KEY UPDATE email=VALUES(email);
