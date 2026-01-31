<?php
/**
 * 商品管理 API - 简单的文件存储
 * 支持跨域访问，供前台和后台共同使用
 */

// 允许跨域
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// 处理 OPTIONS 预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 数据文件路径
$dataFile = __DIR__ . '/products_data.json';

// 默认商品数据
$defaultProducts = [
    ['id' => 1, 'name' => 'iPhone 15 Pro Max 256GB 深空黑', 'sku' => 'IP15PM-256-BK', 'price' => 1199, 'salePrice' => 1099, 'stock' => 50, 'category' => '数码电子', 'image' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=400&h=400&fit=crop', 'images' => ['https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=800&h=800&fit=crop', 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=800&h=800&fit=crop'], 'description' => '全新 iPhone 15 Pro Max，搭载 A17 Pro 芯片'],
    ['id' => 2, 'name' => 'MacBook Pro 14" M3 Pro 芯片', 'sku' => 'MBP14-M3PRO', 'price' => 1999, 'salePrice' => null, 'stock' => 30, 'category' => '数码电子', 'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400&h=400&fit=crop', 'images' => ['https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&h=800&fit=crop'], 'description' => 'MacBook Pro 14 英寸，M3 Pro 芯片'],
    ['id' => 3, 'name' => 'Sony WH-1000XM5 无线降噪耳机', 'sku' => 'SONY-WH1000XM5', 'price' => 399, 'salePrice' => 349, 'stock' => 80, 'category' => '数码电子', 'image' => 'https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?w=400&h=400&fit=crop', 'images' => ['https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?w=800&h=800&fit=crop'], 'description' => '索尼旗舰降噪耳机'],
    ['id' => 4, 'name' => 'Apple Watch Ultra 2 钛金属', 'sku' => 'AW-ULTRA2-TI', 'price' => 799, 'salePrice' => null, 'stock' => 25, 'category' => '数码电子', 'image' => 'https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?w=400&h=400&fit=crop', 'images' => ['https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?w=800&h=800&fit=crop'], 'description' => 'Apple Watch Ultra 2'],
    ['id' => 5, 'name' => 'Dyson V15 Detect 智能吸尘器', 'sku' => 'DYSON-V15', 'price' => 749, 'salePrice' => 699, 'stock' => 15, 'category' => '家居生活', 'image' => 'https://images.unsplash.com/photo-1558317374-067fb5f30001?w=400&h=400&fit=crop', 'images' => ['https://images.unsplash.com/photo-1558317374-067fb5f30001?w=800&h=800&fit=crop'], 'description' => '戴森智能吸尘器'],
    ['id' => 6, 'name' => 'La Mer 海蓝之谜修护精华面霜', 'sku' => 'LAMER-CREAM-60', 'price' => 350, 'salePrice' => 320, 'stock' => 40, 'category' => '美妆护肤', 'image' => 'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=400&h=400&fit=crop', 'images' => ['https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=800&h=800&fit=crop'], 'description' => '海蓝之谜修护面霜'],
    ['id' => 7, 'name' => 'Gucci GG Marmont 链条包', 'sku' => 'GUCCI-MARMONT', 'price' => 2300, 'salePrice' => null, 'stock' => 10, 'category' => '时尚服饰', 'image' => 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=400&h=400&fit=crop', 'images' => ['https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=800&h=800&fit=crop'], 'description' => 'Gucci 经典链条包'],
    ['id' => 8, 'name' => 'Nintendo Switch OLED 白色款', 'sku' => 'NSW-OLED-WH', 'price' => 349, 'salePrice' => 299, 'stock' => 60, 'category' => '数码电子', 'image' => 'https://images.unsplash.com/photo-1578303512597-81e6cc155b3e?w=400&h=400&fit=crop', 'images' => ['https://images.unsplash.com/photo-1578303512597-81e6cc155b3e?w=800&h=800&fit=crop'], 'description' => 'Nintendo Switch OLED'],
    ['id' => 9, 'name' => 'SK-II 神仙水护肤精华露 230ml', 'sku' => 'SKII-FTE-230', 'price' => 185, 'salePrice' => 165, 'stock' => 100, 'category' => '美妆护肤', 'image' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=400&h=400&fit=crop', 'images' => ['https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=800&h=800&fit=crop'], 'description' => 'SK-II 神仙水'],
    ['id' => 10, 'name' => 'Nike Air Jordan 1 Retro High OG', 'sku' => 'NIKE-AJ1-OG', 'price' => 180, 'salePrice' => null, 'stock' => 45, 'category' => '时尚服饰', 'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop', 'images' => ['https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&h=800&fit=crop'], 'description' => 'Nike Air Jordan 1'],
    ['id' => 11, 'name' => 'Philips Sonicare 钻石智能电动牙刷', 'sku' => 'PHILIPS-SONIC', 'price' => 199, 'salePrice' => 149, 'stock' => 70, 'category' => '家居生活', 'image' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=400&h=400&fit=crop', 'images' => ['https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=800&h=800&fit=crop'], 'description' => '飞利浦电动牙刷'],
    ['id' => 12, 'name' => 'Coach Tabby 手提单肩包', 'sku' => 'COACH-TABBY', 'price' => 395, 'salePrice' => null, 'stock' => 20, 'category' => '时尚服饰', 'image' => 'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?w=400&h=400&fit=crop', 'images' => ['https://images.unsplash.com/photo-1590874103328-eac38a683ce7?w=800&h=800&fit=crop'], 'description' => 'Coach Tabby 手提包'],
];

// 读取商品数据
function loadProducts() {
    global $dataFile, $defaultProducts;
    
    if (file_exists($dataFile)) {
        $content = file_get_contents($dataFile);
        $products = json_decode($content, true);
        if ($products !== null) {
            return $products;
        }
    }
    
    // 初始化默认数据
    saveProducts($defaultProducts);
    return $defaultProducts;
}

// 保存商品数据
function saveProducts($products) {
    global $dataFile;
    file_put_contents($dataFile, json_encode($products, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// 获取请求方法和路径
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$pathInfo = $_SERVER['PATH_INFO'] ?? '';

// 解析路径参数 - 从 PATH_INFO 或 URI 中提取 ID
$productId = null;

// 尝试从 PATH_INFO 获取 ID (如 /1)
if (!empty($pathInfo) && preg_match('/^\/(\d+)/', $pathInfo, $matches)) {
    $productId = (int)$matches[1];
}

// 如果 PATH_INFO 为空，尝试从 URI 中解析
if ($productId === null) {
    $path = parse_url($uri, PHP_URL_PATH);
    if (preg_match('/\/(\d+)$/', $path, $matches)) {
        $productId = (int)$matches[1];
    }
}

// 处理请求
switch ($method) {
    case 'GET':
        $products = loadProducts();
        
        if ($productId !== null) {
            // 获取单个商品
            $found = null;
            foreach ($products as $product) {
                if ($product['id'] === $productId) {
                    $found = $product;
                    break;
                }
            }
            
            if ($found) {
                echo json_encode($found, JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode(['error' => '商品未找到'], JSON_UNESCAPED_UNICODE);
            }
        } else {
            // 获取所有商品
            echo json_encode($products, JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'POST':
        // 添加商品
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['name'])) {
            http_response_code(400);
            echo json_encode(['error' => '无效的商品数据'], JSON_UNESCAPED_UNICODE);
            break;
        }
        
        $products = loadProducts();
        
        // 生成新 ID
        $maxId = 0;
        foreach ($products as $p) {
            if ($p['id'] > $maxId) {
                $maxId = $p['id'];
            }
        }
        
        $newProduct = array_merge([
            'id' => $maxId + 1,
            'sku' => '',
            'price' => 0,
            'salePrice' => null,
            'stock' => 0,
            'category' => '',
            'image' => '',
            'images' => [],
            'description' => ''
        ], $input);
        $newProduct['id'] = $maxId + 1;
        
        $products[] = $newProduct;
        saveProducts($products);
        
        http_response_code(201);
        echo json_encode($newProduct, JSON_UNESCAPED_UNICODE);
        break;
        
    case 'PUT':
        // 更新商品
        if ($productId === null) {
            http_response_code(400);
            echo json_encode(['error' => '需要商品 ID'], JSON_UNESCAPED_UNICODE);
            break;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => '无效的商品数据'], JSON_UNESCAPED_UNICODE);
            break;
        }
        
        $products = loadProducts();
        $found = false;
        
        foreach ($products as &$product) {
            if ($product['id'] === $productId) {
                $product = array_merge($product, $input);
                $product['id'] = $productId; // 确保 ID 不变
                $found = true;
                saveProducts($products);
                echo json_encode($product, JSON_UNESCAPED_UNICODE);
                break;
            }
        }
        
        if (!$found) {
            http_response_code(404);
            echo json_encode(['error' => '商品未找到'], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    case 'DELETE':
        // 删除商品
        if ($productId === null) {
            http_response_code(400);
            echo json_encode(['error' => '需要商品 ID'], JSON_UNESCAPED_UNICODE);
            break;
        }
        
        $products = loadProducts();
        $newProducts = [];
        $found = false;
        
        foreach ($products as $product) {
            if ($product['id'] === $productId) {
                $found = true;
            } else {
                $newProducts[] = $product;
            }
        }
        
        if ($found) {
            saveProducts($newProducts);
            echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['error' => '商品未找到'], JSON_UNESCAPED_UNICODE);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => '不支持的请求方法'], JSON_UNESCAPED_UNICODE);
}
