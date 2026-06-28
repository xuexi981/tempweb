<?php
/**
 * TempWeb v2 - 管理后台API
 */
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../response.php';
require_once __DIR__ . '/../auth.php';

$db = Database::getInstance();
$auth = new Auth();
$action = $_GET['action'] ?? '';

// 公开站点信息（无需管理员权限）
if ($action === 'site_info') {
    $info = [
        'site_name' => $db->getSetting('site_name', 'TempWeb'),
        'site_desc' => $db->getSetting('site_desc', '开源免费临时HTML/ZIP托管平台'),
        'site_url' => $db->getSetting('site_url', ''),
        'site_logo' => $db->getSetting('site_logo', ''),
        'footer_links' => $db->getSetting('footer_links', ''),
        'allow_register' => $db->getSetting('allow_register', '1'),
        'captcha_login' => $db->getSetting('captcha_login', '0'),
        'captcha_register' => $db->getSetting('captcha_register', '0'),
        'require_invite' => $db->getSetting('require_invite', '0'),
        'allow_guest_upload' => $db->getSetting('allow_guest_upload', '1'),
        'mail_forgot' => $db->getSetting('mail_forgot', '0'),
        'member_purchase_url' => $db->getSetting('member_purchase_url', ''),
        'project_count' => $db->queryOne("SELECT COUNT(*) as cnt FROM projects")['cnt'],
        'visit_count' => $db->queryOne("SELECT SUM(view_count) as total FROM projects")['total'] ?? 0,
        'default_expire' => $db->getSetting('default_expire', '7'),
        'default_expire_guest' => $db->getSetting('default_expire_guest', '7'),
        'default_expire_member' => $db->getSetting('default_expire_member', '30'),
    ];

    // 当前用户角色
    $currentUser = $auth->getCurrentUser();
    if ($currentUser) {
        $info['current_user_role'] = $currentUser['role'];
    }

    Response::success($info);
    exit;
}

// 公告接口对所有登录用户开放
if ($action === 'announcements') {
    $list = $db->query("SELECT * FROM announcements WHERE status = 1 ORDER BY created_at DESC");
    Response::success($list);
    exit;
}

$user = $auth->requireAdmin();

