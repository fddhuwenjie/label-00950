<?php
/**
 * 国际物流运费计算类
 * 
 * 支持：
 * - 多物流商费率配置（标准物流 / DHL / FedEx / EMS）
 * - 实重与体积重取大计费
 * - 基于 WordPress option 的动态费率配置（管理员可在后台调整）
 * - 免运费门槛
 * - 偏远地区附加费
 * 
 * 生产环境可进一步对接物流商 API 获取实时报价。
 */

if (!defined('ABSPATH')) {
    exit;
}

class CBC_Shipping_Calculator {
    
    private static $instance = null;
    private $config = array();
    
    // 物流区域划分
    private $zones = array(
        'zone_1' => array('CN', 'HK', 'TW', 'MO'),           // 大中华区
        'zone_2' => array('JP', 'KR', 'SG', 'MY', 'TH', 'VN', 'PH', 'ID'), // 亚太
        'zone_3' => array('US', 'CA', 'MX'),                  // 北美
        'zone_4' => array('GB', 'DE', 'FR', 'IT', 'ES', 'NL', 'BE', 'AT', 'CH', 'SE', 'DK', 'NO', 'FI', 'PL', 'CZ', 'PT', 'IE'), // 欧洲
        'zone_5' => array('AU', 'NZ'),                        // 大洋洲
        'zone_6' => array('BR', 'AR', 'CL', 'CO'),            // 南美
        'zone_7' => array('AE', 'SA', 'IL', 'TR'),            // 中东
    );

