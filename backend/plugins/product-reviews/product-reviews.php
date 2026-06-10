<?php
/**
 * Plugin Name: 商品评价与晒单
 * Plugin URI: https://example.com/product-reviews
 * Description: 为跨境电商商城提供商品评价与晒单功能：用户对已完成订单中的商品进行评分（1-5 星）、文字评论、最多 3 张图片，详情页展示评价列表、综合评分及评价数量。通过自定义 REST API 提供，不修改 WooCommerce 核心文件。
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

class Product_Reviews_Plugin {
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

        // 加载共享 JWT 库（沿用 cross-border-commerce 插件的 composer 依赖）
        $this->load_shared_dependencies();

        register_activation_hook(__FILE__, array($this, 'activate'));

        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * 沿用 cross-border-commerce 插件下的 firebase/php-jwt 库，避免重复安装依赖
     */
    private function load_shared_dependencies() {
        if (!class_exists('\\Firebase\\JWT\\JWT')) {
            $autoload = WP_PLUGIN_DIR . '/cross-border-commerce/vendor/autoload.php';
            if (file_exists($autoload)) {
                require_once $autoload;
            }
        }
    }

    /**
     * 插件激活：建表
     */
    public function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table = $this->table_name;

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            order_id BIGINT(20) UNSIGNED NOT NULL,
            order_item_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            rating TINYINT(1) UNSIGNED NOT NULL,
            content TEXT NOT NULL,
            images TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_order_product (order_id, product_id),
            KEY idx_product (product_id),
            KEY idx_user (user_id),
            KEY idx_rating (rating)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * 注册 REST 路由
     */
    public function register_routes() {
        $namespace = 'pr/v1';

        // 获取某商品的评价列表
        register_rest_route($namespace, '/reviews', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_reviews'),
            'permission_callback' => '__return_true',
        ));

        // 创建评价（需登录）
        register_rest_route($namespace, '/reviews', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_review'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));

        // 单个商品的综合评分汇总
        register_rest_route($namespace, '/products/(?P<id>\d+)/summary', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_product_summary'),
            'permission_callback' => '__return_true',
        ));

        // 批量获取多个商品的综合评分（用于商品列表页）
        register_rest_route($namespace, '/products/summary', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_products_summary_batch'),
            'permission_callback' => '__return_true',
        ));

        // 获取订单中可评价的商品（需登录）
        register_rest_route($namespace, '/orders/(?P<id>\d+)/reviewable', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_order_reviewable'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));

        // 上传评价图片（需登录，调用 WordPress 媒体接口）
        register_rest_route($namespace, '/upload', array(
            'methods' => 'POST',
            'callback' => array($this, 'upload_image'),
            'permission_callback' => array($this, 'check_user_permission'),
        ));
    }

    // ===== 权限/认证 =====

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
            if (!class_exists('\\Firebase\\JWT\\JWT')) return null;
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($secret, 'HS256'));
            $user_id = intval($decoded->sub ?? 0);
            if (!$user_id) return null;
            $user = get_user_by('ID', $user_id);
            return $user ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // ===== 评价列表 =====

    public function get_reviews($request) {
        global $wpdb;
        $product_id = intval($request->get_param('product_id') ?: 0);
        if ($product_id <= 0) {
            return new WP_Error('invalid_params', '缺少 product_id 参数', array('status' => 400));
        }

        $filter = sanitize_text_field($request->get_param('filter') ?: 'all');
        $per_page = max(1, min(50, intval($request->get_param('per_page') ?: 10)));
        $page = max(1, intval($request->get_param('page') ?: 1));
        $offset = ($page - 1) * $per_page;

        $where = $wpdb->prepare('product_id = %d', $product_id);
        if ($filter === 'good') {
            $where .= ' AND rating = 5';
        } elseif ($filter === 'mid') {
            $where .= ' AND rating BETWEEN 3 AND 4';
        } elseif ($filter === 'bad') {
            $where .= ' AND rating BETWEEN 1 AND 2';
        }

        $total = intval($wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE {$where}"));

        $rows = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} WHERE {$where} ORDER BY created_at DESC LIMIT {$per_page} OFFSET {$offset}",
            ARRAY_A
        );

        $reviews = array();
        foreach ($rows as $row) {
            $reviews[] = $this->format_review($row);
        }

        // 分布统计
        $distribution = $this->get_rating_distribution($product_id);

        return rest_ensure_response(array(
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'reviews' => $reviews,
            'distribution' => $distribution,
        ));
    }

    private function format_review($row) {
        $user = get_user_by('ID', intval($row['user_id']));
        $nickname = $user ? $user->display_name : '匿名用户';
        // 昵称脱敏：保留首尾，中间用*
        $masked = $this->mask_nickname($nickname);

        $images = array();
        if (!empty($row['images'])) {
            $decoded = json_decode($row['images'], true);
            if (is_array($decoded)) $images = $decoded;
        }

        return array(
            'id' => intval($row['id']),
            'product_id' => intval($row['product_id']),
            'order_id' => intval($row['order_id']),
            'user_id' => intval($row['user_id']),
            'nickname' => $masked,
            'avatar' => $user ? get_avatar_url($user->ID, array('size' => 80)) : '',
            'rating' => intval($row['rating']),
            'content' => $row['content'],
            'images' => $images,
            'created_at' => $row['created_at'],
        );
    }

    private function mask_nickname($name) {
        $name = trim((string)$name);
        if ($name === '') return '匿名用户';
        $len = mb_strlen($name);
        if ($len <= 1) return $name;
        if ($len === 2) return mb_substr($name, 0, 1) . '*';
        return mb_substr($name, 0, 1) . str_repeat('*', $len - 2) . mb_substr($name, -1);
    }

    private function get_rating_distribution($product_id) {
        global $wpdb;
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT rating, COUNT(*) AS cnt FROM {$this->table_name} WHERE product_id = %d GROUP BY rating",
                $product_id
            ),
            ARRAY_A
        );
        $dist = array('all' => 0, 'good' => 0, 'mid' => 0, 'bad' => 0);
        foreach ($rows as $r) {
            $rating = intval($r['rating']);
            $cnt = intval($r['cnt']);
            $dist['all'] += $cnt;
            if ($rating === 5) $dist['good'] += $cnt;
            elseif ($rating >= 3) $dist['mid'] += $cnt;
            else $dist['bad'] += $cnt;
        }
        return $dist;
    }

    // ===== 综合评分 =====

    public function get_product_summary($request) {
        $product_id = intval($request['id']);
        return rest_ensure_response($this->compute_summary($product_id));
    }

    public function get_products_summary_batch($request) {
        $ids_param = $request->get_param('ids');
        if (empty($ids_param)) return rest_ensure_response(array());
        $ids = array_filter(array_map('intval', explode(',', $ids_param)));
        if (empty($ids)) return rest_ensure_response(array());

        global $wpdb;
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $sql = $wpdb->prepare(
            "SELECT product_id, AVG(rating) AS avg_rating, COUNT(*) AS cnt
             FROM {$this->table_name}
             WHERE product_id IN ({$placeholders})
             GROUP BY product_id",
            $ids
        );
        $rows = $wpdb->get_results($sql, ARRAY_A);

        $result = array();
        foreach ($ids as $id) {
            $result[(string)$id] = array('average' => 0.0, 'count' => 0);
        }
        foreach ($rows as $r) {
            $result[(string)$r['product_id']] = array(
                'average' => round(floatval($r['avg_rating']), 1),
                'count' => intval($r['cnt']),
            );
        }
        return rest_ensure_response($result);
    }

    private function compute_summary($product_id) {
        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt
                 FROM {$this->table_name} WHERE product_id = %d",
                $product_id
            ),
            ARRAY_A
        );
        $avg = $row && $row['cnt'] > 0 ? round(floatval($row['avg_rating']), 1) : 0.0;
        $count = $row ? intval($row['cnt']) : 0;
        return array(
            'product_id' => $product_id,
            'average' => $avg,
            'count' => $count,
        );
    }

    // ===== 订单可评价商品 =====

    public function get_order_reviewable($request) {
        if (!function_exists('wc_get_order')) {
            return new WP_Error('wc_not_ready', 'WooCommerce 未加载', array('status' => 503));
        }
        $user = $this->get_user_from_token($request);
        $order_id = intval($request['id']);
        $order = wc_get_order($order_id);
        if (!$order) {
            return new WP_Error('not_found', '订单不存在', array('status' => 404));
        }
        if (intval($order->get_customer_id()) !== intval($user->ID)) {
            return new WP_Error('forbidden', '无权访问此订单', array('status' => 403));
        }
        if ($order->get_status() !== 'completed') {
            return new WP_Error('not_completed', '只有已完成的订单才能评价', array('status' => 400));
        }

        global $wpdb;
        $reviewed = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT product_id FROM {$this->table_name} WHERE order_id = %d AND user_id = %d",
                $order_id,
                $user->ID
            )
        );
        $reviewed = array_map('intval', $reviewed);

        $items = array();
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            if (!$product) continue;
            $product_id = $product->get_id();
            $image = $product->get_meta('_external_image');
            if (empty($image)) {
                $image_id = $product->get_image_id();
                $image = $image_id ? wp_get_attachment_url($image_id) : '';
            }
            $items[] = array(
                'order_item_id' => intval($item_id),
                'product_id' => $product_id,
                'name' => $item->get_name(),
                'image' => $image,
                'quantity' => $item->get_quantity(),
                'reviewed' => in_array($product_id, $reviewed, true),
            );
        }

        return rest_ensure_response(array(
            'order_id' => $order_id,
            'status' => $order->get_status(),
            'items' => $items,
        ));
    }

    // ===== 创建评价 =====

    public function create_review($request) {
        if (!function_exists('wc_get_order')) {
            return new WP_Error('wc_not_ready', 'WooCommerce 未加载', array('status' => 503));
        }
        $user = $this->get_user_from_token($request);
        $params = $request->get_json_params();

        $product_id = intval($params['product_id'] ?? 0);
        $order_id = intval($params['order_id'] ?? 0);
        $rating = intval($params['rating'] ?? 0);
        $content = isset($params['content']) ? sanitize_textarea_field($params['content']) : '';
        $images = isset($params['images']) && is_array($params['images']) ? array_slice($params['images'], 0, 3) : array();

        if ($product_id <= 0 || $order_id <= 0) {
            return new WP_Error('invalid_params', '缺少商品或订单 ID', array('status' => 400));
        }
        if ($rating < 1 || $rating > 5) {
            return new WP_Error('invalid_rating', '评分必须是 1-5 之间的整数', array('status' => 400));
        }
        if (mb_strlen($content) === 0) {
            return new WP_Error('invalid_content', '评论内容不能为空', array('status' => 400));
        }
        if (mb_strlen($content) > 2000) {
            return new WP_Error('invalid_content', '评论内容过长（最多 2000 字）', array('status' => 400));
        }

        // 校验图片为字符串 URL
        $clean_images = array();
        foreach ($images as $img) {
            if (is_string($img) && filter_var($img, FILTER_VALIDATE_URL)) {
                $clean_images[] = esc_url_raw($img);
            }
        }
        if (count($clean_images) > 3) {
            $clean_images = array_slice($clean_images, 0, 3);
        }

        // 校验订单
        $order = wc_get_order($order_id);
        if (!$order) {
            return new WP_Error('not_found', '订单不存在', array('status' => 404));
        }
        if (intval($order->get_customer_id()) !== intval($user->ID)) {
            return new WP_Error('forbidden', '无权评价此订单', array('status' => 403));
        }
        if ($order->get_status() !== 'completed') {
            return new WP_Error('not_completed', '只有已完成的订单才能评价', array('status' => 400));
        }

        // 校验商品在订单中
        $order_item_id = 0;
        foreach ($order->get_items() as $item_id => $item) {
            $p = $item->get_product();
            if ($p && intval($p->get_id()) === $product_id) {
                $order_item_id = intval($item_id);
                break;
            }
        }
        if ($order_item_id === 0) {
            return new WP_Error('not_in_order', '该商品不在此订单内', array('status' => 400));
        }

        // 唯一性校验：每个订单每件商品只能评价一次
        global $wpdb;
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE order_id = %d AND product_id = %d",
                $order_id,
                $product_id
            )
        );
        if ($exists) {
            return new WP_Error('already_reviewed', '该商品已在此订单中评价过', array('status' => 409));
        }

        $inserted = $wpdb->insert(
            $this->table_name,
            array(
                'product_id' => $product_id,
                'order_id' => $order_id,
                'order_item_id' => $order_item_id,
                'user_id' => $user->ID,
                'rating' => $rating,
                'content' => $content,
                'images' => wp_json_encode($clean_images),
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s')
        );

        if ($inserted === false) {
            return new WP_Error('db_error', '保存评价失败', array('status' => 500));
        }

        $id = intval($wpdb->insert_id);
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id),
            ARRAY_A
        );

        return rest_ensure_response($this->format_review($row));
    }

    // ===== 图片上传（调用 WordPress 媒体接口） =====

    public function upload_image($request) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $files = $request->get_file_params();
        if (empty($files['file'])) {
            return new WP_Error('no_file', '请选择文件', array('status' => 400));
        }

        // 限制类型与大小（5MB）
        $allowed_mimes = array('image/jpeg', 'image/png', 'image/webp', 'image/gif');
        $mime = isset($files['file']['type']) ? $files['file']['type'] : '';
        if (!in_array($mime, $allowed_mimes, true)) {
            return new WP_Error('invalid_type', '仅支持 JPG/PNG/WebP/GIF 图片', array('status' => 400));
        }
        $size = isset($files['file']['size']) ? intval($files['file']['size']) : 0;
        if ($size > 5 * 1024 * 1024) {
            return new WP_Error('too_large', '图片大小不能超过 5MB', array('status' => 400));
        }

        $_FILES['pr_review_image'] = $files['file'];
        $attachment_id = media_handle_upload('pr_review_image', 0);
        if (is_wp_error($attachment_id)) {
            return new WP_Error('upload_failed', $attachment_id->get_error_message(), array('status' => 500));
        }

        $url = wp_get_attachment_url($attachment_id);
        return rest_ensure_response(array(
            'id' => intval($attachment_id),
            'url' => $url,
        ));
    }
}

Product_Reviews_Plugin::get_instance();
