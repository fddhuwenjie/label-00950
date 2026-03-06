<?php
/**
 * 货币转换类
 * 
 * 优先对接 Open Exchange Rates / ExchangeRate-API 获取实时汇率。
 * 当 API 不可用时，自动降级为内置汇率数据（每小时缓存刷新）。
 */

if (!defined('ABSPATH')) {
    exit;
}

class CBC_Currency_Converter {
    
    private static $instance = null;
    private $rates = array();
    private $cache_key = 'cbc_exchange_rates';
    private $cache_duration = 3600; // 1小时缓存
    private $api_source = 'fallback'; // 记录数据来源
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_rates();
    }
    
    /**
     * 初始化汇率：优先从缓存读取，缓存过期则尝试从远程 API 获取
     */
    private function init_rates() {
        // 1. 尝试从 WordPress transient 缓存读取
        $cached = get_transient($this->cache_key);
        if ($cached && !empty($cached['rates'])) {
            $this->rates = $cached['rates'];
            $this->api_source = $cached['source'] ?? 'cache';
            return;
        }

        // 2. 尝试从远程 API 获取实时汇率
        $remote_rates = $this->fetch_remote_rates();
        if ($remote_rates) {
            $this->rates = $remote_rates;
            set_transient($this->cache_key, array(
                'rates'  => $this->rates,
                'source' => $this->api_source,
                'time'   => current_time('mysql'),
            ), $this->cache_duration);
            return;
        }

        // 3. 降级：使用内置汇率（基于 2025 年初市场参考值）
        $this->rates = $this->get_fallback_rates();
        $this->api_source = 'fallback';
        set_transient($this->cache_key, array(
            'rates'  => $this->rates,
            'source' => 'fallback',
            'time'   => current_time('mysql'),
        ), 600); // 降级数据只缓存 10 分钟，尽快重试
    }

    /**
     * 从远程 API 获取实时汇率（以 USD 为基准）
     * 依次尝试多个免费 API 源
     */
    private function fetch_remote_rates() {
        // 来源 1: ExchangeRate-API (免费，无需 key，每日 1500 次)
        $rates = $this->fetch_from_exchangerate_api();
        if ($rates) return $rates;

        // 来源 2: Open Exchange Rates (需要 APP_ID，可在 wp-config.php 中配置)
        $rates = $this->fetch_from_open_exchange_rates();
        if ($rates) return $rates;

        return false;
    }

    /**
     * ExchangeRate-API (https://open.er-api.com)
     * 免费、无需注册、每日限额 1500 次
     */
    private function fetch_from_exchangerate_api() {
        $url = 'https://open.er-api.com/v6/latest/USD';
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array('Accept' => 'application/json'),
        ));

        if (is_wp_error($response)) {
            error_log('[CBC Currency] ExchangeRate-API error: ' . $response->get_error_message());
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            error_log('[CBC Currency] ExchangeRate-API HTTP ' . $code);
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['rates']) || ($body['result'] ?? '') !== 'success') {
            return false;
        }

        // 只保留我们支持的币种
        $supported = array('USD','CNY','EUR','GBP','JPY','AUD','CAD','HKD','SGD','KRW','THB','MYR','VND','PHP','IDR','TWD','INR','BRL','RUB','MXN');
        $rates = array();
        foreach ($supported as $cur) {
            if (isset($body['rates'][$cur])) {
                $rates[$cur] = floatval($body['rates'][$cur]);
            }
        }

        if (count($rates) >= 5) {
            $this->api_source = 'exchangerate-api';
            error_log('[CBC Currency] Loaded ' . count($rates) . ' rates from ExchangeRate-API');
            return $rates;
        }

        return false;
    }

    /**
     * Open Exchange Rates (https://openexchangerates.org)
     * 需要在 wp-config.php 中定义 OXR_APP_ID
     */
    private function fetch_from_open_exchange_rates() {
        $app_id = defined('OXR_APP_ID') ? OXR_APP_ID : '';
        if (empty($app_id)) return false;

        $url = 'https://openexchangerates.org/api/latest.json?app_id=' . $app_id;
        $response = wp_remote_get($url, array('timeout' => 10));

        if (is_wp_error($response)) {
            error_log('[CBC Currency] OXR error: ' . $response->get_error_message());
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (empty($body['rates'])) return false;

        $supported = array('USD','CNY','EUR','GBP','JPY','AUD','CAD','HKD','SGD','KRW');
        $rates = array();
        foreach ($supported as $cur) {
            if (isset($body['rates'][$cur])) {
                $rates[$cur] = floatval($body['rates'][$cur]);
            }
        }

        if (count($rates) >= 5) {
            $this->api_source = 'open-exchange-rates';
            return $rates;
        }

        return false;
    }

    /**
     * 内置降级汇率（基于 2025 年初市场参考值）
     */
    private function get_fallback_rates() {
        return array(
            'USD' => 1.0000,
            'CNY' => 7.2500,
            'EUR' => 0.9200,
            'GBP' => 0.7900,
            'JPY' => 149.5000,
            'AUD' => 1.5300,
            'CAD' => 1.3600,
            'HKD' => 7.8200,
            'SGD' => 1.3400,
            'KRW' => 1320.0000,
        );
    }
    
    /**
     * 货币转换
     */
    public function convert($amount, $from, $to) {
        if (!isset($this->rates[$from]) || !isset($this->rates[$to])) {
            return false;
        }
        // 先转为 USD，再转为目标货币
        $usd_amount = $amount / $this->rates[$from];
        $converted = $usd_amount * $this->rates[$to];
        return round($converted, 2);
    }
    
    /**
     * 获取两种货币之间的汇率
     */
    public function get_rate($from, $to) {
        if (!isset($this->rates[$from]) || !isset($this->rates[$to])) {
            return false;
        }
        return round($this->rates[$to] / $this->rates[$from], 4);
    }
    
    /**
     * 获取支持的货币列表
     */
    public function get_supported_currencies() {
        return array_keys($this->rates);
    }

    /**
     * 获取汇率数据来源信息
     */
    public function get_rate_info() {
        $cached = get_transient($this->cache_key);
        return array(
            'source'     => $this->api_source,
            'currencies' => count($this->rates),
            'updated_at' => $cached['time'] ?? null,
        );
    }
    
    /**
     * 格式化价格显示
     */
    public function format_price($amount, $currency) {
        $symbols = array(
            'USD' => '$',    'CNY' => '¥',    'EUR' => '€',
            'GBP' => '£',   'JPY' => '¥',    'AUD' => 'A$',
            'CAD' => 'C$',  'HKD' => 'HK$',  'SGD' => 'S$',
            'KRW' => '₩',   'THB' => '฿',    'INR' => '₹',
            'BRL' => 'R$',  'RUB' => '₽',    'MXN' => 'MX$',
        );
        $symbol = $symbols[$currency] ?? $currency . ' ';
        // 日元和韩元不需要小数
        $decimals = in_array($currency, array('JPY', 'KRW')) ? 0 : 2;
        return $symbol . number_format($amount, $decimals);
    }

    /**
     * 强制刷新汇率缓存
     */
    public function refresh_rates() {
        delete_transient($this->cache_key);
        $this->init_rates();
        return $this->get_rate_info();
    }
}

CBC_Currency_Converter::get_instance();
