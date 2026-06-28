<?php
/**
 * TempWeb v2 - 过期作品清理定时任务
 * 每天凌晨3点执行: 0 3 * * * php /path/to/backend/cron/cleanup.php
 */
require_once __DIR__ . '/../database.php';

$db = Database::getInstance();

if ($db->getSetting('auto_cleanup', '1') !== '1') {
    echo "Auto cleanup disabled.\n";
    exit;
}

// 查找过期作品
$expired = $db->query("SELECT id, user_id, short_code, file_size, name FROM projects WHERE status = 1 AND expires_at IS NOT NULL AND expires_at < NOW()");

$count = 0;
foreach ($expired as $project) {
    // 删除文件
    $dir = UPLOAD_DIR . '/' . $project['short_code'];
    if (is_dir($dir)) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $f) {
            if ($f->isDir()) rmdir($f->getRealPath());
            else unlink($f->getRealPath());
        }
        rmdir($dir);
    }

    // 更新状态
    $db->execute("UPDATE projects SET status = 2 WHERE id = ?", [$project['id']]);

    // 释放存储
    if ($project['user_id']) {
        $db->execute("UPDATE users SET storage_used = GREATEST(0, storage_used - ?) WHERE id = ?", [$project['file_size'], $project['user_id']]);
    }

    $count++;
}

echo "Cleaned up $count expired projects.\n";
