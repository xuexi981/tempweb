<?php
/**
 * TempWeb v2 - 统一入口
 * 所有请求通过此文件路由
 */

$reqUri = strtok($_SERVER['REQUEST_URI'], '?');

// 短链接访问 /s/xxxx 或 /s/xxxx/subfile
if (preg_match('#^/s/([a-zA-Z0-9]+)(/.*)?$#', $reqUri, $m)) {
    $_GET['action'] = 'visit';
    $_GET['code'] = $m[1];
    if (isset($m[2]) && $m[2]) $_GET['path'] = ltrim($m[2], '/');
    require __DIR__ . '/backend/api/site.php';
    exit;
}

// API路由 /api/xxx.php
if (preg_match('#^/api/([a-z_]+)\.php$#', $reqUri, $m)) {
    $file = __DIR__ . '/backend/api/' . $m[1] . '.php';
    if (file_exists($file)) { require $file; exit; }
}

// 安装页面
if ($_SERVER['REQUEST_URI'] === '/install') {
    require __DIR__ . '/backend/install.php';
    exit;
}

// 静态HTML页面
$staticPages = [
    '/' => '/index.html',
    '/index.html' => '/index.html',
    '/login.html' => '/login.html',
    '/register.html' => '/register.html',
    '/forgot-password.html' => '/forgot-password.html',
    '/success.html' => '/success.html',
    '/projects.html' => '/projects.html',
    '/project-detail.html' => '/project-detail.html',
    '/square.html' => '/square.html',
    '/admin.html' => '/admin.html',
];

$uri = strtok($_SERVER['REQUEST_URI'], '?');
if (isset($staticPages[$uri])) {
    $file = __DIR__ . $staticPages[$uri];
    if (file_exists($file)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($file);
        exit;
    }
}

// 静态资源
if (preg_match('#^/assets/(.+)$#', $uri, $m)) {
    $file = __DIR__ . '/assets/' . $m[1];
    if (file_exists($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $types = ['css' => 'text/css', 'js' => 'application/javascript', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'gif' => 'image/gif', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon', 'woff2' => 'font/woff2', 'woff' => 'font/woff'];
        header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
        readfile($file);
        exit;
    }
}

// 404
http_response_code(404);
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>404 - 页面未找到</title></head><body style="display:flex;align-items:center;justify-content:center;height:100vh;margin:0;font-family:sans-serif;background:#f7f8fa"><div style="text-align:center"><h1 style="font-size:72px;color:#d03050;margin:0">404</h1><p style="color:#666;font-size:18px">页面未找到</p><a href="/" style="color:#18a058;text-decoration:none">返回首页</a></div></body></html>';
