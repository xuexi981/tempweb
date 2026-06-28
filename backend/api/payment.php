<?php
/**
 * TempWeb v2 - 支付API
 */
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../response.php';
require_once __DIR__ . '/../auth.php';

$db = Database::getInstance();
$auth = new Auth();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create_order':
        $user = $auth->requireLogin();
        $data = json_decode(file_get_contents('php://input'), true);
        $plan = $data['plan'] ?? 'month';

        $method = $db->getSetting('payment_method', 'none');
        if ($method === 'none') Response::error('支付功能未开启');

        $price = $plan === 'year' ? floatval($db->getSetting('member_price_year', '0')) : floatval($db->getSetting('member_price_month', '0'));
        if ($price <= 0) Response::error('价格配置错误');

        $orderNo = 'TW' . date('YmdHis') . rand(1000, 9999);
        $days = $plan === 'year' ? 365 : 30;

        $db->insert("INSERT INTO orders (user_id, order_no, amount, days, plan, payment_method, status, created_at) VALUES (?,?,?,?,?,?,'pending',NOW())",
            [$user['id'], $orderNo, $price, $days, $plan, $method]);

        if ($method === 'epay') {
            $pid = $db->getSetting('epay_pid', '');
            $key = $db->getSetting('epay_key', '');
            $url = $db->getSetting('epay_url', '');
            $type = $db->getSetting('epay_type', 'alipay');
            $siteUrl = rtrim($db->getSetting('site_url', ''), '/');

            $planName = $plan === 'year' ? '年度' : '月度';
            $params = [
                'pid' => $pid,
                'type' => $type,
                'out_trade_no' => $orderNo,
                'notify_url' => $siteUrl . '/api/payment.php?action=notify',
                'return_url' => $siteUrl . '/projects.html',
                'name' => "TempWeb {$planName}会员",
                'money' => $price,
            ];
            ksort($params);
            $signStr = http_build_query($params) . $key;
            $params['sign'] = md5($signStr);
            $params['sign_type'] = 'MD5';

            $payUrl = $url . '/submit.php?' . http_build_query($params);
            Response::success(['pay_url' => $payUrl, 'order_no' => $orderNo]);
        } else if ($method === 'manual') {
            $info = $db->getSetting('manual_pay_info', '请联系管理员完成支付');
            Response::success(['type' => 'manual', 'info' => $info, 'order_no' => $orderNo]);
        }
        break;

    case 'notify':
        // 易支付回调（支持GET和POST）
        $key = $db->getSetting('epay_key', '');
        $params = $_POST ?: $_GET;
        $sign = $params['sign'] ?? '';
        unset($params['sign'], $params['sign_type']);
        ksort($params);
        $signStr = http_build_query($params) . $key;
        if (md5($signStr) !== $sign) { echo 'sign error'; exit; }

        $orderNo = $params['out_trade_no'] ?? '';
        $tradeNo = $params['trade_no'] ?? '';
        $order = $db->queryOne("SELECT * FROM orders WHERE order_no = ? AND status = 'pending'", [$orderNo]);
        if (!$order) { echo 'order not found'; exit; }

        if ($params['trade_status'] === 'TRADE_SUCCESS') {
            $db->execute("UPDATE orders SET status='paid', trade_no=?, paid_at=NOW() WHERE order_no=?", [$tradeNo, $orderNo]);
            // 开通会员
            $days = $order['days'];
            $uid = $order['user_id'];
            $currentMember = $db->queryOne("SELECT member_expires_at FROM users WHERE id = ?", [$uid]);
            $baseTime = ($currentMember['member_expires_at'] && strtotime($currentMember['member_expires_at']) > time()) ? strtotime($currentMember['member_expires_at']) : time();
            $newExpiry = date('Y-m-d H:i:s', $baseTime + $days * 86400);
            $db->execute("UPDATE users SET role='member', member_expires_at=? WHERE id=?", [$newExpiry, $uid]);
        }
        echo 'success';
        exit;
        break;

    case 'activate_code':
        $user = $auth->requireLogin();
        $data = json_decode(file_get_contents('php://input'), true);
        $code = trim($data['code'] ?? '');
        if (empty($code)) Response::error('请输入卡密');

        $row = $db->queryOne("SELECT * FROM member_codes WHERE code = ? AND status = 'unused'", [$code]);
        if (!$row) Response::error('卡密无效或已使用');

        $days = $row['days'];
        $uid = $user['id'];
        $currentMember = $db->queryOne("SELECT member_expires_at FROM users WHERE id = ?", [$uid]);
        $baseTime = ($currentMember['member_expires_at'] && strtotime($currentMember['member_expires_at']) > time()) ? strtotime($currentMember['member_expires_at']) : time();
        $newExpiry = date('Y-m-d H:i:s', $baseTime + $days * 86400);

        $db->execute("UPDATE users SET role='member', member_expires_at=? WHERE id=?", [$newExpiry, $uid]);
        $db->execute("UPDATE member_codes SET status='used', used_by=?, used_at=NOW() WHERE id=?", [$uid, $row['id']]);
        $auth->log($uid, 'activate_code', "激活会员卡密，{$days}天");
        Response::success(null, '激活成功，会员已开通');
        break;

    case 'my_api_keys':
        $user = $auth->requireLogin();
        $list = $db->query("SELECT id, name, api_key, status, request_count, last_used_at, created_at FROM api_keys WHERE user_id = ? ORDER BY created_at DESC", [$user['id']]);
        Response::success($list);
        break;

    case 'create_api_key':
        $user = $auth->requireLogin();
        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');
        if (empty($name)) Response::error('请输入名称');
        $apiKey = 'tw_' . md5(uniqid(mt_rand(), true));
        $secret = bin2hex(random_bytes(32));
        $db->insert("INSERT INTO api_keys (user_id, name, api_key, secret, status, request_count, created_at) VALUES (?,?,?,?,1,0,NOW())",
            [$user['id'], $name, $apiKey, $secret]);
        $auth->log($user['id'], 'create_api_key', "创建API Key: $name");
        Response::success(['api_key' => $apiKey, 'secret' => $secret], '创建成功');
        break;

    case 'toggle_api_key':
        $user = $auth->requireLogin();
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id'] ?? 0);
        $row = $db->queryOne("SELECT status FROM api_keys WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$row) Response::notFound();
        $db->execute("UPDATE api_keys SET status = ? WHERE id = ?", [$row['status'] ? 0 : 1, $id]);
        Response::success(null, '操作成功');
        break;

    case 'delete_api_key':
        $user = $auth->requireLogin();
        $data = json_decode(file_get_contents('php://input'), true);
        $db->execute("DELETE FROM api_keys WHERE id = ? AND user_id = ?", [intval($data['id'] ?? 0), $user['id']]);
        Response::success(null, '删除成功');
        break;

    case 'check_order':
        $user = $auth->requireLogin();
        $orderNo = $_GET['order_no'] ?? '';
        if (empty($orderNo)) Response::error('订单号不能为空');
        $order = $db->queryOne("SELECT * FROM orders WHERE order_no = ? AND user_id = ?", [$orderNo, $user['id']]);
        if (!$order) Response::notFound();
        Response::success(['status' => $order['status'], 'plan' => $order['plan'], 'amount' => $order['amount']]);
        break;

    default:
        Response::error('未知操作', 1, 404);
}
