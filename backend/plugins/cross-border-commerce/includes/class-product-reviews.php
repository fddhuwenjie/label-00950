<?php
/**
 * 商品评价管理类
 *
 * @package Cross_Border_Commerce
 */

if (!defined('ABSPATH')) exit;

class CBC_Product_Reviews {
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
    }

    public function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id bigint(20) UNSIGNED NOT NULL,
            user_id bigint(20) UNSIGNED NOT NULL,
            order_id bigint(20) UNSIGNED NOT NULL,
            order_item_id bigint(20) UNSIGNED NOT NULL,
            rating tinyint(1) UNSIGNED NOT NULL DEFAULT 5,
            comment text NULL,
            images longtext NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY user_id (user_id),
            KEY order_id (order_id),
            KEY order_item_id (order_item_id),
            UNIQUE KEY order_product_review (order_id, order_item_id, product_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function get_reviews($product_id, $args = array()) {
        global $wpdb;

        $defaults = array(
            'page' => 1,
            'per_page' => 10,
            'rating_filter' => 'all',
        );
        $args = wp_parse_args($args, $defaults);

        $where = $wpdb->prepare('WHERE product_id = %d', $product_id);

        if ($args['rating_filter'] === 'good') {
            $where .= ' AND rating = 5';
        } elseif ($args['rating_filter'] === 'medium') {
            $where .= ' AND rating >= 3 AND rating <= 4';
        } elseif ($args['rating_filter'] === 'bad') {
            $where .= ' AND rating >= 1 AND rating <= 2';
        }

        $offset = ($args['page'] - 1) * $args['per_page'];

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $args['per_page'],
            $offset
        );

        $results = $wpdb->get_results($query);

        $count_query = "SELECT COUNT(*) FROM {$this->table_name} {$where}";
        $total = $wpdb->get_var($count_query);

        return array(
            'reviews' => $this->format_reviews($results),
            'total' => intval($total),
            'page' => intval($args['page']),
            'per_page' => intval($args['per_page']),
            'total_pages' => ceil($total / $args['per_page']),
        );
    }

    public function get_review_summary($product_id) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT 
                COUNT(*) as total_count,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
             FROM {$this->table_name} 
             WHERE product_id = %d",
            $product_id
        );

        $result = $wpdb->get_row($query);

        return array(
            'total_count' => intval($result->total_count),
            'average_rating' => $result->total_count > 0 ? round(floatval($result->average_rating), 1) : 0,
            'rating_distribution' => array(
                '5' => intval($result->five_star),
                '4' => intval($result->four_star),
                '3' => intval($result->three_star),
                '2' => intval($result->two_star),
                '1' => intval($result->one_star),
            ),
        );
    }

    public function add_review($user_id, $product_id, $order_id, $order_item_id, $rating, $comment = '', $images = array()) {
        global $wpdb;

        if (!$this->can_review($user_id, $order_id, $order_item_id, $product_id)) {
            return new WP_Error('cannot_review', '您无法评价此商品', array('status' => 403));
        }

        $result = $wpdb->insert(
            $this->table_name,
            array(
                'product_id' => $product_id,
                'user_id' => $user_id,
                'order_id' => $order_id,
                'order_item_id' => $order_item_id,
                'rating' => $rating,
                'comment' => $comment,
                'images' => maybe_serialize($images),
            ),
            array('%d', '%d', '%d', '%d', '%d', '%s', '%s')
        );

        if ($result === false) {
            return new WP_Error('review_failed', '评价提交失败', array('status' => 500));
        }

        $review_id = $wpdb->insert_id;

        return $this->get_review_by_id($review_id);
    }

    public function can_review($user_id, $order_id, $order_item_id, $product_id) {
        global $wpdb;

        $order = wc_get_order($order_id);
        if (!$order) return false;

        if ($order->get_customer_id() != $user_id) return false;

        if (!in_array($order->get_status(), array('completed'))) return false;

        $query = $wpdb->prepare(
            "SELECT id FROM {$this->table_name} 
             WHERE order_id = %d AND order_item_id = %d AND product_id = %d",
            $order_id,
            $order_item_id,
            $product_id
        );

        $existing = $wpdb->get_var($query);
        if ($existing) return false;

        return true;
    }

    public function get_user_order_items_review_status($user_id, $order_id) {
        global $wpdb;

        $order = wc_get_order($order_id);
        if (!$order || $order->get_customer_id() != $user_id) {
            return array();
        }

        $reviewed_items = array();
        $query = $wpdb->prepare(
            "SELECT order_item_id, product_id FROM {$this->table_name} WHERE order_id = %d AND user_id = %d",
            $order_id,
            $user_id
        );
        $results = $wpdb->get_results($query);
        foreach ($results as $row) {
            $reviewed_items[] = array(
                'order_item_id' => intval($row->order_item_id),
                'product_id' => intval($row->product_id),
            );
        }

        return $reviewed_items;
    }

    private function get_review_by_id($review_id) {
        global $wpdb;
        $query = $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $review_id);
        $result = $wpdb->get_row($query);
        if (!$result) return null;
        return $this->format_review($result);
    }

    private function format_reviews($results) {
        return array_map(array($this, 'format_review'), $results);
    }

    private function format_review($review) {
        $user = get_user_by('ID', $review->user_id);
        $images = maybe_unserialize($review->images);
        if (!is_array($images)) $images = array();

        return array(
            'id' => intval($review->id),
            'product_id' => intval($review->product_id),
            'user_id' => intval($review->user_id),
            'user_name' => $user ? $user->display_name : '匿名用户',
            'user_avatar' => $user ? get_avatar_url($user->ID, array('size' => 80)) : '',
            'rating' => intval($review->rating),
            'comment' => $review->comment,
            'images' => $images,
            'created_at' => $review->created_at,
        );
    }
}
