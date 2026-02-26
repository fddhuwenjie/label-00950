<?php
/**
 * 关税计算类
 * 
 * 注意：本类为简化实现，使用预设的税率数据进行演示。
 * 生产环境应对接海关数据库或专业关税计算服务，并定期更新税率。
 * 实际关税以各国海关核定为准。
 */

if (!defined('ABSPATH')) {
    exit;
}

class CBC_Duty_Calculator {
    
    private static $instance = null;
    
    // 各国关税税率（简化版）
    private $duty_rates = array(
        'CN' => array(
            'default' => 0.13,  // 13% 综合税率
            'electronics' => 0.15,
            'clothing' => 0.20,
            'food' => 0.25,
            'cosmetics' => 0.30,
            'threshold' => 50, // 免税额度（USD）
        ),
        'US' => array(
            'default' => 0.05,
            'electronics' => 0.025,
            'clothing' => 0.12,
            'threshold' => 800,
        ),
        'EU' => array(
            'default' => 0.20,
            'electronics' => 0.05,
            'clothing' => 0.12,
            'threshold' => 150,
        ),
        'JP' => array(
            'default' => 0.10,
            'electronics' => 0.05,
            'clothing' => 0.10,
            'threshold' => 100,
        ),
        'AU' => array(
            'default' => 0.10,
            'electronics' => 0.05,
            'clothing' => 0.10,
            'threshold' => 1000,
        ),
    );
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function calculate($country, $value, $category = 'default') {
        $country_rates = $this->get_country_rates($country);
        
        // 检查是否超过免税额度
        if ($value <= $country_rates['threshold']) {
            return array(
                'duty' => 0,
                'tax_free' => true,
                'threshold' => $country_rates['threshold'],
                'currency' => 'USD',
            );
        }
        
        // 获取税率
        $rate = $country_rates[$category] ?? $country_rates['default'];
        
        // 计算关税（超过免税额度的部分）
        $taxable_amount = $value - $country_rates['threshold'];
        $duty = $taxable_amount * $rate;
        
        return array(
            'duty' => round($duty, 2),
            'tax_free' => false,
            'rate' => $rate * 100 . '%',
            'taxable_amount' => $taxable_amount,
            'threshold' => $country_rates['threshold'],
            'currency' => 'USD',
        );
    }
    
    private function get_country_rates($country) {
        // EU 国家使用 EU 税率
        $eu_countries = array('DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'AT', 'PT', 'IE', 'GR');
        
        if (in_array($country, $eu_countries)) {
            return $this->duty_rates['EU'];
        }
        
        return $this->duty_rates[$country] ?? array(
            'default' => 0.15,
            'threshold' => 0,
        );
    }
    
    public function get_categories() {
        return array(
            'default' => __('一般商品', 'cross-border-commerce'),
            'electronics' => __('电子产品', 'cross-border-commerce'),
            'clothing' => __('服装', 'cross-border-commerce'),
            'food' => __('食品', 'cross-border-commerce'),
            'cosmetics' => __('化妆品', 'cross-border-commerce'),
        );
    }
}

// 初始化
CBC_Duty_Calculator::get_instance();
