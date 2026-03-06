<?php
/**
 * WordPress 跨境电商配置文件
 */

// 数据库设置
define('DB_NAME', getenv('WORDPRESS_DB_NAME') ?: 'wordpress');
define('DB_USER', getenv('WORDPRESS_DB_USER') ?: 'wordpress');
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD') ?: 'wordpress');
define('DB_HOST', getenv('WORDPRESS_DB_HOST') ?: 'mysql');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// 认证密钥
define('AUTH_KEY',         getenv('AUTH_KEY') ?: 'put your unique phrase here');
define('SECURE_AUTH_KEY',  getenv('SECURE_AUTH_KEY') ?: 'put your unique phrase here');
define('LOGGED_IN_KEY',    getenv('LOGGED_IN_KEY') ?: 'put your unique phrase here');
define('NONCE_KEY',        getenv('NONCE_KEY') ?: 'put your unique phrase here');
define('AUTH_SALT',        getenv('AUTH_SALT') ?: 'put your unique phrase here');
define('SECURE_AUTH_SALT', getenv('SECURE_AUTH_SALT') ?: 'put your unique phrase here');
define('LOGGED_IN_SALT',   getenv('LOGGED_IN_SALT') ?: 'put your unique phrase here');
define('NONCE_SALT',       getenv('NONCE_SALT') ?: 'put your unique phrase here');

// 数据库表前缀
$table_prefix = 'wp_';

// 调试模式
define('WP_DEBUG', getenv('WP_DEBUG') === 'true');
define('WP_DEBUG_LOG', getenv('WP_DEBUG') === 'true');
define('WP_DEBUG_DISPLAY', false);

// WordPress 地址
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// 允许跨域访问 REST API
define('WP_HOME', getenv('WP_HOME') ?: 'http://localhost:8080');
define('WP_SITEURL', getenv('WP_SITEURL') ?: 'http://localhost:8080');

// 文件系统方法
define('FS_METHOD', 'direct');

// 禁用自动更新
define('AUTOMATIC_UPDATER_DISABLED', true);

// WooCommerce 配置
define('WC_ENABLE_REST_API', true);

// JWT 认证配置
define('JWT_AUTH_SECRET_KEY', getenv('API_SECRET_KEY') ?: 'jwt-secret-key-change-me');
define('JWT_AUTH_CORS_ENABLE', true);

// 内存限制
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');

// 绝对路径
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// 加载 WordPress
require_once ABSPATH . 'wp-settings.php';
