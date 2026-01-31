<?php
/**
 * Plugin Name: 跨境电商增强
 * Plugin URI: https://example.com/cross-border-commerce
 * Description: 为 WooCommerce 添加跨境电商功能，包括多货币、国际物流、关税计算等
 * Version: 1.0.0
 * Author: Cross Border Team
 * Author URI: https://example.com
 * Text Domain: cross-border-commerce
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * WC requires at least: 8.0
 * WC tested up to: 8.5
 */

if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('CBC_VERSION', '1.0.0');
define('CBC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CBC_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * 主插件类
 */
class Cross_Border_Commerce {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
        add_action('init', array($this, 'load_textdomain'));
        
        // CORS 支持
        add_action('rest_api_init', array($this, 'add_cors_support'), 15);
    }
    
    public function init() {
        // 检查 WooCommerce 是否激活
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // 加载功能模块
        $this->load_modules();
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('cross-border-commerce', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function woocommerce_missing_notice() {
        ?>
        <div class="error">
            <p><?php _e('跨境电商增强插件需要 WooCommerce 才能运行。', 'cross-border-commerce'); ?></p>
        </div>
        <?php
    }
    
    private function load_modules() {
        // 加载货币转换模块
        require_once CBC_PLUGIN_DIR . 'includes/class-currency-converter.php';
        
        // 加载物流计算模块
        require_once CBC_PLUGIN_DIR . 'includes/class-shipping-calculator.php';
        
        // 加载关税计算模块
        require_once CBC_PLUGIN_DIR . 'includes/class-duty-calculator.php';
    }
    
    public function add_cors_support() {
        remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
        add_filter('rest_pre_serve_request', function($value) {
            $origin = get_http_origin();
            
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
            
            return $value;
        });
    }
    
    public function register_rest_routes() {
        // 商品相关 API
        register_rest_route('cbc/v1', '/products', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_products'),
            'permission_callback' => '__return_true',
        ));
        
        register_rest_route('cbc/v1', '/products/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_product'),
            'permission_callback' => '__return_true',
        ));
        
        // 分类相关 API
        register_rest_route('cbc/v1', '/categories', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_categories'),
            'permission_callback' => '__return_true',
        ));
        
        // 订单相关 API
        register_rest_route('cbc/v1', '/orders', array(
            'methods' => array('GET', 'POST'),
            'callback' => array($this, 'handle_orders'),
            'permission_callback' => array($this, 'check_auth'),
        ));
        
        // 货币转换 API
        register_rest_route('cbc/v1', '/currency/convert', array(
            'methods' => 'GET',
            'callback' => array($this, 'convert_currency'),
            'permission_callback' => '__return_true',
        ));
        
        // 运费计算 API
        register_rest_route('cbc/v1', '/shipping/calculate', array(
            'methods' => 'POST',
            'callback' => array($this, 'calculate_shipping'),
            'permission_callback' => '__return_true',
        ));
        
        // 统计数据 API（管理后台使用）
        register_rest_route('cbc/v1', '/stats/dashboard', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_dashboard_stats'),
            'permission_callback' => array($this, 'check_admin_auth'),
        ));
    }
    
    public function check_auth($request) {
        return is_user_logged_in();
    }
    
    public function check_admin_auth($request) {
        return current_user_can('manage_woocommerce');
    }
    
    public function get_products($request) {
        $args = array(
            'status' => 'publish',
            'limit' => $request->get_param('per_page') ?: 10,
            'page' => $request->get_param('page') ?: 1,
            'category' => $request->get_param('category') ?: '',
            'orderby' => $request->get_param('orderby') ?: 'date',
            'order' => $request->get_param('order') ?: 'DESC',
        );
        
        $products = wc_get_products($args);
        $data = array();
        
        foreach ($products as $product) {
            $data[] = $this->format_product($product);
        }
        
        return new WP_REST_Response($data, 200);
    }
    
    public function get_product($request) {
        $product = wc_get_product($request['id']);
        
        if (!$product) {
            return new WP_Error('not_found', __('商品未找到', 'cross-border-commerce'), array('status' => 404));
        }
        
        return new WP_REST_Response($this->format_product($product), 200);
    }
    
    private function format_product($product) {
        return array(
            'id' => $product->get_id(),
            'name' => $product->get_name(),
            'slug' => $product->get_slug(),
            'description' => $product->get_description(),
            'short_description' => $product->get_short_description(),
            'price' => $product->get_price(),
            'regular_price' => $product->get_regular_price(),
            'sale_price' => $product->get_sale_price(),
            'currency' => get_woocommerce_currency(),
            'stock_status' => $product->get_stock_status(),
            'stock_quantity' => $product->get_stock_quantity(),
            'images' => $this->get_product_images($product),
            'categories' => $this->get_product_categories($product),
            'attributes' => $product->get_attributes(),
            'created_at' => $product->get_date_created() ? $product->get_date_created()->format('Y-m-d H:i:s') : null,
        );
    }
    
    private function get_product_images($product) {
        $images = array();
        $attachment_ids = $product->get_gallery_image_ids();
        
        // 添加主图
        if ($product->get_image_id()) {
            array_unshift($attachment_ids, $product->get_image_id());
        }
        
        foreach ($attachment_ids as $attachment_id) {
            $images[] = array(
                'id' => $attachment_id,
                'src' => wp_get_attachment_url($attachment_id),
                'thumbnail' => wp_get_attachment_image_url($attachment_id, 'thumbnail'),
            );
        }
        
        return $images;
    }
    
    private function get_product_categories($product) {
        $categories = array();
        $term_ids = $product->get_category_ids();
        
        foreach ($term_ids as $term_id) {
            $term = get_term($term_id, 'product_cat');
            if ($term && !is_wp_error($term)) {
                $categories[] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                );
            }
        }
        
        return $categories;
    }
    
    public function get_categories($request) {
        $args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => $request->get_param('hide_empty') !== 'false',
            'orderby' => 'name',
            'order' => 'ASC',
        );
        
        $terms = get_terms($args);
        $data = array();
        
        foreach ($terms as $term) {
            $data[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
                'description' => $term->description,
                'count' => $term->count,
                'parent' => $term->parent,
            );
        }
        
        return new WP_REST_Response($data, 200);
    }
    
    public function handle_orders($request) {
        if ($request->get_method() === 'GET') {
            return $this->get_orders($request);
        }
        return $this->create_order($request);
    }
    
    private function get_orders($request) {
        $user_id = get_current_user_id();
        
        $args = array(
            'customer' => $user_id,
            'limit' => $request->get_param('per_page') ?: 10,
            'page' => $request->get_param('page') ?: 1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        $orders = wc_get_orders($args);
        $data = array();
        
        foreach ($orders as $order) {
            $data[] = $this->format_order($order);
        }
        
        return new WP_REST_Response($data, 200);
    }
    
    private function create_order($request) {
        $params = $request->get_json_params();
        
        $order = wc_create_order(array(
            'customer_id' => get_current_user_id(),
        ));
        
        if (is_wp_error($order)) {
            return $order;
        }
        
        // 添加商品
        if (!empty($params['items'])) {
            foreach ($params['items'] as $item) {
                $product = wc_get_product($item['product_id']);
                if ($product) {
                    $order->add_product($product, $item['quantity'] ?? 1);
                }
            }
        }
        
        // 设置地址
        if (!empty($params['billing'])) {
            $order->set_address($params['billing'], 'billing');
        }
        if (!empty($params['shipping'])) {
            $order->set_address($params['shipping'], 'shipping');
        }
        
        $order->calculate_totals();
        $order->save();
        
        return new WP_REST_Response($this->format_order($order), 201);
    }
    
    private function format_order($order) {
        return array(
            'id' => $order->get_id(),
            'number' => $order->get_order_number(),
            'status' => $order->get_status(),
            'total' => $order->get_total(),
            'currency' => $order->get_currency(),
            'items' => $this->get_order_items($order),
            'billing' => $order->get_address('billing'),
            'shipping' => $order->get_address('shipping'),
            'created_at' => $order->get_date_created() ? $order->get_date_created()->format('Y-m-d H:i:s') : null,
        );
    }
    
    private function get_order_items($order) {
        $items = array();
        
        foreach ($order->get_items() as $item) {
            $items[] = array(
                'id' => $item->get_id(),
                'product_id' => $item->get_product_id(),
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'total' => $item->get_total(),
            );
        }
        
        return $items;
    }
    
    public function convert_currency($request) {
        $from = $request->get_param('from') ?: 'USD';
        $to = $request->get_param('to') ?: 'CNY';
        $amount = floatval($request->get_param('amount') ?: 1);
        
        // 简单的汇率转换（实际项目中应使用实时汇率 API）
        $rates = array(
            'USD' => 1,
            'CNY' => 7.2,
            'EUR' => 0.92,
            'GBP' => 0.79,
            'JPY' => 149.5,
        );
        
        if (!isset($rates[$from]) || !isset($rates[$to])) {
            return new WP_Error('invalid_currency', __('不支持的货币类型', 'cross-border-commerce'), array('status' => 400));
        }
        
        $converted = $amount / $rates[$from] * $rates[$to];
        
        return new WP_REST_Response(array(
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'converted' => round($converted, 2),
            'rate' => round($rates[$to] / $rates[$from], 4),
        ), 200);
    }
    
    public function calculate_shipping($request) {
        $params = $request->get_json_params();
        
        $country = $params['country'] ?? 'CN';
        $weight = floatval($params['weight'] ?? 1);
        
        // 简单的运费计算（实际项目中应对接物流 API）
        $base_rates = array(
            'CN' => 0,
            'US' => 15,
            'UK' => 18,
            'DE' => 16,
            'JP' => 12,
            'AU' => 20,
        );
        
        $base = $base_rates[$country] ?? 25;
        $shipping_cost = $base + ($weight * 2);
        
        return new WP_REST_Response(array(
            'country' => $country,
            'weight' => $weight,
            'cost' => round($shipping_cost, 2),
            'currency' => 'USD',
            'estimated_days' => $country === 'CN' ? '3-5' : '7-14',
        ), 200);
    }
    
    public function get_dashboard_stats($request) {
        global $wpdb;
        
        // 获取今日订单数
        $today_orders = wc_get_orders(array(
            'date_created' => '>' . date('Y-m-d 00:00:00'),
            'return' => 'ids',
        ));
        
        // 获取本月销售额
        $month_start = date('Y-m-01 00:00:00');
        $month_orders = wc_get_orders(array(
            'date_created' => '>' . $month_start,
            'status' => array('completed', 'processing'),
        ));
        
        $month_revenue = 0;
        foreach ($month_orders as $order) {
            $month_revenue += $order->get_total();
        }
        
        // 获取商品总数
        $products_count = wp_count_posts('product')->publish;
        
        // 获取用户总数
        $users_count = count_users()['total_users'];
        
        return new WP_REST_Response(array(
            'today_orders' => count($today_orders),
            'month_revenue' => round($month_revenue, 2),
            'products_count' => $products_count,
            'users_count' => $users_count,
            'currency' => get_woocommerce_currency(),
        ), 200);
    }
}

// 初始化插件
Cross_Border_Commerce::get_instance();
