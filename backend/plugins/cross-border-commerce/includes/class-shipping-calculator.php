<?php
/**
 * 国际物流运费计算类
 */

if (!defined('ABSPATH')) {
    exit;
}

class CBC_Shipping_Calculator {
    
    private static $instance = null;
    
    // 物流区域配置
    private $zones = array(
        'zone_1' => array('CN', 'HK', 'TW', 'MO'), // 大中华区
        'zone_2' => array('JP', 'KR', 'SG', 'MY', 'TH', 'VN', 'PH', 'ID'), // 亚洲
        'zone_3' => array('US', 'CA', 'MX'), // 北美
        'zone_4' => array('GB', 'DE', 'FR', 'IT', 'ES', 'NL', 'BE'), // 欧洲
        'zone_5' => array('AU', 'NZ'), // 大洋洲
    );
    
    // 基础运费（按区域）
    private $base_rates = array(
        'zone_1' => 5,
        'zone_2' => 10,
        'zone_3' => 15,
        'zone_4' => 18,
        'zone_5' => 20,
    );
    
    // 每公斤追加费用
    private $per_kg_rates = array(
        'zone_1' => 1,
        'zone_2' => 2,
        'zone_3' => 3,
        'zone_4' => 3.5,
        'zone_5' => 4,
    );
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function calculate($country, $weight, $method = 'standard') {
        $zone = $this->get_zone($country);
        
        if (!$zone) {
            $zone = 'zone_5'; // 默认最远区域
        }
        
        $base = $this->base_rates[$zone];
        $per_kg = $this->per_kg_rates[$zone];
        
        // 计算运费
        $shipping_cost = $base + ($weight * $per_kg);
        
        // 快递加价
        if ($method === 'express') {
            $shipping_cost *= 1.8;
        }
        
        // 预计送达时间
        $estimated_days = $this->get_estimated_days($zone, $method);
        
        return array(
            'cost' => round($shipping_cost, 2),
            'currency' => 'USD',
            'zone' => $zone,
            'method' => $method,
            'estimated_days' => $estimated_days,
        );
    }
    
    private function get_zone($country) {
        foreach ($this->zones as $zone => $countries) {
            if (in_array($country, $countries)) {
                return $zone;
            }
        }
        return null;
    }
    
    private function get_estimated_days($zone, $method) {
        $days = array(
            'zone_1' => array('standard' => '3-5', 'express' => '1-2'),
            'zone_2' => array('standard' => '5-7', 'express' => '2-3'),
            'zone_3' => array('standard' => '7-14', 'express' => '3-5'),
            'zone_4' => array('standard' => '10-15', 'express' => '4-6'),
            'zone_5' => array('standard' => '10-18', 'express' => '5-7'),
        );
        
        return $days[$zone][$method] ?? '15-30';
    }
    
    public function get_available_methods() {
        return array(
            'standard' => __('标准物流', 'cross-border-commerce'),
            'express' => __('国际快递', 'cross-border-commerce'),
        );
    }
}

// 初始化
CBC_Shipping_Calculator::get_instance();
