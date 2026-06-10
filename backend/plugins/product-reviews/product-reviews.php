<?php
/**
 * Plugin Name: 商品评价与晒单
 * Plugin URI: https://example.com/product-reviews
 * Description: 为 WooCommerce 商品添加评价与晒单功能，支持评分、文字评论和图片上传
 * Version: 1.0.0
 * Author: Cross Border Team
 * Text Domain: product-reviews
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * WC requires at least: 8.0
 */

if (!defined('ABSPATH')) exit;

define('PR_VERSION', '1.0.0');
define('PR_PLUGIN_DIR', plugin_dir_path(__FILE__));

class Product_Reviews {
    private static $instance = null;
    private $table_name;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'cbc_product_reviews';

        add_action('rest_api_init', array($this, 'register_routes'));

        register_activation_hook(__FILE__, array($this, 'activate'));
    }

    public function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            order_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            rating tinyint(1) NOT NULL DEFAULT 5,
            content text NOT NULL,
            images longtext DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY product_id (product_id),
            KEY order_product_user (order_id, product_id, user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function get_user_from_token($request) {
        $auth = $request->get_header('Authorization');
        if (!$auth) return null;
        $token = str_replace('Bearer ', '', $auth);
        if (empty($token)) return null;

        $secret = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : 'default-secret';

        try {
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($secret, 'HS256'));
            $user_id = intval($decoded->sub ?? 0);
            if (!$user_id) return null;
            $user = get_user_by('ID', $user_id);
            return $user ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function check_user_permission($request) {
        $user = $this->get_user_from_token($request);
        return $user ? true : new WP_Error('unauthorized', '未授权', array('status' => 401));
    }

    public function register_routes() {
        $namespace = 'cbc/v1';

        register_rest_route($namespace, '/reviews', array(
            'methods' => 'POST',
            'callback' => array($this, 'submit_review'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));

        register_rest_route($namespace, '/reviews', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_reviews'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route($namespace, '/reviews/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_review_stats'),
            'permission_callback' => '__return_true',
        ));

        register_rest_route($namespace, '/reviews/upload', array(
            'methods' => 'POST',
            'callback' => array($this, 'upload_review_image'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));

        register_rest_route($namespace, '/reviews/check', array(
            'methods' => 'GET',
            'callback' => array($this, 'check_can_review'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));
    }

    public function submit_review($request) {
        global $wpdb;
        $user = $this->get_user_from_token($request);
        $params = $request->get_json_params();

        $product_id = intval($params['product_id'] ?? 0);
        $order_id = intval($params['order_id'] ?? 0);
        $rating = intval($params['rating'] ?? 0);
        $content = sanitize_textarea_field($params['content'] ?? '');
        $images = $params['images'] ?? array();

        if (!$product_id || !$order_id) {
            return new WP_Error('missing_fields', '商品ID和订单ID不能为空', array('status' => 400));
        }

        if ($rating < 1 || $rating > 5) {
            return new WP_Error('invalid_rating', '评分必须在1-5之间', array('status' => 400));
        }

        if (empty($content)) {
            return new WP_Error('empty_content', '评价内容不能为空', array('status' => 400));
        }

        if (mb_strlen($content) > 500) {
            return new WP_Error('content_too_long', '评价内容不能超过500字', array('status' => 400));
        }

        if (count($images) > 3) {
            return new WP_Error('too_many_images', '最多上传3张图片', array('status' => 400));
        }

        $wc_check = $this->ensure_wc_loaded();
        if (is_wp_error($wc_check)) return $wc_check;

        $order = wc_get_order($order_id);
        if (!$order) {
            return new WP_Error('order_not_found', '订单不存在', array('status' => 404));
        }

        if ($order->get_customer_id() !== $user->ID) {
            return new WP_Error('not_owner', '只能评价自己的订单', array('status' => 403));
        }

        if ($order->get_status() !== 'completed') {
            return new WP_Error('order_not_completed', '只能评价已完成的订单', array('status' => 400));
        }

        $product_in_order = false;
        foreach ($order->get_items() as $item) {
            if ($item->get_product_id() === $product_id) {
                $product_in_order = true;
                break;
            }
        }
        if (!$product_in_order) {
            return new WP_Error('product_not_in_order', '该商品不在该订单中', array('status' => 400));
        }

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE order_id = %d AND product_id = %d AND user_id = %d",
            $order_id, $product_id, $user->ID
        ));
        if ($existing) {
            return new WP_Error('already_reviewed', '您已经评价过该商品', array('status' => 409));
        }

        $sanitized_images = array();
        foreach ($images as $img) {
            $sanitized_images[] = esc_url_raw($img);
        }

        $inserted = $wpdb->insert(
            $this->table_name,
            array(
                'product_id' => $product_id,
                'order_id' => $order_id,
                'user_id' => $user->ID,
                'rating' => $rating,
                'content' => $content,
                'images' => wp_json_encode($sanitized_images),
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%d', '%d', '%d', '%s', '%s', '%s')
        );

        if (!$inserted) {
            return new WP_Error('insert_failed', '评价提交失败', array('status' => 500));
        }

        return rest_ensure_response(array(
            'id' => $wpdb->insert_id,
            'message' => '评价提交成功',
        ));
    }

    public function get_reviews($request) {
        global $wpdb;

        $product_id = intval($request->get_param('product_id') ?? 0);
        if (!$product_id) {
            return new WP_Error('missing_product_id', '商品ID不能为空', array('status' => 400));
        }

        $rating_filter = sanitize_text_field($request->get_param('rating') ?? 'all');
        $page = max(1, intval($request->get_param('page') ?? 1));
        $per_page = min(50, max(1, intval($request->get_param('per_page') ?? 10)));
        $offset = ($page - 1) * $per_page;

        $where = $wpdb->prepare("WHERE product_id = %d", $product_id);

        if ($rating_filter === 'good') {
            $where .= " AND rating = 5";
        } elseif ($rating_filter === 'medium') {
            $where .= " AND rating IN (3, 4)";
        } elseif ($rating_filter === 'bad') {
            $where .= " AND rating IN (1, 2)";
        }

        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} $where");

        $reviews = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} $where ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page, $offset
        ));

        $data = array();
        foreach ($reviews as $review) {
            $user = get_user_by('ID', $review->user_id);
            $user_name = $user ? $user->display_name : '匿名用户';
            if (mb_strlen($user_name) > 1) {
                $user_name = mb_substr($user_name, 0, 1) . str_repeat('*', mb_strlen($user_name) - 1);
            }

            $images = json_decode($review->images, true);
            if (!is_array($images)) $images = array();

            $data[] = array(
                'id' => intval($review->id),
                'user_name' => $user_name,
                'rating' => intval($review->rating),
                'content' => $review->content,
                'images' => $images,
                'created_at' => $review->created_at,
            );
        }

        return rest_ensure_response(array(
            'reviews' => $data,
            'total' => intval($total),
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page),
        ));
    }

    public function get_review_stats($request) {
        global $wpdb;

        $product_ids = sanitize_text_field($request->get_param('product_id') ?? '');
        if (empty($product_ids)) {
            return new WP_Error('missing_product_id', '商品ID不能为空', array('status' => 400));
        }

        $ids = array_map('intval', explode(',', $product_ids));
        $ids = array_filter($ids, function($id) { return $id > 0; });

        if (empty($ids)) {
            return new WP_Error('invalid_product_id', '无效的商品ID', array('status' => 400));
        }

        $result = array();

        foreach ($ids as $pid) {
            $stats = $wpdb->get_row($wpdb->prepare(
                "SELECT COUNT(*) as total_count, AVG(rating) as avg_rating FROM {$this->table_name} WHERE product_id = %d",
                $pid
            ));

            $distribution = array();
            for ($i = 1; $i <= 5; $i++) {
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->table_name} WHERE product_id = %d AND rating = %d",
                    $pid, $i
                ));
                $distribution[$i] = intval($count);
            }

            $result[$pid] = array(
                'average_rating' => $stats->total_count > 0 ? round(floatval($stats->avg_rating), 1) : 0,
                'total_count' => intval($stats->total_count),
                'distribution' => $distribution,
            );
        }

        return rest_ensure_response($result);
    }

    public function upload_review_image($request) {
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

    public function check_can_review($request) {
        global $wpdb;
        $user = $this->get_user_from_token($request);

        $order_id = intval($request->get_param('order_id') ?? 0);
        $product_id = intval($request->get_param('product_id') ?? 0);

        if (!$order_id || !$product_id) {
            return rest_ensure_response(array(
                'can_review' => false,
                'reason' => '参数不完整',
            ));
        }

        $wc_check = $this->ensure_wc_loaded();
        if (is_wp_error($wc_check)) {
            return rest_ensure_response(array(
                'can_review' => false,
                'reason' => '系统繁忙，请稍后',
            ));
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return rest_ensure_response(array(
                'can_review' => false,
                'reason' => '订单不存在',
            ));
        }

        if ($order->get_customer_id() !== $user->ID) {
            return rest_ensure_response(array(
                'can_review' => false,
                'reason' => '只能评价自己的订单',
            ));
        }

        if ($order->get_status() !== 'completed') {
            return rest_ensure_response(array(
                'can_review' => false,
                'reason' => '只能评价已完成的订单',
            ));
        }

        $product_in_order = false;
        foreach ($order->get_items() as $item) {
            if ($item->get_product_id() === $product_id) {
                $product_in_order = true;
                break;
            }
        }
        if (!$product_in_order) {
            return rest_ensure_response(array(
                'can_review' => false,
                'reason' => '该商品不在该订单中',
            ));
        }

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE order_id = %d AND product_id = %d AND user_id = %d",
            $order_id, $product_id, $user->ID
        ));
        if ($existing) {
            return rest_ensure_response(array(
                'can_review' => false,
                'reason' => '您已经评价过该商品',
            ));
        }

        return rest_ensure_response(array(
            'can_review' => true,
            'reason' => '',
        ));
    }

    private function ensure_wc_loaded() {
        if (!function_exists('wc_get_products')) {
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
}

Product_Reviews::get_instance();
