<?php
// config.php  –  Database connection (PDO)

define('DB_HOST', 'localhost');
define('DB_NAME', 'internship_portal');
define('DB_USER', 'root');          // change in production
define('DB_PASS', '');              // change in production
define('DB_CHARSET', 'utf8mb4');

define('UPLOAD_DIR', __DIR__ . '/uploads/resumes/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024);   // 2 MB
define('ALLOWED_MIME', ['application/pdf']);
define('ALLOWED_EXT',  ['pdf']);

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}
