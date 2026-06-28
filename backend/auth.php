<?php
/**
 * TempWeb v2 - JWT认证 + 用户管理
 */
require_once __DIR__ . '/database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * 生成JWT Token（简易实现，无需firebase库）
     */
    public function generateToken($userId, $username, $role) {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode([
            'uid' => $userId,
            'username' => $username,
            'role' => $role,
            'exp' => time() + JWT_EXPIRE * 3600,
            'iat' => time(),
        ]));
        $signature = base64_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
        return "$header.$payload.$signature";
    }

    /**
     * 验证JWT Token
     */
    public function verifyToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;
        list($header, $payload, $signature) = $parts;
        $expectedSig = base64_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
        if ($signature !== $expectedSig) return false;
        $data = json_decode(base64_decode($payload), true);
        if (!$data || !isset($data['exp']) || $data['exp'] < time()) return false;
        return $data;
    }

    /**
     * 从请求头获取当前用户
     */
    public function getCurrentUser() {
        $token = null;
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
        }
        // 也支持X-API-Key
        if (isset($_SERVER['HTTP_X_API_KEY'])) {
            return $this->getUserByApiKey($_SERVER['HTTP_X_API_KEY']);
        }
        if (!$token) return null;
        $data = $this->verifyToken($token);
        if (!$data) return null;
        return $this->db->queryOne("SELECT id, username, email, role, status, storage_used, storage_quota, member_expires_at FROM users WHERE id = ? AND status = 1", [$data['uid']]);
    }

    /**
     * 要求登录
     */
    public function requireLogin() {
        $user = $this->getCurrentUser();
        if (!$user) {
            require_once __DIR__ . '/response.php';
            Response::unauthorized();
        }
        return $user;
    }

    /**
     * 要求管理员
     */
    public function requireAdmin() {
        $user = $this->requireLogin();
        if ($user['role'] !== 'admin') {
            require_once __DIR__ . '/response.php';
            Response::forbidden();
        }
        return $user;
    }

    /**
     * 通过API Key获取用户
     */
    private function getUserByApiKey($key) {
        $row = $this->db->queryOne("SELECT user_id FROM api_keys WHERE api_key = ? AND status = 1", [$key]);
        if (!$row) return null;
        $this->db->execute("UPDATE api_keys SET request_count = request_count + 1, last_used_at = NOW() WHERE api_key = ?", [$key]);
        return $this->db->queryOne("SELECT id, username, email, role, status, storage_used, storage_quota, member_expires_at FROM users WHERE id = ? AND status = 1", [$row['user_id']]);
    }

    /**
     * 密码哈希
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * 验证密码
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * 检查是否为会员
     */
    public function isMember($user) {
        if ($user['role'] === 'admin') return true;
        if ($user['role'] !== 'member') return false;
        if (empty($user['member_expires_at'])) return false;
        return strtotime($user['member_expires_at']) > time();
    }

    /**
     * 生成图形验证码
     */
    public function generateCaptcha() {
        $code = '';
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        for ($i = 0; $i < 4; $i++) $code .= $chars[rand(0, strlen($chars) - 1)];
        $token = md5(uniqid(mt_rand(), true));
        $this->db->execute("INSERT INTO captcha_tokens (token, code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))", [$token, $code]);

        // 生成图片
        $width = 120;
        $height = 40;
        $img = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($img, 240, 240, 240);
        imagefill($img, 0, 0, $bg);
        // 干扰线
        for ($i = 0; $i < 4; $i++) {
            $lineColor = imagecolorallocate($img, rand(100, 200), rand(100, 200), rand(100, 200));
            imageline($img, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
        }
        // 干扰点
        for ($i = 0; $i < 50; $i++) {
            $dotColor = imagecolorallocate($img, rand(100, 200), rand(100, 200), rand(100, 200));
            imagesetpixel($img, rand(0, $width), rand(0, $height), $dotColor);
        }
        // 文字
        $textColor = imagecolorallocate($img, rand(30, 80), rand(30, 80), rand(30, 80));
        $fontFile = __DIR__ . '/font.ttf';
        if (file_exists($fontFile)) {
            imagettftext($img, 20, rand(-10, 10), 15, 28, $textColor, $fontFile, $code);
        } else {
            imagestring($img, 5, 30, 10, $code, $textColor);
        }

        ob_start();
        imagepng($img);
        $imageData = base64_encode(ob_get_clean());
        imagedestroy($img);

        return ['token' => $token, 'image' => 'data:image/png;base64,' . $imageData];
    }

    /**
     * 验证验证码
     */
    public function verifyCaptcha($token, $code) {
        $row = $this->db->queryOne("SELECT code FROM captcha_tokens WHERE token = ? AND expires_at > NOW()", [$token]);
        if (!$row) return false;
        $this->db->execute("DELETE FROM captcha_tokens WHERE token = ?", [$token]);
        return strtoupper($code) === strtoupper($row['code']);
    }

    /**
     * 频率限制检查
     */
    public function checkRateLimit($action, $ip, $maxTimes = 10, $perMinutes = 1) {
        $this->db->execute("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)", [$perMinutes]);
        $row = $this->db->queryOne("SELECT COUNT(*) as cnt FROM rate_limits WHERE ip = ? AND action = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)", [$ip, $action, $perMinutes]);
        if ($row['cnt'] >= $maxTimes) {
            return false;
        }
        $this->db->insert("INSERT INTO rate_limits (ip, action, created_at) VALUES (?, ?, NOW())", [$ip, $action]);
        return true;
    }

    /**
     * 记录操作日志
     */
    public function log($userId, $action, $detail = '', $ip = '') {
        $ip = $ip ?: ($_SERVER['REMOTE_ADDR'] ?? '');
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $this->db->insert("INSERT INTO logs (user_id, action, detail, ip, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())", [$userId, $action, $detail, $ip, $ua]);
    }
}
