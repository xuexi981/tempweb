<?php
/**
 * TempWeb v2 - 配置文件
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_NAME', 'tempweb');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('JWT_SECRET', '4d6b8dba771601d60e1e5c2705cbc3228dd701993d61b0e4f73ec40b9d76a617');
define('JWT_EXPIRE', 168);
define('SITE_ROOT', __DIR__ . '/..');
define('UPLOAD_DIR', SITE_ROOT . '/storage');
define('TEMP_DIR', SITE_ROOT . '/temp');

foreach ([UPLOAD_DIR, TEMP_DIR] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}
