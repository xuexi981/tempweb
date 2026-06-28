<?php
/**
 * TempWeb v2 - 数据库连接
 */
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['code' => 500, 'message' => '数据库连接失败: ' . $e->getMessage()]);
            exit;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo() {
        return $this->pdo;
    }

    /**
     * 查询多条记录
     */
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * 查询单条记录
     */
    public function queryOne($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * 执行写操作，返回影响行数
     */
    public function execute($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * 执行写操作，返回lastInsertId
     */
    public function insert($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    /**
     * 获取设置项
     */
    public function getSetting($key, $default = '') {
        $row = $this->queryOne("SELECT value FROM settings WHERE `key` = ?", [$key]);
        return $row ? $row['value'] : $default;
    }

    /**
     * 设置项
     */
    public function setSetting($key, $value) {
        return $this->execute(
            "INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?",
            [$key, $value, $value]
        );
    }

    /**
     * 获取所有设置（按分组）
     */
    public function getAllSettings() {
        $rows = $this->query("SELECT `key`, `value`, `group` FROM settings ORDER BY `group`, `key`");
        $result = [];
        foreach ($rows as $row) {
            $group = $row['group'] ?: 'basic';
            $result[$group][$row['key']] = $row['value'];
        }
        return $result;
    }
}
