<?php
/**
 * TempWeb v2 - 站点访问API（短链接访问）
 */
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../response.php';
require_once __DIR__ . '/../auth.php';

$db = Database::getInstance();
$auth = new Auth();
$code = $_GET['code'] ?? '';

// 输出HTML页面的辅助函数
function outputHtmlPage($content) {
    header('Content-Type: text/html; charset=utf-8');
    echo $content;
    exit;
}

if (empty($code)) {
    http_response_code(404);
    outputHtmlPage(getDefaultPage('404', '页面不存在', '您访问的页面不存在或已被删除'));
}

$project = $db->queryOne("SELECT * FROM projects WHERE short_code = ?", [$code]);

if (!$project) {
    http_response_code(404);
    outputHtmlPage(getDefaultPage('404', '作品不存在', '您访问的作品不存在或已被删除', '/index.html'));
}

// 维护模式
if ($db->getSetting('maintenance_mode', '0') === '1') {
    $msg = $db->getSetting('maintenance_msg', '系统维护中，请稍后访问');
    outputHtmlPage(getDefaultPage('maintenance', '系统维护中', htmlspecialchars($msg)));
}

// 过期检查
if ($project['status'] == 2 || ($project['expires_at'] && strtotime($project['expires_at']) < time())) {
    if ($project['status'] != 2) {
        $db->execute("UPDATE projects SET status = 2 WHERE id = ?", [$project['id']]);
    }
    outputHtmlPage(getDefaultPage('expired', '作品已过期', '该作品已过期'));
}

// 违规
if ($project['status'] == 3) {
    outputHtmlPage(getDefaultPage('banned', '作品已下架', '该作品因违规已被下架'));
}

// 防盗链
if ($db->getSetting('anti_hotlink', '0') === '1') {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $whitelist = $db->getSetting('hotlink_whitelist', '');
    $siteUrl = $db->getSetting('site_url', '');
    $allowed = false;
    if (empty($referer)) $allowed = true; // 直接访问
    if (strpos($referer, $siteUrl) === 0) $allowed = true;
    if ($whitelist) {
        foreach (explode(',', $whitelist) as $domain) {
            if (strpos($referer, trim($domain)) !== false) $allowed = true;
        }
    }
    if (!$allowed) {
        http_response_code(403);
        echo 'Access Denied';
        exit;
    }
}

// 密码保护
if (!empty($project['password'])) {
    session_start();
    $pwdKey = 'pwd_' . $project['id'];
    if (!isset($_SESSION[$pwdKey]) || $_SESSION[$pwdKey] !== $project['password']) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
            if ($_POST['password'] === $project['password']) {
                $_SESSION[$pwdKey] = $project['password'];
            } else {
                showPasswordPage($project['name'], true);
                exit;
            }
        } else {
            showPasswordPage($project['name'], false);
            exit;
        }
    }
}

// 访问统计（每次打开都算一次访问）
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$today = date('Y-m-d');

// 非会员作品每日访问次数限制
$dailyVisitLimit = intval($db->getSetting('non_member_daily_limit', '0')); // 0=不限制
$isMemberProject = false;
if (!empty($project['user_id'])) {
    $owner = $db->queryOne("SELECT role, member_expires_at FROM users WHERE id = ?", [$project['user_id']]);
    if ($owner) {
        $isMember = ($owner['role'] === 'admin' || $owner['role'] === 'member');
        if ($isMember && $owner['role'] === 'member' && !empty($owner['member_expires_at'])) {
            if (strtotime($owner['member_expires_at']) < time()) $isMember = false;
        }
        $isMemberProject = $isMember;
    }
}
if ($dailyVisitLimit > 0 && !$isMemberProject) {
    $todayViews = intval($db->queryOne("SELECT COUNT(*) as cnt FROM visit_logs WHERE project_id = ? AND DATE(created_at) = ?", [$project['id'], $today])['cnt']);
    if ($todayViews >= $dailyVisitLimit) {
        http_response_code(429);
        outputHtmlPage(getDefaultPage('429', '访问受限', '该作品今日访问量已达限制，请明天再试'));
    }
}

