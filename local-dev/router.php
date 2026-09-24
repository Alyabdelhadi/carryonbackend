<?php
// Router for PHP's built-in server.
//  - Files that exist on disk (uploads, admin assets) are served directly.
//  - Uploads missing locally are redirected to the production copy, so a
//    fresh checkout still shows every image the database refers to.
//  - Everything else goes to Laravel.
$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . '/..' . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}
if (str_starts_with($path, '/upload/') && !is_file($file)) {
    header('Location: https://carryon.app/admin' . $path, true, 302);
    exit;
}
require __DIR__ . '/../index.php';