switch ($action) {
    case 'dashboard':
        $userCount = $db->queryOne("SELECT COUNT(*) as cnt FROM users")['cnt'];
        $projectCount = $db->queryOne("SELECT COUNT(*) as cnt FROM projects")['cnt'];
        $todayVisits = $db->queryOne("SELECT COUNT(*) as cnt FROM visit_logs WHERE DATE(created_at) = CURDATE()")['cnt'];
        $storageUsed = $db->queryOne("SELECT SUM(file_size) as total FROM projects")['total'] ?? 0;
        $recentLogs = $db->query("SELECT l.*, u.username FROM logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 10");

        Response::success([
            'user_count' => $userCount,
            'project_count' => $projectCount,
            'today_visits' => $todayVisits,
            'storage_used' => $storageUsed,
            'storage_used_formatted' => formatBytes($storageUsed),
            'recent_logs' => $recentLogs,
            'php_version' => PHP_VERSION,
            'disk_free' => round(disk_free_space('/') / 1073741824, 1) . ' GB',
        ]);
        break;

    case 'users':
        $page = max(1, intval($_GET['page'] ?? 1));
        $pageSize = min(50, max(1, intval($_GET['pageSize'] ?? 20)));
        $keyword = $_GET['keyword'] ?? '';
        $role = $_GET['role'] ?? '';
        $offset = ($page - 1) * $pageSize;

        $where = "WHERE 1=1";
        $params = [];
        if ($keyword) { $where .= " AND (username LIKE ? OR email LIKE ?)"; $params[] = "%$keyword%"; $params[] = "%$keyword%"; }
        if ($role) { $where .= " AND role = ?"; $params[] = $role; }

        $total = $db->queryOne("SELECT COUNT(*) as cnt FROM users $where", $params)['cnt'];
        $list = $db->query("SELECT id, username, email, role, status, storage_used, storage_quota, member_expires_at, created_at FROM users $where ORDER BY created_at DESC LIMIT $offset, $pageSize", $params);
        foreach ($list as &$u) {
            $u['storage_used_formatted'] = formatBytes($u['storage_used']);
            $u['storage_quota_formatted'] = formatBytes($u['storage_quota']);
        }

        Response::paginate($list, $total, $page, $pageSize);
        break;

    case 'user_detail':
        $id = intval($_GET['id'] ?? 0);
        $u = $db->queryOne("SELECT id, username, email, role, status, storage_used, storage_quota, member_expires_at, created_at FROM users WHERE id = ?", [$id]);
        if (!$u) Response::notFound();
        $u['storage_used_formatted'] = formatBytes($u['storage_used']);
        $u['storage_quota_formatted'] = formatBytes($u['storage_quota']);
        Response::success($u);
        break;

    case 'update_user':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        if ($id === $user['id']) Response::error('不能修改自己的账号');
        $u = $db->queryOne("SELECT role FROM users WHERE id = ?", [$id]);
        if (!$u) Response::notFound();
        if ($u['role'] === 'admin' && $data['role'] !== 'admin') Response::error('不能修改管理员角色');

        $updates = [];
        $params = [];
        if (isset($data['email'])) { $updates[] = 'email = ?'; $params[] = $data['email']; }
        if (isset($data['role'])) { $updates[] = 'role = ?'; $params[] = $data['role']; }
        if (isset($data['status'])) { $updates[] = 'status = ?'; $params[] = intval($data['status']); }
        if (isset($data['storage_quota'])) { $updates[] = 'storage_quota = ?'; $params[] = intval($data['storage_quota']); }
        if (array_key_exists('member_expires_at', $data)) {
            $expiresAt = $data['member_expires_at'] ? date('Y-m-d H:i:s', strtotime($data['member_expires_at'])) : null;
            $updates[] = 'member_expires_at = ?';
            $params[] = $expiresAt;
        }
        if (!empty($data['password'])) {
            $updates[] = 'password = ?';
            $params[] = $auth->hashPassword($data['password']);
        }

        if (!empty($updates)) {
            $params[] = $id;
            $db->execute("UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?", $params);
        }
        $auth->log($user['id'], 'update_user', "更新用户ID $id 信息");
        Response::success(null, '更新成功');
        break;

    case 'toggle_user_role':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $u = $db->queryOne("SELECT role FROM users WHERE id = ?", [$id]);
        if (!$u) Response::notFound();
        if ($u['role'] === 'admin') Response::error('不能修改管理员角色');
        $newRole = $u['role'] === 'member' ? 'user' : 'member';
        if ($newRole === 'member') {
            $db->execute("UPDATE users SET role = ?, member_expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY) WHERE id = ?", [$newRole, $id]);
        } else {
            $db->execute("UPDATE users SET role = ?, member_expires_at = NULL WHERE id = ?", [$newRole, $id]);
        }
        $auth->log($user['id'], 'toggle_role', "用户ID $id 角色改为 $newRole");
        Response::success(null, '操作成功');
        break;

    case 'toggle_user_status':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        if ($id === $user['id']) Response::error('不能禁用自己的账号');
        $u = $db->queryOne("SELECT status, role FROM users WHERE id = ?", [$id]);
        if (!$u) Response::notFound();
        if ($u['role'] === 'admin') Response::error('不能修改管理员状态');
        $db->execute("UPDATE users SET status = ? WHERE id = ?", [intval($u['status']) ? 0 : 1, $id]);
        $auth->log($user['id'], 'toggle_status', "用户ID $id 状态切换");
        Response::success(null, '操作成功');
        break;

    case 'delete_user':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        if ($id === $user['id']) Response::error('不能删除自己');
        $u = $db->queryOne("SELECT role FROM users WHERE id = ?", [$id]);
        if ($u && $u['role'] === 'admin') Response::error('不能删除管理员账号');
        $db->execute("DELETE FROM users WHERE id = ?", [$id]);
        $auth->log($user['id'], 'delete_user', "删除用户ID $id");
        Response::success(null, '删除成功');
        break;

    case 'projects':
        $page = max(1, intval($_GET['page'] ?? 1));
        $pageSize = min(50, max(1, intval($_GET['pageSize'] ?? 20)));
        $keyword = $_GET['keyword'] ?? '';
        $offset = ($page - 1) * $pageSize;

        $where = "WHERE 1=1";
        $params = [];
        if ($keyword) { $where .= " AND (p.name LIKE ? OR p.short_code LIKE ?)"; $params[] = "%$keyword%"; $params[] = "%$keyword%"; }

        $total = $db->queryOne("SELECT COUNT(*) as cnt FROM projects p $where", $params)['cnt'];
        $list = $db->query("SELECT p.*, u.username FROM projects p LEFT JOIN users u ON p.user_id = u.id $where ORDER BY p.created_at DESC LIMIT $offset, $pageSize", $params);
        foreach ($list as &$p) {
            $p['file_size_formatted'] = formatBytes($p['file_size']);
        }

        Response::paginate($list, $total, $page, $pageSize);
        break;

    case 'delete_project':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $project = $db->queryOne("SELECT * FROM projects WHERE id = ?", [$id]);
        if (!$project) Response::notFound();
        $dir = UPLOAD_DIR . '/' . $project['short_code'];
        if (is_dir($dir)) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $f) { if ($f->isDir()) rmdir($f->getRealPath()); else unlink($f->getRealPath()); }
            rmdir($dir);
        }
        if ($project['user_id']) {
            $db->execute("UPDATE users SET storage_used = GREATEST(0, storage_used - ?) WHERE id = ?", [$project['file_size'], $project['user_id']]);
        }
        $db->execute("DELETE FROM projects WHERE id = ?", [$id]);
        $auth->log($user['id'], 'admin_delete_project', "删除作品: {$project['name']}");
        Response::success(null, '删除成功');
        break;

    case 'update_project':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $project = $db->queryOne("SELECT * FROM projects WHERE id = ?", [$id]);
        if (!$project) Response::notFound();
        $updates = [];
        $params = [];
        $fields = ['name', 'description', 'tags', 'password', 'is_public', 'status'];
        foreach ($fields as $f) {
            if (isset($data[$f])) {
                $updates[] = "$f = ?";
                $params[] = $data[$f];
            }
        }
        // 过期时间处理
        if (isset($data['is_permanent'])) {
            if (intval($data['is_permanent'])) {
                $updates[] = "expires_at = NULL";
            } else {
                $days = intval($data['expires_days'] ?? 7);
                $updates[] = "expires_at = DATE_ADD(NOW(), INTERVAL ? DAY)";
                $params[] = $days;
                $updates[] = "status = 1";
            }
        }
        if ($updates) {
            $params[] = $id;
            $db->execute("UPDATE projects SET " . implode(', ', $updates) . " WHERE id = ?", $params);
        }
        $auth->log($user['id'], 'admin_update_project', "编辑作品: {$project['name']}");
        Response::success(null, '更新成功');
        break;

    case 'get_project':
        $id = intval($_GET['id'] ?? 0);
        $project = $db->queryOne("SELECT * FROM projects WHERE id = ?", [$id]);
        if (!$project) Response::notFound();
        Response::success($project);
        break;

    case 'get_settings':
        $settings = $db->getAllSettings();
        // 确保所有分组存在
        $groups = ['basic', 'upload', 'security', 'mail', 'custom'];
        foreach ($groups as $g) {
            if (!isset($settings[$g])) $settings[$g] = [];
        }
        Response::success($settings);
        break;

    case 'save_settings':
        $data = json_decode(file_get_contents('php://input'), true);
        foreach ($data as $group => $items) {
            if (!is_array($items)) continue;
            foreach ($items as $key => $value) {
                $db->execute("INSERT INTO settings (`key`, `value`, `group`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)", [$key, $value, $group]);
            }
        }
        $auth->log($user['id'], 'save_settings', '更新系统设置');
        Response::success(null, '保存成功');
        break;

    case 'announcements_list':
        $list = $db->query("SELECT * FROM announcements ORDER BY created_at DESC");
        Response::success($list);
        break;

    case 'save_announcement':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        if ($id) {
            $db->execute("UPDATE announcements SET title=?, content=?, type=?, status=?, closable=? WHERE id=?",
                [$data['title'], $data['content'], $data['type'], intval($data['status']), intval($data['closable']), $id]);
        } else {
            $db->insert("INSERT INTO announcements (title, content, type, status, closable, created_at) VALUES (?,?,?,?,?,NOW())",
                [$data['title'], $data['content'], $data['type'], intval($data['status']), intval($data['closable'])]);
        }
        Response::success(null, '保存成功');
        break;

    case 'delete_announcement':
        $data = json_decode(file_get_contents('php://input'), true);
        $db->execute("DELETE FROM announcements WHERE id = ?", [intval($data['id'])]);
        Response::success(null, '删除成功');
        break;

    case 'invites':
        $list = $db->query("SELECT * FROM invite_codes ORDER BY created_at DESC");
        Response::success($list);
        break;

    case 'create_invites':
        $data = json_decode(file_get_contents('php://input'), true);
        $count = min(100, max(1, intval($data['count'] ?? 5)));
        $maxUses = intval($data['max_uses'] ?? 1);
        $expiresDays = intval($data['expires_days'] ?? 7);
        $expiresAt = $expiresDays > 0 ? date('Y-m-d H:i:s', time() + $expiresDays * 86400) : null;
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
            $db->insert("INSERT INTO invite_codes (code, max_uses, expires_at, status, created_at) VALUES (?,?,?,1,NOW())", [$code, $maxUses, $expiresAt]);
            $codes[] = $code;
        }
        $auth->log($user['id'], 'create_invites', "生成{$count}个邀请码");
        Response::success(['codes' => $codes], '生成成功');
        break;

    case 'member_codes':
        $list = $db->query("SELECT mc.*, u.username as used_by_name FROM member_codes mc LEFT JOIN users u ON mc.used_by = u.id ORDER BY mc.created_at DESC");
        Response::success($list);
        break;

    case 'create_member_codes':
        $data = json_decode(file_get_contents('php://input'), true);
        $count = min(100, max(1, intval($data['count'] ?? 5)));
        $days = intval($data['days'] ?? 30);
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 12));
            $db->insert("INSERT INTO member_codes (code, days, status, created_at) VALUES (?,?,'unused',NOW())", [$code, $days]);
            $codes[] = $code;
        }
        $auth->log($user['id'], 'create_member_codes', "生成{$count}个会员卡密");
        Response::success(['codes' => $codes], '生成成功');
        break;

    case 'logs':
        $page = max(1, intval($_GET['page'] ?? 1));
        $pageSize = min(100, max(1, intval($_GET['pageSize'] ?? 20)));
        $keyword = $_GET['keyword'] ?? '';
        $offset = ($page - 1) * $pageSize;
        $where = '';
        $params = [];
        if ($keyword) {
            $where = "WHERE (u.username LIKE ? OR l.action LIKE ? OR l.detail LIKE ?)";
            $params = ["%$keyword%", "%$keyword%", "%$keyword%"];
        }
        $total = $db->queryOne("SELECT COUNT(*) as cnt FROM logs l LEFT JOIN users u ON l.user_id = u.id $where", $params)['cnt'];
        $list = $db->query("SELECT l.*, u.username FROM logs l LEFT JOIN users u ON l.user_id = u.id $where ORDER BY l.created_at DESC LIMIT $offset, $pageSize", $params);
        Response::paginate($list, $total, $page, $pageSize);
        break;

    case 'clear_logs':
        $db->execute("DELETE FROM logs");
        $auth->log($user['id'], 'admin_clear_logs', '清除所有操作日志');
        Response::success(null, '清除成功');
        break;

    default:
        Response::error('未知操作', 1, 404);
}

function formatBytes($bytes) {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' GB';
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
    return $bytes . ' B';
}