// 每次访问都计数
$db->execute("UPDATE projects SET view_count = view_count + 1 WHERE id = ?", [$project['id']]);
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$db->insert("INSERT INTO visit_logs (project_id, ip, user_agent, created_at) VALUES (?, ?, ?, NOW())", [$project['id'], $ip, $ua]);

// 输出文件
$projectDir = UPLOAD_DIR . '/' . $project['short_code'];
$requestUri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
$pathPart = '';

// 支持 /s/code/xxx 格式访问子文件
if (isset($_GET['path'])) {
    $pathPart = $_GET['path'];
} else {
    // 从URI中提取路径
    $prefix = '/s/' . $code;
    $afterCode = substr($requestUri, strlen($prefix));
    if ($afterCode && $afterCode !== '/') {
        $pathPart = ltrim($afterCode, '/');
    }
}

if ($pathPart) {
    // 访问子文件 - 防止目录遍历
    $pathPart = str_replace('\\', '/', $pathPart);
    $pathPart = preg_replace('#\.+/#', '', $pathPart);
    $filePath = $projectDir . '/' . $pathPart;
    $realPath = realpath($filePath);
    $realDir = realpath($projectDir);
    if (!$realPath || !$realDir || strpos($realPath, $realDir . DIRECTORY_SEPARATOR) !== 0 || !is_file($realPath)) {
        http_response_code(404);
        echo 'File not found';
        exit;
    }
    $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'html' => 'text/html', 'htm' => 'text/html', 'css' => 'text/css', 'js' => 'application/javascript',
        'json' => 'application/json', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon', 'pdf' => 'application/pdf',
        'txt' => 'text/plain', 'xml' => 'application/xml', 'woff' => 'font/woff', 'woff2' => 'font/woff2',
        'ttf' => 'font/ttf', 'eot' => 'application/vnd.ms-fontobject', 'mp3' => 'audio/mpeg', 'mp4' => 'video/mp4',
    ];
    $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
    header('Content-Type: ' . $mime);
    readfile($realPath);
    exit;
}

// 默认输出index.html
$indexPath = $projectDir . '/index.html';
if (!file_exists($indexPath)) {
    $indexPath = $projectDir . '/index.htm';
}
if (!file_exists($indexPath)) {
    http_response_code(404);
    echo 'Index file not found';
    exit;
}

// 读取HTML内容
$htmlContent = file_get_contents($indexPath);

// 水印注入（基于作品所有者是否为会员决定）
$watermarkEnabled = $db->getSetting('watermark', '1') === '1';
// 判断作品所有者是否为会员（会员作品不显示水印）
$isMemberProject = false;
if (!empty($project['user_id'])) {
    $owner = $db->queryOne("SELECT role, member_expires_at FROM users WHERE id = ?", [$project['user_id']]);
    if ($owner) {
        $isMember = ($owner['role'] === 'admin' || $owner['role'] === 'member');
        // 检查会员是否在有效期内
        if ($isMember && $owner['role'] === 'member' && !empty($owner['member_expires_at'])) {
            if (strtotime($owner['member_expires_at']) < time()) {
                $isMember = false;
            }
        }
        $isMemberProject = $isMember;
    }
}
if ($watermarkEnabled && !$isMemberProject) {
    $watermarkText = $db->getSetting('watermark_text', 'Hosted by TempWeb');
    $watermarkLink = $db->getSetting('watermark_link', '');
    $watermarkStyle = $db->getSetting('watermark_style', 'single'); // single | tiled
    $escText = htmlspecialchars($watermarkText);
    $linkOpen = $watermarkLink ? '<a href="' . htmlspecialchars($watermarkLink) . '" target="_blank" style="color:inherit;text-decoration:none;">' : '';
    $linkClose = $watermarkLink ? '</a>' : '';
    $innerContent = $linkOpen . $escText . $linkClose;

    if ($watermarkStyle === 'tiled') {
        // 平铺水印
        $watermark = '<div style="position:fixed;top:0;left:0;width:100%;height:100%;z-index:999999;pointer-events:none;user-select:none;display:flex;flex-wrap:wrap;align-content:space-around;justify-content:space-around;font-family:sans-serif;font-size:14px;color:rgba(0,0,0,0.08);overflow:hidden;">';
        $tileCount = 30;
        for ($i = 0; $i < $tileCount; $i++) {
            $watermark .= '<span style="transform:rotate(-30deg);white-space:nowrap;padding:20px;">' . $innerContent . '</span>';
        }
        $watermark .= '</div>';
    } else {
        // 单条水印（右下角）
        $watermark = '<div style="position:fixed;bottom:10px;right:10px;font-size:13px;color:rgba(255,255,255,0.7);background:rgba(0,0,0,0.4);padding:4px 10px;border-radius:4px;z-index:999999;user-select:none;font-family:sans-serif;">' . $innerContent . '</div>';
    }

    if (stripos($htmlContent, '</body>') !== false) {
        $htmlContent = str_ireplace('</body>', $watermark . '</body>', $htmlContent);
    } else {
        $htmlContent .= $watermark;
    }
}

