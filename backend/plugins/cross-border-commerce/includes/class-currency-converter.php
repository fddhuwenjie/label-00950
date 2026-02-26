<?php
/**
 * 货币转换类
 * 
 * 注意：本类为简化实现，使用静态汇率数据用于演示。
 * 生产环境应对接实时汇率 API（如 Open Exchange Rates、Fixer.io 等）。
 */

if (!defined('ABSPATH')) {
    exit;
}

class CBC_Currency_Converter {
    
    private static $instance = null;
    private $rates = array();
    private $cache_key = 'cbc_exchange_rates';
    private $cache_duration = 3600; // 1小时
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_rates();
    }
    
    private function init_rates() {
        // 从缓存获取汇率
        $cached_rates = get_transient($this->cache_key);
        
        if ($cached_rates) {
            $this->rates = $cached_rates;
        } else {
            // 默认汇率（基于 USD）
            $this->rates = array(
                'USD' => 1.0000,
                'CNY' => 7.2000,
                'EUR' => 0.9200,
                'GBP' => 0.7900,
                'JPY' => 149.5000,
                'AUD' => 1.5200,
                'CAD' => 1.3500,
                'HKD' => 7.8200,
                'SGD' => 1.3400,
                'KRW' => 1320.0000,
            );
            
            set_transient($this->cache_key, $this->rates, $this->cache_duration);
        }
    }
    
    public function convert($amount, $from, $to) {
        if (!isset($this->rates[$from]) || !isset($this->rates[$to])) {
            return false;
        }
        
        // 先转换为 USD，再转换为目标货币
        $usd_amount = $amount / $this->rates[$from];
        $converted = $usd_amount * $this->rates[$to];
        
        return round($converted, 2);
    }
    
    public function get_rate($from, $to) {
        if (!isset($this->rates[$from]) || !isset($this->rates[$to])) {
            return false;
        }
        
        return round($this->rates[$to] / $this->rates[$from], 4);
    }
    
    public function get_supported_currencies() {
        return array_keys($this->rates);
    }
    
    public function format_price($amount, $currency) {
        $symbols = array(
            'USD' => '$',
            'CNY' => '¥',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'HKD' => 'HK$',
            'SGD' => 'S$',
            'KRW' => '₩',
        );
        
        $symbol = $symbols[$currency] ?? $currency . ' ';
        
        return $symbol . number_format($amount, 2);
    }
}

// 初始化
CBC_Currency_Converter::get_instance();
