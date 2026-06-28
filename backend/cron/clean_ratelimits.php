<?php
/**
 * TempWeb v2 - 清理过期频率限制记录
 * 每小时执行: 0 * * * * php /path/to/backend/cron/clean_ratelimits.php
 */
require_once __DIR__ . '/../database.php';

$db = Database::getInstance();
$db->execute("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
$db->execute("DELETE FROM captcha_tokens WHERE expires_at < NOW()");
echo "Cleaned up rate limits and captcha tokens.\n";
