<?php
/**
 * Plugin Name: 跨境电商增强
 * Plugin URI: https://example.com/cross-border-commerce
 * Description: 为 WooCommerce 添加跨境电商功能，包括多货币、国际物流、关税计算，并提供前端 REST API
 * Version: 1.0.0
 * Author: Cross Border Team
 * Text Domain: cross-border-commerce
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * WC requires at least: 8.0
 */

if (!defined('ABSPATH')) exit;

define('CBC_VERSION', '1.0.0');
define('CBC_PLUGIN_DIR', plugin_dir_path(__FILE__));

class Cross_Border_Commerce {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->includes();
        add_action('rest_api_init', array($this, 'register_routes'));
        // 允许 CORS
        add_action('rest_api_init', function() {
            remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
            add_filter('rest_pre_serve_request', function($value) {
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
                header('Access-Control-Allow-Headers: Content-Type, Authorization');
                header('Access-Control-Allow-Credentials: true');
                return $value;
            });
        }, 15);
    }

    private function includes() {
        // 加载 Composer 依赖（firebase/php-jwt）
        $autoload = CBC_PLUGIN_DIR . 'vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }
        require_once CBC_PLUGIN_DIR . 'includes/class-currency-converter.php';
        require_once CBC_PLUGIN_DIR . 'includes/class-duty-calculator.php';
        require_once CBC_PLUGIN_DIR . 'includes/class-shipping-calculator.php';
    }

    /**
     * 检查 WooCommerce 是否已加载，如未加载则尝试手动加载
     */
    private function ensure_wc_loaded() {
        if (!function_exists('wc_get_products')) {
            // 尝试手动加载 WooCommerce
            if (defined('WC_ABSPATH') && file_exists(WC_ABSPATH . 'includes/wc-product-functions.php')) {
                include_once WC_ABSPATH . 'includes/wc-product-functions.php';
                include_once WC_ABSPATH . 'includes/wc-order-functions.php';
            }
        }
        if (!function_exists('wc_get_products')) {
            return new WP_Error('wc_not_ready', 'WooCommerce 尚未加载完成，请稍后重试', array('status' => 503));
        }
        return true;
    }

    public function register_routes() {
        $namespace = 'cbc/v1';

        // ===== 商品 API（公开，使用 WooCommerce 数据） =====
        register_rest_route($namespace, '/products', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_products'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route($namespace, '/products/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_product'),
            'permission_callback' => '__return_true',
        ));

        // 商品管理（需要认证）
        register_rest_route($namespace, '/products', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_product'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));

        register_rest_route($namespace, '/products/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_product'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));

        register_rest_route($namespace, '/products/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_product'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));

        register_rest_route($namespace, '/upload', array(
            'methods' => 'POST',
            'callback' => array($this, 'upload_image'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));

        // ===== 分类 API =====
        register_rest_route($namespace, '/categories', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_categories'),
            'permission_callback' => '__return_true',
        ));

        // ===== 用户认证 API =====
        register_rest_route($namespace, '/auth/login', array(
            'methods' => 'POST',
            'callback' => array($this, 'user_login'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route($namespace, '/auth/register', array(
            'methods' => 'POST',
            'callback' => array($this, 'user_register'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route($namespace, '/auth/me', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_current_user_info'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));

        register_rest_route($namespace, '/auth/change-password', array(
            'methods' => 'POST',
            'callback' => array($this, 'change_password'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));

        register_rest_route($namespace, '/auth/forgot-password', array(
            'methods' => 'POST',
            'callback' => array($this, 'forgot_password'),
            'permission_callback' => '__return_true',
        ));

        // ===== 订单 API =====
        register_rest_route($namespace, '/orders', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_orders'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));

        register_rest_route($namespace, '/orders', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_order'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));

        register_rest_route($namespace, '/orders/(?P<id>\d+)/status', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_order_status'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));

        register_rest_route($namespace, '/orders/(?P<id>\d+)/pay', array(
            'methods' => 'POST',
            'callback' => array($this, 'pay_order'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));

        // ===== 跨境电商功能 API =====
        register_rest_route($namespace, '/currency/convert', array(
            'methods' => 'GET',
            'callback' => array($this, 'convert_currency'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route($namespace, '/shipping/calculate', array(
            'methods' => 'POST',
            'callback' => array($this, 'calculate_shipping'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route($namespace, '/duty/calculate', array(
            'methods' => 'POST',
            'callback' => array($this, 'calculate_duty'),
            'permission_callback' => '__return_true',
        ));

        // ===== 站点设置 API =====
        register_rest_route($namespace, '/settings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_settings'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route($namespace, '/settings', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_settings'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));

        // ===== 仪表盘 API =====
        register_rest_route($namespace, '/dashboard', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_dashboard'),
            'permission_callback' => array($this, 'check_admin_permission'),
        ));
    }

    // ===== 权限检查 =====
    public function check_admin_permission($request) {
        $user = $this->get_user_from_token($request);
        if (!$user) return new WP_Error('unauthorized', '未授权', array('status' => 401));
        return user_can($user, 'manage_options');
    }

    public function check_user_permission($request) {
        $user = $this->get_user_from_token($request);
        return $user ? true : new WP_Error('unauthorized', '未授权', array('status' => 401));
    }

    private function get_user_from_token($request) {
        $auth = $request->get_header('Authorization');
        if (!$auth) return null;
        $token = str_replace('Bearer ', '', $auth);
        if (empty($token)) return null;

        $secret = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : 'default-secret';

        try {
            // 使用 firebase/php-jwt 标准库解码
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($secret, 'HS256'));
            $user_id = intval($decoded->sub ?? 0);
            if (!$user_id) return null;

            $user = get_user_by('ID', $user_id);
            return $user ?: null;
        } catch (\Firebase\JWT\ExpiredException $e) {
            // Token 过期
            return null;
        } catch (\Exception $e) {
            // 其他解码错误（签名无效等）
            return null;
        }
    }

    private function generate_token($user_id) {
        $secret = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : 'default-secret';
        $now = time();

        $payload = array(
            'iss' => get_bloginfo('url'),       // 签发者
            'iat' => $now,                       // 签发时间
            'nbf' => $now,                       // 生效时间
            'exp' => $now + (7 * 86400),         // 过期时间（7天）
            'sub' => $user_id,                   // 用户 ID
        );

        return \Firebase\JWT\JWT::encode($payload, $secret, 'HS256');
    }

    // ===== 用户认证 =====
    public function user_login($request) {
        $params = $request->get_json_params();
        $username = sanitize_text_field($params['username'] ?? '');
        $password = $params['password'] ?? '';

        if (empty($username) || empty($password)) {
            return new WP_Error('missing_fields', '用户名和密码不能为空', array('status' => 400));
        }

        $user = wp_authenticate($username, $password);
        if (is_wp_error($user)) {
            return new WP_Error('invalid_credentials', '用户名或密码错误', array('status' => 401));
        }

        $token = $this->generate_token($user->ID);

        return rest_ensure_response(array(
            'token' => $token,
            'user' => array(
                'id' => $user->ID,
                'name' => $user->display_name,
                'email' => $user->user_email,
                'role' => implode(',', $user->roles),
            ),
        ));
    }

    public function user_register($request) {
        $params = $request->get_json_params();
        $username = sanitize_text_field($params['username'] ?? '');
        $email = sanitize_email($params['email'] ?? '');
        $password = $params['password'] ?? '';
        $name = sanitize_text_field($params['name'] ?? $username);

        if (empty($username) || empty($email) || empty($password)) {
            return new WP_Error('missing_fields', '所有字段都是必填的', array('status' => 400));
        }

        if (username_exists($username)) {
            return new WP_Error('username_exists', '用户名已存在', array('status' => 409));
        }

        if (email_exists($email)) {
            return new WP_Error('email_exists', '邮箱已被注册', array('status' => 409));
        }

        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            return new WP_Error('registration_failed', '注册失败', array('status' => 500));
        }

        wp_update_user(array('ID' => $user_id, 'display_name' => $name));
        $user = get_user_by('ID', $user_id);
        $user->set_role('customer');

        $token = $this->generate_token($user_id);

        return rest_ensure_response(array(
            'token' => $token,
            'user' => array(
                'id' => $user_id,
                'name' => $name,
                'email' => $email,
                'role' => 'customer',
            ),
        ));
    }

    public function get_current_user_info($request) {
        $user = $this->get_user_from_token($request);
        if (!$user) return new WP_Error('unauthorized', '未授权', array('status' => 401));

        return rest_ensure_response(array(
            'id' => $user->ID,
            'name' => $user->display_name,
            'email' => $user->user_email,
            'role' => implode(',', $user->roles),
        ));
    }

    public function change_password($request) {
        $user = $this->get_user_from_token($request);
        if (!$user) return new WP_Error('unauthorized', '未授权', array('status' => 401));

        $params = $request->get_json_params();
        $current = $params['current_password'] ?? '';
        $new_pass = $params['new_password'] ?? '';

        if (empty($current) || empty($new_pass)) {
            return new WP_Error('missing_fields', '请填写当前密码和新密码', array('status' => 400));
        }

        if (!wp_check_password($current, $user->user_pass, $user->ID)) {
            return new WP_Error('wrong_password', '当前密码不正确', array('status' => 400));
        }

        wp_set_password($new_pass, $user->ID);
        // 生成新 token（密码变更后旧 token 仍有效，因为我们用的是 JWT）
        $token = $this->generate_token($user->ID);

        return rest_ensure_response(array(
            'message' => '密码已更新',
            'token' => $token,
        ));
    }

    public function forgot_password($request) {
        $params = $request->get_json_params();
        $email = sanitize_email($params['email'] ?? '');

        if (empty($email)) {
            return new WP_Error('missing_email', '请输入邮箱', array('status' => 400));
        }

        $user = get_user_by('email', $email);
        if ($user) {
            // 使用 WordPress 内置的密码重置功能
            retrieve_password($user->user_login);
        }

        // 无论邮箱是否存在都返回成功（安全考虑）
        return rest_ensure_response(array(
            'message' => '如果该邮箱已注册，重置链接已发送',
        ));
    }

    // ===== 商品 API（使用 WooCommerce 数据） =====
    public function get_products($request) {
        $wc_check = $this->ensure_wc_loaded();
        if (is_wp_error($wc_check)) return $wc_check;
        $per_page = intval($request->get_param('per_page') ?: 50);
        $page = intval($request->get_param('page') ?: 1);
        $category = $request->get_param('category');
        $search = $request->get_param('search');

        $args = array(
            'status' => 'publish',
            'limit' => $per_page,
            'page' => $page,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        if ($category) {
            $args['category'] = array($category);
        }

        if ($search) {
            $args['s'] = $search;
        }

        $products = wc_get_products($args);
        $data = array();

        foreach ($products as $product) {
            $data[] = $this->format_product($product);
        }

        return rest_ensure_response($data);
    }

    public function get_product($request) {
        $wc_check = $this->ensure_wc_loaded();
        if (is_wp_error($wc_check)) return $wc_check;
        $id = intval($request['id']);
        $product = wc_get_product($id);

        if (!$product) {
            return new WP_Error('not_found', '商品不存在', array('status' => 404));
        }

        return rest_ensure_response($this->format_product($product));
    }

    public function create_product($request) {
        $wc_check = $this->ensure_wc_loaded();
        if (is_wp_error($wc_check)) return $wc_check;
        $params = $request->get_json_params();

        $product = new WC_Product_Simple();
        $product->set_name(sanitize_text_field($params['name'] ?? ''));
        $product->set_sku(sanitize_text_field($params['sku'] ?? ''));
        $product->set_regular_price($params['price'] ?? 0);
        if (!empty($params['salePrice'])) {
            $product->set_sale_price($params['salePrice']);
        }
        $product->set_stock_quantity(intval($params['stock'] ?? 0));
        $product->set_manage_stock(true);
        $product->set_description(sanitize_textarea_field($params['description'] ?? ''));
        $product->set_short_description(sanitize_textarea_field($params['description'] ?? ''));

        // 设置分类
        if (!empty($params['category'])) {
            $term = get_term_by('name', $params['category'], 'product_cat');
            if ($term) {
                $product->set_category_ids(array($term->term_id));
            }
        }

        // 设置图片
        if (!empty($params['image'])) {
            // 存储外部图片 URL 到 meta
            $product->update_meta_data('_external_image', $params['image']);
        }
        if (!empty($params['images'])) {
            $product->update_meta_data('_external_images', $params['images']);
        }

        $product->set_status('publish');
        $id = $product->save();

        return rest_ensure_response($this->format_product(wc_get_product($id)));
    }

    public function update_product($request) {
        $wc_check = $this->ensure_wc_loaded();
        if (is_wp_error($wc_check)) return $wc_check;
        $id = intval($request['id']);
        $product = wc_get_product($id);
        if (!$product) {
            return new WP_Error('not_found', '商品不存在', array('status' => 404));
        }

        $params = $request->get_json_params();

        if (isset($params['name'])) $product->set_name(sanitize_text_field($params['name']));
        if (isset($params['sku'])) $product->set_sku(sanitize_text_field($params['sku']));
        if (isset($params['price'])) $product->set_regular_price($params['price']);
        if (isset($params['salePrice'])) $product->set_sale_price($params['salePrice']);
        if (isset($params['stock'])) $product->set_stock_quantity(intval($params['stock']));
        if (isset($params['description'])) {
            $product->set_description(sanitize_textarea_field($params['description']));
            $product->set_short_description(sanitize_textarea_field($params['description']));
        }

        if (!empty($params['category'])) {
            $term = get_term_by('name', $params['category'], 'product_cat');
            if ($term) {
                $product->set_category_ids(array($term->term_id));
            }
        }

        if (!empty($params['image'])) {
            $product->update_meta_data('_external_image', $params['image']);
        }
        if (!empty($params['images'])) {
            $product->update_meta_data('_external_images', $params['images']);
        }

        $product->save();

        return rest_ensure_response($this->format_product($product));
    }

    public function delete_product($request) {
        $wc_check = $this->ensure_wc_loaded();
        if (is_wp_error($wc_check)) return $wc_check;
        $id = intval($request['id']);
        $product = wc_get_product($id);
        if (!$product) {
            return new WP_Error('not_found', '商品不存在', array('status' => 404));
        }

        $product->delete(true);
        return rest_ensure_response(array('deleted' => true, 'id' => $id));
    }

    private function format_product($product) {
        $cat_terms = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'all'));
        $category = '';
        $categorySlug = '';
        if (!empty($cat_terms) && !is_wp_error($cat_terms)) {
            $category = $cat_terms[0]->name;
            $categorySlug = $cat_terms[0]->slug;
        }

        // 获取图片
        $image = $product->get_meta('_external_image');
        $images = $product->get_meta('_external_images');

        // 如果没有外部图片，尝试获取 WooCommerce 图片
        if (empty($image)) {
            $image_id = $product->get_image_id();
            $image = $image_id ? wp_get_attachment_url($image_id) : '';
        }
        if (empty($images)) {
            $gallery_ids = $product->get_gallery_image_ids();
            $images = array_map('wp_get_attachment_url', $gallery_ids);
        }

        return array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'sku' => $product->get_sku(),
            'price' => floatval($product->get_regular_price()),
            'salePrice' => $product->get_sale_price() ? floatval($product->get_sale_price()) : null,
            'stock' => $product->get_stock_quantity(),
            'category' => $category,
            'categorySlug' => $categorySlug,
            'image' => $image ?: '',
            'images' => is_array($images) ? $images : array(),
            'description' => $product->get_description(),
            'status' => $product->get_status(),
        );
    }

    // ===== 分类 =====
    public function get_categories($request) {
        $terms = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
        ));

        $data = array();
        foreach ($terms as $term) {
            if ($term->slug === 'uncategorized') continue;
            $data[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'count' => $term->count,
            );
        }

        return rest_ensure_response($data);
    }

    // ===== 订单 =====
    public function get_orders($request) {
        $wc_check = $this->ensure_wc_loaded();
        if (is_wp_error($wc_check)) return $wc_check;
        $user = $this->get_user_from_token($request);
        $is_admin = user_can($user, 'manage_options');

        $args = array(
            'limit' => intval($request->get_param('per_page') ?: 20),
            'page' => intval($request->get_param('page') ?: 1),
            'orderby' => 'date',
            'order' => 'DESC',
        );

        if (!$is_admin) {
            $args['customer_id'] = $user->ID;
        }

        $orders = wc_get_orders($args);
        $data = array();

        foreach ($orders as $order) {
            $items = array();
            foreach ($order->get_items() as $item) {
                $product = $item->get_product();
                $image = '';
                if ($product) {
                    $image = $product->get_meta('_external_image');
                    if (empty($image)) {
                        $image_id = $product->get_image_id();
                        $image = $image_id ? wp_get_attachment_url($image_id) : '';
                    }
                }
                $items[] = array(
                    'id' => $item->get_id(),
                    'name' => $item->get_name(),
                    'quantity' => $item->get_quantity(),
                    'price' => floatval($item->get_total()),
                    'image' => $image,
                );
            }

            $data[] = array(
                'id' => $order->get_id(),
                'number' => $order->get_order_number(),
                'status' => $order->get_status(),
                'total' => floatval($order->get_total()),
                'date' => $order->get_date_created()->format('Y-m-d H:i:s'),
                'items' => $items,
                'billing' => array(
                    'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                    'email' => $order->get_billing_email(),
                    'phone' => $order->get_billing_phone(),
                    'address' => $order->get_billing_address_1(),
                ),
            );
        }

        return rest_ensure_response($data);
    }

    public function create_order($request) {
        $wc_check = $this->ensure_wc_loaded();
        if (is_wp_error($wc_check)) return $wc_check;
        $user = $this->get_user_from_token($request);
        $params = $request->get_json_params();

        $order = wc_create_order(array('customer_id' => $user->ID));

        // 添加商品
        if (!empty($params['items'])) {
            foreach ($params['items'] as $item) {
                $product = wc_get_product(intval($item['id']));
                if ($product) {
                    $order->add_product($product, intval($item['quantity'] ?? 1));
                }
            }
        }

        // 设置账单信息
        if (!empty($params['billing'])) {
            $b = $params['billing'];
            $order->set_billing_first_name(sanitize_text_field($b['firstName'] ?? ''));
            $order->set_billing_last_name(sanitize_text_field($b['lastName'] ?? ''));
            $order->set_billing_email(sanitize_email($b['email'] ?? $user->user_email));
            $order->set_billing_phone(sanitize_text_field($b['phone'] ?? ''));
            $order->set_billing_address_1(sanitize_text_field($b['address'] ?? ''));
            $order->set_billing_city(sanitize_text_field($b['city'] ?? ''));
            $order->set_billing_state(sanitize_text_field($b['state'] ?? ''));
            $order->set_billing_postcode(sanitize_text_field($b['postcode'] ?? ''));
            $order->set_billing_country(sanitize_text_field($b['country'] ?? 'CN'));
        }

        // 设置运费
        if (!empty($params['shipping_fee'])) {
            $shipping = new WC_Order_Item_Shipping();
            $shipping->set_method_title('国际物流');
            $shipping->set_total($params['shipping_fee']);
            $order->add_item($shipping);
        }

        $order->calculate_totals();
        $order->set_status('pending');
        $order->save();

        return rest_ensure_response(array(
            'id' => $order->get_id(),
            'number' => $order->get_order_number(),
            'total' => floatval($order->get_total()),
            'status' => $order->get_status(),
        ));
    }

    public function update_order_status($request) {
        $wc_check = $this->ensure_wc_loaded();
        if (is_wp_error($wc_check)) return $wc_check;
        $order_id = intval($request->get_param('id'));
        $params = $request->get_json_params();
        $new_status = sanitize_text_field($params['status'] ?? '');

        $valid_statuses = array('pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed');
        if (!in_array($new_status, $valid_statuses)) {
            return new WP_Error('invalid_status', '无效的订单状态', array('status' => 400));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return new WP_Error('not_found', '订单不存在', array('status' => 404));
        }

        $order->set_status($new_status);
        $order->save();

        return rest_ensure_response(array(
            'id' => $order->get_id(),
            'status' => $order->get_status(),
            'message' => '订单状态已更新',
        ));
    }

    public function pay_order($request) {
        $wc_check = $this->ensure_wc_loaded();
        if (is_wp_error($wc_check)) return $wc_check;
        $user = $this->get_user_from_token($request);
        $order_id = intval($request->get_param('id'));
        $order = wc_get_order($order_id);

        if (!$order) {
            return new WP_Error('not_found', '订单不存在', array('status' => 404));
        }

        // 验证订单属于当前用户
        if ($order->get_customer_id() !== $user->ID) {
            return new WP_Error('forbidden', '无权操作此订单', array('status' => 403));
        }

        // 只允许 pending 状态的订单支付
        if ($order->get_status() !== 'pending') {
            return new WP_Error('invalid_status', '该订单不是待付款状态', array('status' => 400));
        }

        $order->set_status('processing');
        $order->set_date_paid(current_time('timestamp'));
        $order->save();

        return rest_ensure_response(array(
            'id' => $order->get_id(),
            'status' => $order->get_status(),
            'message' => '支付成功',
        ));
    }

    // ===== 图片上传 =====
    public function upload_image($request) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $files = $request->get_file_params();
        if (empty($files['file'])) {
            return new WP_Error('no_file', '请选择文件', array('status' => 400));
        }

        $_FILES['upload'] = $files['file'];
        $attachment_id = media_handle_upload('upload', 0);

        if (is_wp_error($attachment_id)) {
            return new WP_Error('upload_failed', $attachment_id->get_error_message(), array('status' => 500));
        }

        $url = wp_get_attachment_url($attachment_id);

        return rest_ensure_response(array(
            'id' => $attachment_id,
            'url' => $url,
        ));
    }

    // ===== 跨境电商功能 =====
    public function convert_currency($request) {
        $amount = floatval($request->get_param('amount') ?: 0);
        $from = strtoupper($request->get_param('from') ?: 'USD');
        $to = strtoupper($request->get_param('to') ?: 'CNY');

        $converter = CBC_Currency_Converter::get_instance();
        $result = $converter->convert($amount, $from, $to);
        $rate_info = $converter->get_rate_info();

        return rest_ensure_response(array(
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'converted' => round($result, 2),
            'rate' => $converter->get_rate($from, $to),
            'formatted' => $converter->format_price($result, $to),
            'source' => $rate_info['source'],
            'updated_at' => $rate_info['updated_at'],
        ));
    }

    public function calculate_shipping($request) {
        $params = $request->get_json_params();
        $country = sanitize_text_field($params['country'] ?? 'CN');
        $weight = floatval($params['weight'] ?? 1);
        $method = sanitize_text_field($params['method'] ?? 'standard');
        $dimensions = isset($params['dimensions']) ? array_map('floatval', $params['dimensions']) : array();
        $order_total = floatval($params['order_total'] ?? 0);

        $calculator = CBC_Shipping_Calculator::get_instance();

        // 如果请求所有报价
        if (!empty($params['all_quotes'])) {
            $quotes = $calculator->get_all_quotes($country, $weight, $dimensions, $order_total);
            return rest_ensure_response(array(
                'country' => $country,
                'weight' => $weight,
                'quotes' => $quotes,
            ));
        }

        $result = $calculator->calculate($country, $weight, $method, $dimensions, $order_total);
        return rest_ensure_response($result);
    }

    public function calculate_duty($request) {
        $params = $request->get_json_params();
        $country = sanitize_text_field($params['country'] ?? 'CN');
        $amount = floatval($params['amount'] ?? 0);
        $category = sanitize_text_field($params['category'] ?? 'default');

        $calculator = CBC_Duty_Calculator::get_instance();
        $duty = $calculator->calculate($country, $amount, $category);

        return rest_ensure_response(array(
            'country' => $country,
            'amount' => $amount,
            'category' => $category,
            'duty' => round($duty, 2),
            'rate' => $calculator->get_rate($country, $category),
            'total' => round($amount + $duty, 2),
        ));
    }

    // ===== 站点设置 =====
    public function get_settings($request) {
        return rest_ensure_response(array(
            'siteName' => get_bloginfo('name'),
            'siteDescription' => get_bloginfo('description'),
            'currency' => get_woocommerce_currency(),
            'currencySymbol' => get_woocommerce_currency_symbol(),
            'contactEmail' => get_option('cbc_contact_email', get_option('admin_email')),
            'contactPhone' => get_option('cbc_contact_phone', '+86 400-888-8888'),
            'address' => get_option('cbc_address', '中国上海市浦东新区陆家嘴金融贸易区世纪大道100号'),
            'defaultShippingFee' => floatval(get_option('cbc_default_shipping_fee', 15)),
            'estimatedDelivery' => get_option('cbc_estimated_delivery', '7-15个工作日'),
        ));
    }

    public function update_settings($request) {
        $params = $request->get_json_params();

        if (isset($params['siteName'])) {
            update_option('blogname', sanitize_text_field($params['siteName']));
        }
        if (isset($params['siteDescription'])) {
            update_option('blogdescription', sanitize_text_field($params['siteDescription']));
        }
        if (isset($params['contactEmail'])) {
            update_option('cbc_contact_email', sanitize_email($params['contactEmail']));
        }
        if (isset($params['contactPhone'])) {
            update_option('cbc_contact_phone', sanitize_text_field($params['contactPhone']));
        }
        if (isset($params['address'])) {
            update_option('cbc_address', sanitize_text_field($params['address']));
        }
        if (isset($params['defaultShippingFee'])) {
            update_option('cbc_default_shipping_fee', floatval($params['defaultShippingFee']));
        }
        if (isset($params['estimatedDelivery'])) {
            update_option('cbc_estimated_delivery', sanitize_text_field($params['estimatedDelivery']));
        }

        return $this->get_settings($request);
    }

    // ===== 仪表盘 =====
    public function get_dashboard($request) {
        $wc_check = $this->ensure_wc_loaded();
        if (is_wp_error($wc_check)) return $wc_check;
        $orders_count = 0;
        $revenue = 0;
        $orders = wc_get_orders(array('limit' => -1, 'status' => array('processing', 'completed')));
        foreach ($orders as $order) {
            $orders_count++;
            $revenue += floatval($order->get_total());
        }

        $products_count = wp_count_posts('product');
        $users_count = count_users();

        return rest_ensure_response(array(
            'orders_count' => $orders_count,
            'revenue' => round($revenue, 2),
            'products_count' => $products_count->publish ?? 0,
            'users_count' => $users_count['total_users'] ?? 0,
            'currency' => get_woocommerce_currency(),
        ));
    }
}

// 初始化
Cross_Border_Commerce::get_instance();
