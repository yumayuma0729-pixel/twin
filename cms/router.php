<?php
declare(strict_types=1);
// PHP development server router; Xserver uses public/.htaccess.
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if($path==='/cms-admin.css'||$path==='/cms-admin.js')return false;
require __DIR__.'/public/index.php';