header('Content-Type: text/html; charset=utf-8');
echo $htmlContent;

// 默认提示页面（404/维护/过期/违规/429）
function getDefaultPage($type, $title, $desc, $homeUrl = '/index.html') {
    global $db;
    // 优先使用后台设置的自定义页面
    $customPage = $db->getSetting('page_' . $type, '');
    if ($customPage) {
        return $customPage;
    }
    $icons = [
        '404' => '🔍',
        'maintenance' => '🔧',
        'expired' => '⏰',
        'banned' => '🚫',
        '429' => '🚧',
    ];
    $icon = $icons[$type] ?? '⚠️';
    return '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . $title . '</title><style>*{margin:0;padding:0;box-sizing:border-box;}body{font-family:system-ui,-apple-system,sans-serif;background:linear-gradient(135deg,#f0f9ff,#e0f2fe);min-height:100vh;display:flex;align-items:center;justify-content:center;color:#1e293b;}.card{background:#fff;padding:48px 40px;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,0.08);text-align:center;max-width:420px;width:90%;}.icon{font-size:72px;margin-bottom:16px;}h1{font-size:24px;margin-bottom:12px;color:#0f172a;}p{color:#64748b;margin-bottom:24px;line-height:1.6;}.btn{display:inline-block;padding:10px 28px;background:#18a058;color:#fff;text-decoration:none;border-radius:8px;font-size:15px;transition:background 0.2s;}.btn:hover{background:#0e7a43;}</style></head><body><div class="card"><div class="icon">' . $icon . '</div><h1>' . $title . '</h1><p>' . $desc . '</p><a href="' . $homeUrl . '" class="btn">返回首页</a></div></body></html>';
}

function showPasswordPage($name, $wrong) {
    global $db;
    header('Content-Type: text/html; charset=utf-8');
    // 优先使用后台设置的自定义密码页面
    $customPage = $db->getSetting('page_password', '');
    if ($customPage) {
        echo $customPage;
        exit;
    }
    $error = $wrong ? '<div style="color:#d03050;margin-bottom:12px;font-size:14px;">密码错误，请重试</div>' : '';
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>访问验证</title><style>*{margin:0;padding:0;box-sizing:border-box;}body{font-family:system-ui,-apple-system,sans-serif;background:linear-gradient(135deg,#f0f9ff,#e0f2fe);min-height:100vh;display:flex;align-items:center;justify-content:center;color:#1e293b;}form{background:#fff;padding:40px 32px;border-radius:16px;box-shadow:0 10px 40px rgba(0,0,0,0.08);width:380px;max-width:90%;text-align:center;}h3{margin:0 0 8px;font-size:20px;color:#0f172a;}.sub{color:#64748b;font-size:14px;margin-bottom:20px;}input{width:100%;padding:12px;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:12px;font-size:15px;box-sizing:border-box;}input:focus{outline:none;border-color:#18a058;}button{width:100%;padding:12px;background:#18a058;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:15px;transition:background 0.2s;}button:hover{background:#0e7a43;}</style></head><body><form method="POST"><h3>🔒 访问验证</h3><div class="sub">' . htmlspecialchars($name) . '</div>' . $error . '<input type="password" name="password" placeholder="请输入访问密码" autofocus><button type="submit">访问作品</button></form></body></html>';
    exit;
}
