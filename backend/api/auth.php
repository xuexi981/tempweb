<?php
/**
 * TempWeb v2 - 认证API
 */
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../response.php';
require_once __DIR__ . '/../auth.php';

$db = Database::getInstance();
$auth = new Auth();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'captcha':
        $data = $auth->generateCaptcha();
        Response::success($data);
        break;

    case 'register':
        $data = json_decode(file_get_contents('php://input'), true);
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $captchaToken = $data['captcha_token'] ?? '';
        $captchaCode = $data['captcha_code'] ?? '';
        $inviteCode = trim($data['invite_code'] ?? '');

        // 检查注册开关
        if ($db->getSetting('allow_register', '1') !== '1') {
            Response::error('注册功能已关闭');
        }

        // 验证码
        if ($db->getSetting('captcha_register', '0') === '1') {
            if (!$auth->verifyCaptcha($captchaToken, $captchaCode)) {
                Response::error('验证码错误');
            }
        }

        // 邀请码
        if ($db->getSetting('require_invite', '0') === '1') {
            if (empty($inviteCode)) {
                $required = $db->getSetting('invite_required', '0') === '1';
                if ($required) Response::error('邀请码不能为空');
            } else {
                $invite = $db->queryOne("SELECT id, max_uses, used_count, expires_at, status FROM invite_codes WHERE code = ?", [$inviteCode]);
                if (!$invite || !$invite['status']) Response::error('邀请码无效');
                if ($invite['max_uses'] > 0 && $invite['used_count'] >= $invite['max_uses']) Response::error('邀请码已用完');
                if ($invite['expires_at'] && strtotime($invite['expires_at']) < time()) Response::error('邀请码已过期');
            }
        }

        if (mb_strlen($username) < 3 || mb_strlen($username) > 50) Response::error('用户名需3-50个字符');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) Response::error('邮箱格式不正确');
        if (strlen($password) < 6) Response::error('密码至少6个字符');

        if ($db->queryOne("SELECT id FROM users WHERE username = ?", [$username])) Response::error('用户名已存在');
        if ($db->queryOne("SELECT id FROM users WHERE email = ?", [$email])) Response::error('邮箱已注册');

        $hash = $auth->hashPassword($password);
        $storageQuota = intval($db->getSetting('storage_quota', '524288000')); // 500MB
        $uid = $db->insert("INSERT INTO users (username, email, password, role, status, storage_used, storage_quota, created_at) VALUES (?, ?, ?, 'user', 1, 0, ?, NOW())", [$username, $email, $hash, $storageQuota]);

        // 消耗邀请码
        if (!empty($inviteCode)) {
            $db->execute("UPDATE invite_codes SET used_count = used_count + 1 WHERE code = ?", [$inviteCode]);
            if ($invite && $invite['max_uses'] > 0 && $invite['used_count'] + 1 >= $invite['max_uses']) {
                $db->execute("UPDATE invite_codes SET status = 0 WHERE code = ?", [$inviteCode]);
            }
        }

        $token = $auth->generateToken($uid, $username, 'user');
        $auth->log($uid, 'register', '用户注册');
        Response::success(['token' => $token, 'user' => ['id' => $uid, 'username' => $username, 'email' => $email, 'role' => 'user', 'is_member' => false]], '注册成功');
        break;

    case 'login':
        $data = json_decode(file_get_contents('php://input'), true);
        $account = trim($data['account'] ?? '');
        $password = $data['password'] ?? '';
        $captchaToken = $data['captcha_token'] ?? '';
        $captchaCode = $data['captcha_code'] ?? '';

        // 验证码
        if ($db->getSetting('captcha_login', '0') === '1') {
            if (!$auth->verifyCaptcha($captchaToken, $captchaCode)) {
                Response::error('验证码错误');
            }
        }

        if (empty($account) || empty($password)) Response::error('请填写账号和密码');

        $user = $db->queryOne("SELECT id, username, email, password, role, status, storage_used, storage_quota, member_expires_at FROM users WHERE username = ? OR email = ?", [$account, $account]);
        if (!$user || !$auth->verifyPassword($password, $user['password'])) {
            Response::error('账号或密码错误');
        }
        if (!$user['status']) Response::error('账号已被禁用');

        $token = $auth->generateToken($user['id'], $user['username'], $user['role']);
        unset($user['password']);
        $isMember = $auth->isMember($user);
        // 根据会员身份动态获取配额（完全使用后台设置）
        $quota = $isMember ? intval($db->getSetting('storage_quota_member', '1073741824')) : intval($db->getSetting('storage_quota', '524288000'));
        $user['storage_quota'] = $quota;
        $user['is_member'] = $isMember;
        $auth->log($user['id'], 'login', '用户登录');
        Response::success(['token' => $token, 'user' => $user], '登录成功');
        break;

    case 'me':
        $user = $auth->requireLogin();
        unset($user['password']);
        // 根据会员身份动态获取配额（完全使用后台设置）
        $isMember = $auth->isMember($user);
        $quota = $isMember ? intval($db->getSetting('storage_quota_member', '1073741824')) : intval($db->getSetting('storage_quota', '524288000'));
        $user['storage_quota'] = $quota;
        // 格式化存储
        $user['storage_used_formatted'] = formatBytes($user['storage_used']);
        $user['storage_quota_formatted'] = formatBytes($quota);
        $user['is_member'] = $isMember;
        Response::success($user);
        break;

    case 'update_profile':
        $user = $auth->requireLogin();
        $data = json_decode(file_get_contents('php://input'), true);
        $nickname = trim($data['nickname'] ?? '');
        if ($nickname) {
            $db->execute("UPDATE users SET nickname = ? WHERE id = ?", [$nickname, $user['id']]);
        }
        $auth->log($user['id'], 'update_profile', '修改资料');
        Response::success(null, '更新成功');
        break;

    case 'change_password':
        $user = $auth->requireLogin();
        $data = json_decode(file_get_contents('php://input'), true);
        $oldPwd = $data['old_password'] ?? '';
        $newPwd = $data['new_password'] ?? '';
        $row = $db->queryOne("SELECT password FROM users WHERE id = ?", [$user['id']]);
        if (!$auth->verifyPassword($oldPwd, $row['password'])) Response::error('原密码错误');
        if (strlen($newPwd) < 6) Response::error('新密码至少6个字符');
        $db->execute("UPDATE users SET password = ? WHERE id = ?", [$auth->hashPassword($newPwd), $user['id']]);
        $auth->log($user['id'], 'change_password', '修改密码');
        Response::success(null, '密码修改成功');
        break;

    case 'forgot_password':
        $data = json_decode(file_get_contents('php://input'), true);
        $email = trim($data['email'] ?? '');
        if ($db->getSetting('mail_forgot', '0') !== '1') Response::error('忘记密码功能未开启');
        // 简化：生成重置token，实际应发邮件
        $user = $db->queryOne("SELECT id, username FROM users WHERE email = ?", [$email]);
        if (!$user) Response::error('邮箱未注册');
        $resetToken = md5(uniqid(mt_rand(), true));
        $db->execute("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))", [$user['id'], $resetToken]);
        Response::success(['reset_token' => $resetToken], '重置链接已发送到您的邮箱');
        break;

    case 'reset_password':
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? '';
        $newPwd = $data['new_password'] ?? '';
        $row = $db->queryOne("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()", [$token]);
        if (!$row) Response::error('重置链接无效或已过期');
        if (strlen($newPwd) < 6) Response::error('密码至少6个字符');
        $db->execute("UPDATE users SET password = ? WHERE id = ?", [$auth->hashPassword($newPwd), $row['user_id']]);
        $db->execute("DELETE FROM password_resets WHERE token = ?", [$token]);
        Response::success(null, '密码重置成功');
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
