<?php
/**
 * 安全工具类
 * 提供 CSRF 保护、XSS 防护、输入验证等安全功能
 */

class Security {
    private static $csrfTokenName = 'X-CSRF-Token';
    private static $csrfCookieName = 'csrf_token';
    private static $tokenExpiry = 3600; // 1小时
    
    /**
     * 生成 CSRF Token
     */
    public static function generateCsrfToken(): string {
        $token = bin2hex(random_bytes(32));
        
        // 存储到 session 或 cookie
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        // 同时设置 cookie (用于 SPA)
        setcookie(
            self::$csrfCookieName,
            $token,
            [
                'expires' => time() + self::$tokenExpiry,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => false, // 允许 JS 读取
                'samesite' => 'Strict'
            ]
        );
        
        return $token;
    }
    
    /**
     * 验证 CSRF Token
     */
    public static function validateCsrfToken(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // 从 header 获取 token
        $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        // 从 session 获取存储的 token
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $tokenTime = $_SESSION['csrf_token_time'] ?? 0;
        
        // 检查 token 是否过期
        if (time() - $tokenTime > self::$tokenExpiry) {
            return false;
        }
        
        // 使用时间安全的比较
        return hash_equals($sessionToken, $headerToken);
    }
    
    /**
     * XSS 防护 - 清理输出
     */
    public static function escapeHtml($data) {
        if (is_array($data)) {
            return array_map([self::class, 'escapeHtml'], $data);
        }
        
        if (is_string($data)) {
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        return $data;
    }
    
    /**
     * 清理输入数据
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        if (is_string($data)) {
            // 移除 NULL 字节
            $data = str_replace(chr(0), '', $data);
            // 移除不可见字符 (保留换行和制表符)
            $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $data);
            // trim
            $data = trim($data);
        }
        
        return $data;
    }
    
    /**
     * 验证邮箱
     */
    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * 验证整数
     */
    public static function validateInt($value, int $min = null, int $max = null): bool {
        $options = [];
        if ($min !== null) $options['min_range'] = $min;
        if ($max !== null) $options['max_range'] = $max;
        
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => $options]) !== false;
    }
    
    /**
     * 验证价格
     */
    public static function validatePrice($value): bool {
        return is_numeric($value) && $value >= 0;
    }
    
    /**
     * 验证 URL
     */
    public static function validateUrl(string $url): bool {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * 生成安全的随机字符串
     */
    public static function generateRandomString(int $length = 32): string {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * 密码哈希
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * 验证密码
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    /**
     * 设置安全响应头
     */
    public static function setSecurityHeaders(): void {
        // 防止 MIME 类型嗅探
        header('X-Content-Type-Options: nosniff');
        
        // 防止点击劫持
        header('X-Frame-Options: DENY');
        
        // XSS 保护
        header('X-XSS-Protection: 1; mode=block');
        
        // 内容安全策略
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' https: data:;");
        
        // 引用策略
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // 权限策略
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }
    
    /**
     * 速率限制检查 (简单实现，生产环境建议使用 Redis)
     */
    public static function checkRateLimit(string $identifier, int $maxRequests = 100, int $windowSeconds = 60): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = 'rate_limit_' . md5($identifier);
        $now = time();
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 1, 'start' => $now];
            return true;
        }
        
        $data = $_SESSION[$key];
        
        // 窗口已过期，重置
        if ($now - $data['start'] > $windowSeconds) {
            $_SESSION[$key] = ['count' => 1, 'start' => $now];
            return true;
        }
        
        // 检查是否超过限制
        if ($data['count'] >= $maxRequests) {
            return false;
        }
        
        // 增加计数
        $_SESSION[$key]['count']++;
        return true;
    }
    
    /**
     * 记录安全事件
     */
    public static function logSecurityEvent(string $event, array $details = []): void {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];
        
        $logFile = __DIR__ . '/../logs/security.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents(
            $logFile,
            json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }
}

/**
 * 输入验证器类
 */
class InputValidator {
    private $errors = [];
    private $data = [];
    
    public function __construct(array $data) {
        $this->data = Security::sanitizeInput($data);
    }
    
    public function required(string $field, string $message = null): self {
        if (!isset($this->data[$field]) || $this->data[$field] === '') {
            $this->errors[$field] = $message ?? "{$field} 是必填项";
        }
        return $this;
    }
    
    public function email(string $field, string $message = null): self {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            if (!Security::validateEmail($this->data[$field])) {
                $this->errors[$field] = $message ?? "请输入有效的邮箱地址";
            }
        }
        return $this;
    }
    
    public function minLength(string $field, int $min, string $message = null): self {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = $message ?? "{$field} 至少需要 {$min} 个字符";
        }
        return $this;
    }
    
    public function maxLength(string $field, int $max, string $message = null): self {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = $message ?? "{$field} 不能超过 {$max} 个字符";
        }
        return $this;
    }
    
    public function numeric(string $field, string $message = null): self {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            if (!is_numeric($this->data[$field])) {
                $this->errors[$field] = $message ?? "{$field} 必须是数字";
            }
        }
        return $this;
    }
    
    public function min(string $field, $min, string $message = null): self {
        if (isset($this->data[$field]) && is_numeric($this->data[$field])) {
            if ($this->data[$field] < $min) {
                $this->errors[$field] = $message ?? "{$field} 不能小于 {$min}";
            }
        }
        return $this;
    }
    
    public function isValid(): bool {
        return empty($this->errors);
    }
    
    public function getErrors(): array {
        return $this->errors;
    }
    
    public function getData(): array {
        return $this->data;
    }
    
    public function get(string $field, $default = null) {
        return $this->data[$field] ?? $default;
    }
}
