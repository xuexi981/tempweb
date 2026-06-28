<?php
/**
 * TempWeb v2 - 作品API
 */
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../response.php';
require_once __DIR__ . '/../auth.php';

$db = Database::getInstance();
$auth = new Auth();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'upload':
        $user = $auth->getCurrentUser();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        // 游客上传检查
        $allowGuest = $db->getSetting('allow_guest_upload', '1') === '1';
        if (!$user && !$allowGuest) Response::error('请先登录', 401, 401);

        // 频率限制
        $maxUpload = intval($db->getSetting('rate_limit_upload', '10'));
        if (!$auth->checkRateLimit('upload', $ip, $maxUpload, 1)) {
            Response::error('上传频率过高，请稍后再试');
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        $expiresDays = intval($_POST['expires'] ?? 0);
        $password = $_POST['project_password'] ?? '';
        $customCode = trim($_POST['custom_code'] ?? '');
        $contentType = $_POST['content_type'] ?? 'zip'; // zip or html

        if (empty($name)) Response::error('请输入作品名称');

        // 角色权限
        $role = $user ? $user['role'] : 'guest';
        $isMember = $user ? $auth->isMember($user) : false;

        // 会员功能限制
        if ($password && !$isMember) Response::error('密码保护为会员功能');
        if ($customCode && !$isMember) Response::error('自定义短码为会员功能');

        // 会员自动不加水印
        $noWatermark = $isMember ? 1 : 0;

        // 文件大小限制
        $maxSize = $role === 'admin' ? PHP_INT_MAX : ($isMember ? intval($db->getSetting('max_file_size_member', '104857600')) : ($user ? intval($db->getSetting('max_file_size', '52428800')) : intval($db->getSetting('max_file_size_guest', '20971520'))));

        // 存储配额检查（完全使用后台设置）
        if ($user && $role !== 'admin') {
            $quota = $isMember ? intval($db->getSetting('storage_quota_member', '1073741824')) : intval($db->getSetting('storage_quota', '524288000'));
            $used = $user['storage_used'];
        }

        // 生成短码
        $shortCode = $customCode ?: generateShortCode(intval($db->getSetting('short_code_length', '6')));
        if ($db->queryOne("SELECT id FROM projects WHERE short_code = ?", [$shortCode])) {
            $shortCode = generateShortCode(8); // 冲突则重新生成
        }

        // 过期时间：按身份使用对应保存天数
        if ($isMember) {
            $defaultExpire = intval($db->getSetting('default_expire_member', '30'));
        } elseif ($user) {
            $defaultExpire = intval($db->getSetting('default_expire', '7'));
        } else {
            $defaultExpire = intval($db->getSetting('default_expire_guest', '7'));
        }
        if ($expiresDays <= 0) $expiresDays = $defaultExpire;
        // 非会员不能超过对应身份的默认天数
        if ($role !== 'admin' && $expiresDays > $defaultExpire) $expiresDays = $defaultExpire;
        $expiresAt = $expiresDays > 0 ? date('Y-m-d H:i:s', time() + $expiresDays * 86400) : null;

        $projectDir = UPLOAD_DIR . '/' . $shortCode;
        if (!is_dir($projectDir)) mkdir($projectDir, 0755, true);

        $fileSize = 0;
        $fileCount = 1;

        if ($contentType === 'html') {
            // HTML粘贴模式
            $htmlContent = $_POST['content'] ?? '';
            if (empty($htmlContent)) Response::error('请输入HTML代码');
            // 检查大小限制
            if (strlen($htmlContent) > $maxSize) {
                cleanDir($projectDir);
                Response::error('内容大小超出限制');
            }
            $htmlContent = cleanHtml($htmlContent);
            file_put_contents($projectDir . '/index.html', $htmlContent);
            $fileSize = strlen($htmlContent);
        } else {
            // ZIP上传模式
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                Response::error('请上传ZIP文件');
            }
            $tmpFile = $_FILES['file']['tmp_name'];
            $fileSize = $_FILES['file']['size'];
            if ($fileSize > $maxSize) {
                cleanDir($projectDir);
                Response::error('文件大小超出限制');
            }

            $zip = new ZipArchive;
            if ($zip->open($tmpFile) !== true) {
                cleanDir($projectDir);
                Response::error('ZIP文件无法打开');
            }

            $maxZipFiles = intval($db->getSetting('max_zip_files', '500'));
            $maxZipSize = intval($db->getSetting('max_zip_size', '10485760'));
            $allowedTypes = array_filter(array_map('trim', explode(',', $db->getSetting('allowed_types', 'html,htm,css,js,json,png,jpg,jpeg,gif,svg,ico,woff,woff2,ttf,eot,mp3,mp4,pdf,txt,xml'))));
            $bannedFiles = array_filter(array_map('trim', explode(',', $db->getSetting('banned_files', '.env,.git,.htaccess'))));
            // 危险后缀黑名单（无论是否在允许列表中都禁止）
            $dangerousExt = ['php','php3','php4','php5','php7','phtml','pht','phar','asp','aspx','jsp','jspx','exe','bat','cmd','sh','cgi','pl','py','rb','htaccess'];

            $fileCount = 0;
            $hasIndex = false;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                // 跳过目录
                if (substr($entry, -1) === '/') continue;
                $fileCount++;
                if ($fileCount > $maxZipFiles) {
                    $zip->close();
                    cleanDir($projectDir);
                    Response::error("ZIP内文件数超过{$maxZipFiles}个限制");
                }
                // 检查文件名
                $basename = basename($entry);
                foreach ($bannedFiles as $banned) {
                    if ($banned && stripos($basename, $banned) !== false) {
                        $zip->close();
                        cleanDir($projectDir);
                        Response::error("禁止的文件: $basename");
                    }
                }
                // 检查扩展名
                $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
                // 危险后缀直接拒绝整个上传
                if (in_array($ext, $dangerousExt)) {
                    $zip->close();
                    cleanDir($projectDir);
                    Response::error("禁止上传可执行文件: $basename");
                }
                // 不在允许列表的跳过
                if (!empty($allowedTypes) && !in_array($ext, $allowedTypes)) {
                    continue;
                }
                // 检查单文件大小
                $stat = $zip->statIndex($i);
                if ($stat['size'] > $maxZipSize) {
                    $zip->close();
                    cleanDir($projectDir);
                    Response::error("ZIP内单文件{$basename}超过大小限制");
                }
                if (strtolower($basename) === 'index.html' || strtolower($basename) === 'index.htm') {
                    $hasIndex = true;
                }
            }

            if (!$hasIndex) {
                $zip->close();
                cleanDir($projectDir);
                Response::error('ZIP包内必须包含index.html');
            }

            $zip->extractTo($projectDir);
            $zip->close();

            // 计算实际大小
            $fileSize = dirSize($projectDir);
        }

        // 存储配额检查
        if ($user && $role !== 'admin') {
            if ($used + $fileSize > $quota) {
                cleanDir($projectDir);
                Response::error('存储空间不足');
            }
        }

        // 写入数据库
        $userId = $user ? $user['id'] : null;
        // 所有用户都可以设置公开
        $isPublic = intval($_POST['is_public'] ?? 0);
        $siteUrl = rtrim($db->getSetting('site_url', ''), '/');
        $projectUrl = $siteUrl . '/s/' . $shortCode;

        $pid = $db->insert(
            "INSERT INTO projects (user_id, name, description, tags, short_code, file_size, file_count, expires_at, password, no_watermark, is_public, status, view_count, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0, NOW())",
            [$userId, $name, $description, $tags, $shortCode, $fileSize, $fileCount, $expiresAt, $password ?: null, $noWatermark, $isPublic]
        );

        // 更新用户存储
        if ($user && $role !== 'admin') {
            $db->execute("UPDATE users SET storage_used = storage_used + ? WHERE id = ?", [$fileSize, $user['id']]);
        }

        // 日志
        if ($user) $auth->log($user['id'], 'upload', "上传作品: $name ($shortCode)");

        Response::success([
            'id' => $pid,
            'name' => $name,
            'short_code' => $shortCode,
            'url' => $projectUrl,
            'expires_at' => $expiresAt,
            'file_size' => $fileSize,
            'file_size_formatted' => formatBytes($fileSize),
        ], '上传成功');
        break;

    case 'stats':
        $user = $auth->requireLogin();
        $active = $db->queryOne("SELECT COUNT(*) as cnt FROM projects WHERE user_id = ? AND status = 1", [$user['id']])['cnt'];
        $views = $db->queryOne("SELECT SUM(view_count) as total FROM projects WHERE user_id = ?", [$user['id']])['total'] ?? 0;
        Response::success(['active' => $active, 'views' => $views]);
        break;

    case 'list':
        $user = $auth->requireLogin();
        $page = max(1, intval($_GET['page'] ?? 1));
        $pageSize = min(50, max(1, intval($_GET['pageSize'] ?? 10)));
        $keyword = $_GET['keyword'] ?? '';
        $status = $_GET['status'] ?? '';
        $tag = $_GET['tag'] ?? '';
        $offset = ($page - 1) * $pageSize;

        $where = "WHERE user_id = ?";
        $params = [$user['id']];
        if ($keyword) { $where .= " AND name LIKE ?"; $params[] = "%$keyword%"; }
        if ($status !== '') { $where .= " AND status = ?"; $params[] = $status; }
        if ($tag) { $where .= " AND FIND_IN_SET(?, tags)"; $params[] = $tag; }

        $total = $db->queryOne("SELECT COUNT(*) as cnt FROM projects $where", $params)['cnt'];
        $list = $db->query("SELECT id, name, description, tags, short_code, file_size, file_count, expires_at, password IS NOT NULL as has_password, no_watermark, is_public, status, view_count, created_at FROM projects $where ORDER BY created_at DESC LIMIT $offset, $pageSize", $params);

        foreach ($list as &$item) {
            $item['file_size_formatted'] = formatBytes($item['file_size']);
            $item['url'] = rtrim($db->getSetting('site_url', ''), '/') . '/s/' . $item['short_code'];
            // 计算今日剩余访问次数（仅非会员作品）
            $dailyLimit = intval($db->getSetting('non_member_daily_limit', '0'));
            $item['daily_visit_limit'] = $dailyLimit;
            $item['daily_visit_remaining'] = -1; // -1表示不限制
            if ($dailyLimit > 0 && $user['role'] !== 'admin' && $user['role'] !== 'member') {
                $todayViews = intval($db->queryOne("SELECT COUNT(*) as cnt FROM visit_logs WHERE project_id = ? AND DATE(created_at) = CURDATE()", [$item['id']])['cnt']);
                $item['daily_visit_remaining'] = max(0, $dailyLimit - $todayViews);
            }
        }

        Response::paginate($list, $total, $page, $pageSize);
        break;

    case 'public_list':
        $page = max(1, intval($_GET['page'] ?? 1));
        $pageSize = min(50, max(1, intval($_GET['pageSize'] ?? 12)));
        $keyword = $_GET['keyword'] ?? '';
        $offset = ($page - 1) * $pageSize;

        $where = "WHERE is_public = 1 AND status = 1 AND (expires_at IS NULL OR expires_at > NOW())";
        $params = [];
        if ($keyword) { $where .= " AND (name LIKE ? OR tags LIKE ?)"; $params[] = "%$keyword%"; $params[] = "%$keyword%"; }

        $total = $db->queryOne("SELECT COUNT(*) as cnt FROM projects $where", $params)['cnt'];
        $list = $db->query("SELECT id, name, description, tags, short_code, view_count, created_at FROM projects $where ORDER BY view_count DESC, created_at DESC LIMIT $offset, $pageSize", $params);

        foreach ($list as &$item) {
            $item['url'] = rtrim($db->getSetting('site_url', ''), '/') . '/s/' . $item['short_code'];
        }

        Response::paginate($list, $total, $page, $pageSize);
        break;

    case 'detail':
        $user = $auth->requireLogin();
        $id = intval($_GET['id'] ?? 0);
        $project = $db->queryOne("SELECT * FROM projects WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$project) Response::notFound('作品不存在');
        $project['file_size_formatted'] = formatBytes($project['file_size']);
        $project['url'] = rtrim($db->getSetting('site_url', ''), '/') . '/s/' . $project['short_code'];
        $project['has_password'] = !empty($project['password']);
        unset($project['password']);
        Response::success($project);
        break;

    case 'get_html':
        $user = $auth->requireLogin();
        $id = intval($_GET['id'] ?? 0);
        $project = $db->queryOne("SELECT short_code, no_watermark FROM projects WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$project) Response::notFound('作品不存在');
        $projectDir = UPLOAD_DIR . '/' . $project['short_code'];
        $indexPath = $projectDir . '/index.html';
        if (!file_exists($indexPath)) Response::error('HTML文件不存在');
        $html = file_get_contents($indexPath);
        // 去除可能存在的水印div以便编辑原始内容（兼容旧作品）
        $watermarkText = $db->getSetting('watermark_text', 'Hosted by TempWeb');
        $watermarkDiv = '<div style="position:fixed;bottom:10px;right:10px;font-size:12px;color:rgba(0,0,0,0.3);z-index:9999;pointer-events:none;user-select:none;">' . htmlspecialchars($watermarkText) . '</div>';
        $html = str_replace($watermarkDiv, '', $html);
        $html = str_ireplace($watermarkDiv . '</body>', '</body>', $html);
        Response::success(['content' => $html]);
        break;

    case 'update':
        $user = $auth->requireLogin();
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $project = $db->queryOne("SELECT * FROM projects WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$project) Response::notFound('作品不存在');

        $isMember = $auth->isMember($user);
        $updates = [];
        $params = [];

        if (isset($data['name'])) { $updates[] = "name = ?"; $params[] = trim($data['name']); }
        if (isset($data['description'])) { $updates[] = "description = ?"; $params[] = trim($data['description']); }
        if (isset($data['tags'])) { $updates[] = "tags = ?"; $params[] = trim($data['tags']); }
        if (isset($data['is_permanent'])) {
            $isPermanent = intval($data['is_permanent']);
            if ($isPermanent && !$isMember && $user['role'] !== 'admin') {
                Response::error('非会员无法设置永久保存，请开通会员');
            }
            if ($isPermanent) {
                $updates[] = "expires_at = NULL";
            } else {
                // 取消永久，设置默认过期时间
                if ($project['expires_at'] === null) {
                    $defaultExpire = intval($db->getSetting('default_expire', '7'));
                    $updates[] = "expires_at = DATE_ADD(NOW(), INTERVAL ? DAY)";
                    $params[] = $defaultExpire;
                }
            }
        }
        if (isset($data['password'])) {
            if ($data['password'] && !$isMember && $user['role'] !== 'admin') {
                Response::error('密码保护为会员功能');
            }
            $updates[] = "password = ?";
            $params[] = $data['password'] ?: null;
        }
        if (isset($data['is_public'])) {
            $updates[] = "is_public = ?";
            $params[] = intval($data['is_public']);
        }
        if (isset($data['content'])) {
            $htmlContent = cleanHtml($data['content']);
            $projectDir = UPLOAD_DIR . '/' . $project['short_code'];
            file_put_contents($projectDir . '/index.html', $htmlContent);
            $updates[] = "file_size = ?";
            $params[] = strlen($htmlContent);
            $updates[] = "file_count = ?";
            $params[] = 1;
        }

        if (!empty($updates)) {
            $params[] = $id;
            $db->execute("UPDATE projects SET " . implode(', ', $updates) . " WHERE id = ?", $params);
        }
        $auth->log($user['id'], 'update_project', "更新作品: {$project['name']}");
        Response::success(null, '更新成功');
        break;

    case 'delete':
        $user = $auth->requireLogin();
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $project = $db->queryOne("SELECT * FROM projects WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$project) Response::notFound('作品不存在');

        // 删除文件
        $projectDir = UPLOAD_DIR . '/' . $project['short_code'];
        if (is_dir($projectDir)) cleanDir($projectDir);

        // 释放存储
        if ($user['role'] !== 'admin') {
            $db->execute("UPDATE users SET storage_used = GREATEST(0, storage_used - ?) WHERE id = ?", [$project['file_size'], $user['id']]);
        }

        $db->execute("DELETE FROM projects WHERE id = ?", [$id]);
        $auth->log($user['id'], 'delete_project', "删除作品: {$project['name']}");
        Response::success(null, '删除成功');
        break;

    case 'batch_delete':
        $user = $auth->requireLogin();
        $data = json_decode(file_get_contents('php://input'), true);
        $ids = $data['ids'] ?? [];
        if (empty($ids)) Response::error('请选择作品');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $projects = $db->query("SELECT id, short_code, file_size FROM projects WHERE id IN ($placeholders) AND user_id = ?", array_merge($ids, [$user['id']]));
        foreach ($projects as $p) {
            $dir = UPLOAD_DIR . '/' . $p['short_code'];
            if (is_dir($dir)) cleanDir($dir);
            if ($user['role'] !== 'admin') {
                $db->execute("UPDATE users SET storage_used = GREATEST(0, storage_used - ?) WHERE id = ?", [$p['file_size'], $user['id']]);
            }
        }
        $db->execute("DELETE FROM projects WHERE id IN ($placeholders) AND user_id = ?", array_merge($ids, [$user['id']]));
        Response::success(null, '批量删除成功');
        break;

    case 'download':
        $user = $auth->requireLogin();
        $id = intval($_GET['id'] ?? 0);
        $project = $db->queryOne("SELECT * FROM projects WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$project) Response::notFound();

        $projectDir = UPLOAD_DIR . '/' . $project['short_code'];
        if (!is_dir($projectDir)) Response::error('文件不存在');

        $zipFile = TEMP_DIR . '/' . $project['short_code'] . '.zip';
        $zip = new ZipArchive;
        $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        // 使用作品名称作为压缩包内根目录，避免暴露服务器路径
        $rootName = preg_replace('/[\\\\\/:*?"<>|]/', '_', $project['name']);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectDir, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (!$file->isDir()) {
                // 计算相对作品目录的路径，再以作品名作为根目录
                $relativePath = substr($file->getPathname(), strlen($projectDir) + 1);
                $zip->addFile($file->getPathname(), $rootName . '/' . $relativePath);
            }
        }
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $project['name'] . '.zip"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);
        @unlink($zipFile);
        exit;
        break;

    case 'clone':
        $user = $auth->requireLogin();
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $project = $db->queryOne("SELECT * FROM projects WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$project) Response::notFound();

        $newCode = generateShortCode(intval($db->getSetting('short_code_length', '6')));
        $srcDir = UPLOAD_DIR . '/' . $project['short_code'];
        $dstDir = UPLOAD_DIR . '/' . $newCode;
        recurseCopy($srcDir, $dstDir);

        $pid = $db->insert(
            "INSERT INTO projects (user_id, name, description, tags, short_code, file_size, file_count, expires_at, password, no_watermark, status, view_count, created_at) VALUES (?, CONCAT(?,' (副本)'), ?, ?, ?, ?, ?, ?, ?, ?, 1, 0, NOW())",
            [$user['id'], $project['name'], $project['description'], $project['tags'], $newCode, $project['file_size'], $project['file_count'], $project['expires_at'], $project['password'], $project['no_watermark']]
        );
        if ($user['role'] !== 'admin') {
            $db->execute("UPDATE users SET storage_used = storage_used + ? WHERE id = ?", [$project['file_size'], $user['id']]);
        }
        Response::success(['id' => $pid, 'short_code' => $newCode], '克隆成功');
        break;

    case 'embed':
        $user = $auth->requireLogin();
        $id = intval($_GET['id'] ?? 0);
        $project = $db->queryOne("SELECT short_code FROM projects WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$project) Response::notFound();
        $siteUrl = rtrim($db->getSetting('site_url', ''), '/');
        $iframe = '<iframe src="' . $siteUrl . '/s/' . $project['short_code'] . '" width="100%" height="600" frameborder="0"></iframe>';
        Response::success(['iframe' => $iframe]);
        break;

    case 'detail_stats':
        $user = $auth->requireLogin();
        $id = intval($_GET['id'] ?? 0);
        $project = $db->queryOne("SELECT id, view_count FROM projects WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$project) Response::notFound();
        $totalViews = intval($project['view_count']);
        $totalUv = intval($db->queryOne("SELECT COUNT(DISTINCT ip) as cnt FROM visit_logs WHERE project_id = ?", [$id])['cnt']);
        $todayViews = intval($db->queryOne("SELECT COUNT(*) as cnt FROM visit_logs WHERE project_id = ? AND DATE(created_at) = CURDATE()", [$id])['cnt']);
        // 今日剩余访问次数
        $dailyLimit = intval($db->getSetting('non_member_daily_limit', '0'));
        $dailyRemaining = -1;
        if ($dailyLimit > 0 && $user['role'] !== 'admin' && $user['role'] !== 'member') {
            $dailyRemaining = max(0, $dailyLimit - $todayViews);
        }
        Response::success([
            'total_views' => $totalViews,
            'total_uv' => $totalUv,
            'today_views' => $todayViews,
            'daily_remaining' => $dailyRemaining,
            'daily_limit' => $dailyLimit,
        ]);
        break;

    default:
        Response::error('未知操作', 1, 404);
}

// 辅助函数
function generateShortCode($length = 6) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) $code .= $chars[rand(0, strlen($chars) - 1)];
    return $code;
}

function formatBytes($bytes) {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' GB';
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}

function cleanHtml($html) {
    // XSS过滤（受后台设置控制）
    $xssFilter = Database::getInstance()->getSetting('xss_filter', '1');
    if ($xssFilter === '1') {
        // 仅过滤危险的事件处理器和内联脚本
        $html = preg_replace('/\bon\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/javascript\s*:/i', '', $html);
    }
    return $html;
}

function injectWatermark($html, $text) {
    $watermark = '<div style="position:fixed;bottom:10px;right:10px;font-size:12px;color:rgba(0,0,0,0.3);z-index:9999;pointer-events:none;">' . htmlspecialchars($text) . '</div>';
    if (stripos($html, '</body>') !== false) {
        $html = str_ireplace('</body>', $watermark . '</body>', $html);
    } else {
        $html .= $watermark;
    }
    return $html;
}

function cleanDir($dir) {
    if (!is_dir($dir)) return;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        if ($file->isDir()) rmdir($file->getRealPath());
        else unlink($file->getRealPath());
    }
    rmdir($dir);
}

function dirSize($dir) {
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
        if ($file->isFile()) $size += $file->getSize();
    }
    return $size;
}

function recurseCopy($src, $dst) {
    $dir = opendir($src);
    if (!is_dir($dst)) mkdir($dst, 0755, true);
    while (false !== ($file = readdir($dir))) {
        if ($file !== '.' && $file !== '..') {
            if (is_dir($src . '/' . $file)) recurseCopy($src . '/' . $file, $dst . '/' . $file);
            else copy($src . '/' . $file, $dst . '/' . $file);
        }
    }
    closedir($dir);
}
