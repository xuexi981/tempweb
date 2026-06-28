<?php
/**
 * TempWeb v2 - 统一响应
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// 全局异常捕获，确保API始终返回JSON
set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    echo json_encode(['code' => 500, 'message' => '服务器内部错误: ' . $e->getMessage(), 'data' => null], JSON_UNESCAPED_UNICODE);
    exit;
});

class Response {
    public static function success($data = null, $message = 'success') {
        echo json_encode(['code' => 0, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error($message = 'error', $code = 1, $httpCode = 400) {
        http_response_code($httpCode);
        echo json_encode(['code' => $code, 'message' => $message, 'data' => null], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function unauthorized($message = '未登录或登录已过期') {
        self::error($message, 401, 401);
    }

    public static function forbidden($message = '权限不足') {
        self::error($message, 403, 403);
    }

    public static function notFound($message = '资源不存在') {
        self::error($message, 404, 404);
    }

    public static function paginate($list, $total, $page, $pageSize) {
        self::success([
            'list' => $list,
            'total' => (int)$total,
            'page' => (int)$page,
            'pageSize' => (int)$pageSize,
        ]);
    }
}
