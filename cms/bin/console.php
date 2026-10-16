<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/../app/bootstrap.php';
$cmd=$argv[1]??'';
if($cmd==='migrate'){
 $sql=file_get_contents(__DIR__.'/../database/001_initial.sql');db()->exec($sql);echo "データベースの初期化・確認が完了しました。\n";
}elseif($cmd==='user:create'){
 $email=strtolower(trim($argv[2]??''));$name=$argv[3]??'管理者';$role=$argv[4]??'admin';
 if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>190||!in_array($role,['admin','editor','support'],true)){fwrite(STDERR,"Usage: php bin/console.php user:create EMAIL NAME [admin|editor|support] < password-file\n");exit(1);}
 $password=rtrim(stream_get_contents(STDIN),"\r\n");if(strlen($password)<14||strlen($password)>72){fwrite(STDERR,"パスワードは14〜72バイトで標準入力へ渡してください。\n");exit(1);}
 query('INSERT INTO users(email,name,password_hash,role) VALUES(?,?,?,?)',[$email,$name,password_hash($password,PASSWORD_DEFAULT),$role]);echo "アカウントを作成しました。\n";
}elseif($cmd==='user:password'){
 $email=strtolower(trim($argv[2]??''));$password=rtrim(stream_get_contents(STDIN),"\r\n");if(strlen($password)<14||strlen($password)>72)exit(1);
 query('UPDATE users SET password_hash=? WHERE email=?',[password_hash($password,PASSWORD_DEFAULT),$email]);echo "パスワード更新処理が完了しました。\n";
}elseif($cmd==='notify'){
 require __DIR__.'/../app/notify.php';run_notifications();
}else{echo "Commands: migrate, user:create EMAIL NAME ROLE, user:password EMAIL, notify\n";}
