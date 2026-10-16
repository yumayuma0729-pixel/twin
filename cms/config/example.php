<?php
// Copy to local.php OUTSIDE public_html. Never commit actual values.
return [
 'env'=>'production',
 'base_url'=>'https://example.com',
 'db_dsn'=>'mysql:host=YOUR_MYSQL_HOST;dbname=YOUR_DATABASE;charset=utf8mb4',
 'db_user'=>'YOUR_DATABASE_USER', 'db_password'=>'',
 'auth_key'=>'', // Generate with: php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'
 'site_root'=>'/home/YOUR_ACCOUNT/YOUR_DOMAIN/public_html',
 'media_root'=>'/home/YOUR_ACCOUNT/twin-cms/storage/media',
 'indexing_enabled'=>false,
 'inquiries_enabled'=>false,
 'notification_to'=>'', 'notification_from'=>'', 'mail_enabled'=>false,
];