    // 偏远地区国家（附加费）
    private $remote_countries = array('BR', 'AR', 'CL', 'CO', 'RU', 'ZA', 'NG', 'KE');

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_config();
    }

    /**
     * 从 WordPress option 加载费率配置，支持管理员动态调整
     */
    private function load_config() {
        $saved = get_option('cbc_shipping_config', array());
        
        $defaults = array(
            'free_shipping_threshold' => 200, // 满 $200 免标准运费
            'remote_surcharge'        => 10,  // 偏远地区附加费
            'carriers' => array(
                'standard' => array(
                    'name'  => '标准国际物流',
                    'rates' => array(
                        'zone_1' => array('base' => 5,  'per_kg' => 1.0),
                        'zone_2' => array('base' => 8,  'per_kg' => 2.0),
                        'zone_3' => array('base' => 12, 'per_kg' => 3.0),
                        'zone_4' => array('base' => 15, 'per_kg' => 3.5),
                        'zone_5' => array('base' => 18, 'per_kg' => 4.0),
                        'zone_6' => array('base' => 22, 'per_kg' => 5.0),
                        'zone_7' => array('base' => 20, 'per_kg' => 4.5),
                    ),
                    'estimated_days' => array(
                        'zone_1' => '3-5',  'zone_2' => '5-7',
                        'zone_3' => '7-14', 'zone_4' => '10-15',
                        'zone_5' => '10-18','zone_6' => '15-25',
                        'zone_7' => '10-20',
                    ),
                ),
                'express' => array(
                    'name'  => '国际快递 (DHL/FedEx)',
                    'rates' => array(
                        'zone_1' => array('base' => 12, 'per_kg' => 3.0),
                        'zone_2' => array('base' => 18, 'per_kg' => 5.0),
                        'zone_3' => array('base' => 25, 'per_kg' => 7.0),
                        'zone_4' => array('base' => 28, 'per_kg' => 8.0),
                        'zone_5' => array('base' => 32, 'per_kg' => 9.0),
                        'zone_6' => array('base' => 38, 'per_kg' => 10.0),
                        'zone_7' => array('base' => 35, 'per_kg' => 9.5),
                    ),
                    'estimated_days' => array(
                        'zone_1' => '1-2',  'zone_2' => '2-3',
                        'zone_3' => '3-5',  'zone_4' => '3-5',
                        'zone_5' => '4-6',  'zone_6' => '5-8',
                        'zone_7' => '3-5',
                    ),
                ),
                'ems' => array(
                    'name'  => 'EMS 国际特快',
                    'rates' => array(
                        'zone_1' => array('base' => 8,  'per_kg' => 2.0),
                        'zone_2' => array('base' => 12, 'per_kg' => 3.5),
                        'zone_3' => array('base' => 18, 'per_kg' => 5.0),
                        'zone_4' => array('base' => 20, 'per_kg' => 5.5),
                        'zone_5' => array('base' => 22, 'per_kg' => 6.0),
                        'zone_6' => array('base' => 28, 'per_kg' => 7.0),
                        'zone_7' => array('base' => 25, 'per_kg' => 6.5),
                    ),
                    'estimated_days' => array(
                        'zone_1' => '2-3',  'zone_2' => '3-5',
                        'zone_3' => '5-8',  'zone_4' => '5-8',
                        'zone_5' => '6-10', 'zone_6' => '8-15',
                        'zone_7' => '5-8',
                    ),
                ),
            ),
        );

        $this->config = wp_parse_args($saved, $defaults);
    }

    /**
     * 计算运费
     * 
     * @param string $country  目的国家代码
     * @param float  $weight   实际重量 (kg)
     * @param string $method   物流方式: standard / express / ems
     * @param array  $dimensions 可选，包裹尺寸 [length, width, height] (cm)
     * @param float  $order_total 可选，订单金额（用于判断免运费）
     * @return array
     */
    public function calculate($country, $weight = 1, $method = 'standard', $dimensions = array(), $order_total = 0) {
        $zone = $this->get_zone($country);
        if (!$zone) {
            $zone = 'zone_5'; // 未知区域按大洋洲计费
        }

        $carrier = $this->config['carriers'][$method] ?? $this->config['carriers']['standard'];
        $zone_rate = $carrier['rates'][$zone] ?? array('base' => 20, 'per_kg' => 5);

        // 体积重计算：长×宽×高 / 5000 (国际标准 DIM 因子)
        $billable_weight = $weight;
        if (!empty($dimensions) && count($dimensions) >= 3) {
            $vol_weight = ($dimensions[0] * $dimensions[1] * $dimensions[2]) / 5000;
            $billable_weight = max($weight, $vol_weight); // 取实重和体积重的较大值
        }

        // 基础运费 = 首重费 + 续重费
        $shipping_cost = $zone_rate['base'] + ($billable_weight * $zone_rate['per_kg']);

        // 偏远地区附加费
        $surcharge = 0;
        if (in_array($country, $this->remote_countries)) {
            $surcharge = $this->config['remote_surcharge'];
            $shipping_cost += $surcharge;
        }

        // 免运费判断（仅标准物流）
        $free_shipping = false;
        if ($method === 'standard' && $order_total >= $this->config['free_shipping_threshold']) {
            $free_shipping = true;
            $original_cost = $shipping_cost;
            $shipping_cost = 0;
        }

        $estimated_days = $carrier['estimated_days'][$zone] ?? '15-30';

        return array(
            'cost'            => round($shipping_cost, 2),
            'original_cost'   => isset($original_cost) ? round($original_cost, 2) : round($shipping_cost, 2),
            'currency'        => 'USD',
            'zone'            => $zone,
            'method'          => $method,
            'carrier_name'    => $carrier['name'],
            'estimated_days'  => $estimated_days,
            'billable_weight' => round($billable_weight, 2),
            'surcharge'       => $surcharge,
            'free_shipping'   => $free_shipping,
        );
    }

    /**
     * 获取所有可用物流方式及其对指定国家的报价
     */
    public function get_all_quotes($country, $weight = 1, $dimensions = array(), $order_total = 0) {
        $quotes = array();
        foreach ($this->config['carriers'] as $method => $carrier) {
            $quotes[] = $this->calculate($country, $weight, $method, $dimensions, $order_total);
        }
        // 按价格排序
        usort($quotes, function($a, $b) {
            return $a['cost'] <=> $b['cost'];
        });
        return $quotes;
    }
    
    /**
     * 获取国家所属物流区域
     */
    private function get_zone($country) {
        foreach ($this->zones as $zone => $countries) {
            if (in_array($country, $countries)) {
                return $zone;
            }
        }
        return null;
    }
    
    /**
     * 获取可用物流方式列表
     */
    public function get_available_methods() {
        $methods = array();
        foreach ($this->config['carriers'] as $key => $carrier) {
            $methods[$key] = $carrier['name'];
        }
        return $methods;
    }

    /**
     * 更新运费配置（管理员接口）
     */
    public function update_config($new_config) {
        $this->config = wp_parse_args($new_config, $this->config);
        update_option('cbc_shipping_config', $this->config);
        return $this->config;
    }

    /**
     * 获取当前配置
     */
    public function get_config() {
        return $this->config;
    }
}

CBC_Shipping_Calculator::get_instance();
