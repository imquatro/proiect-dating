<?php
// Minimal environment-based DB configuration. Treat localhost and private LAN IPs as local.
$serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
$serverAddr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';

function isPrivateIp($ip) {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/', $ip) === 1;
    }
    return false;
}

$isLocalName = in_array($serverName, ['localhost', '127.0.0.1']);
$isLocalIp   = isPrivateIp($serverName) || isPrivateIp($serverAddr);
$isCli       = php_sapi_name() === 'cli';
$isLocal     = $isLocalName || $isLocalIp || $isCli;

if ($isLocal) {
    define('DB_HOST', '127.0.0.1');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'datingz1');
} else {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'barnsoci_barnsoci');
    define('DB_PASS', 'aw94Z33qtU');
    define('DB_NAME', 'barnsoci_datingz1');
}