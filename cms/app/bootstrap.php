<?php
declare(strict_types=1);
const CMS_ROOT = __DIR__ . '/..';
$localFile=CMS_ROOT.'/config/local.php';
$local=is_file($localFile)?require $localFile:[];
function option(string $key, mixed $default=null): mixed {
 global $local;
 $env=getenv('TWIN_'.strtoupper($key));
 return $env!==false ? $env : ($local[$key]??$default);
}
function enabled(string $key): bool {return filter_var(option($key,false),FILTER_VALIDATE_BOOLEAN);}
date_default_timezone_set('Asia/Tokyo');
ini_set('display_errors','0');
ini_set('log_errors','1');
function e(mixed $s): string {return htmlspecialchars((string)$s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function text_length(string $text): int {if(!preg_match('//u',$text))throw new InvalidArgumentException('文字コードを確認してください。');return preg_match_all('/./us',$text);}
function json_data(mixed $data): string {return json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);}
function db(): PDO {
 static $pdo;
 if(!$pdo){$pdo=new PDO((string)option('db_dsn'),(string)option('db_user'),(string)option('db_password'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);$pdo->exec("SET time_zone = '+00:00'");}
 return $pdo;
}
function query(string $sql,array $params=[]): PDOStatement {$q=db()->prepare($sql);$q->execute($params);return $q;}
function base_url(): string {return rtrim((string)option('base_url','http://127.0.0.1:8080'),'/');}
function site_root(): string {return rtrim((string)option('site_root',CMS_ROOT.'/../public'),'/');}
function media_root(): string {return rtrim((string)option('media_root',CMS_ROOT.'/storage/media'),'/');}
function secret_key(): string {$key=(string)option('auth_key','');if(strlen($key)<32)throw new RuntimeException('CMS authentication key is not configured');return $key;}
function csrf_token(): string {if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32));return $_SESSION['csrf'];}
function csrf_field(): string {return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">';}
function verify_csrf(): void {if(!isset($_POST['csrf'])||!is_string($_POST['csrf'])||!hash_equals(csrf_token(),$_POST['csrf']))abort_request(403,'操作の有効期限が切れました。再読み込みしてください。');}
function abort_request(int $code,string $message): never {http_response_code($code);header('Cache-Control: no-store');echo '<!doctype html><html lang="ja"><meta charset="UTF-8"><meta name="robots" content="noindex"><title>リクエストを確認してください</title><body><h1>'.e($message).'</h1><p><a href="/admin/">管理画面へ</a> · <a href="/">サイトへ</a></p></body></html>';exit;}
function redirect(string $path): never {header('Location: '.$path,true,303);exit;}
function flash(string $s): void {$_SESSION['flash']=$s;}
function current_user(): ?array {
 static $loaded=false,$user=null;
 if($loaded)return $user;$loaded=true;
 if(empty($_SESSION['uid']))return null;
 if(time()-(int)($_SESSION['last_activity']??0)>1800||time()-(int)($_SESSION['signed_in']??0)>43200){$_SESSION=[];return null;}
 $user=query('SELECT id,email,name,role,password_hash FROM users WHERE id=? AND active=1',[(int)$_SESSION['uid']])->fetch()?:null;if(!$user){$_SESSION=[];return null;}
 if($user&&(!isset($_SESSION['password_stamp'])||!hash_equals(hash_hmac('sha256',$user['password_hash'],secret_key()),$_SESSION['password_stamp']))){$user=null;$_SESSION=[];}if($user){unset($user['password_hash']);$_SESSION['last_activity']=time();}return $user;
}
function allowed(string $ability): bool {$u=current_user();return $u&&($u['role']==='admin'||($ability==='content'&&$u['role']==='editor')||($ability==='inquiry'&&$u['role']==='support'));}
function require_ability(string $ability): array {if(!current_user())redirect('/admin/login/');if(!allowed($ability))abort_request(403,'この操作の権限がありません。');return current_user();}
function audit(string $action,string $type,?int $id=null): void {query('INSERT INTO audit_logs(user_id,action,target_type,target_id) VALUES(?,?,?,?)',[current_user()['id']??null,$action,$type,$id]);}
function rate_allowed(string $kind,string $identity,int $limit,int $seconds): bool {
 $bucket=hash_hmac('sha256',$kind.'|'.$identity,secret_key());$now=time();
 query('INSERT INTO rate_limits(bucket,hits,window_started) VALUES(?,1,?) ON DUPLICATE KEY UPDATE hits=IF(window_started < ?,1,hits+1),window_started=IF(window_started < ?,VALUES(window_started),window_started)',[$bucket,$now,$now-$seconds,$now-$seconds]);
 return (int)query('SELECT hits FROM rate_limits WHERE bucket=?',[$bucket])->fetchColumn()<=$limit;
}
function datetime_jp(?string $date): string {return $date?(new DateTimeImmutable($date,new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Tokyo'))->format('Y/m/d H:i'):'—';}
function content_url(array $item): string {$prefix=['vehicle'=>'stock','story'=>'story','news'=>'news','column'=>'column'][$item['type']];return '/'.$prefix.'/'.rawurlencode($item['slug']).'/';}
function payload(array $row,bool $draft=false): array {return json_decode((string)($draft?$row['draft']:$row['published']),true,512,JSON_THROW_ON_ERROR);}
function publishable_index(array $data): bool {return enabled('indexing_enabled')&&!empty($data['indexable'])&&empty($data['is_sample']);}
function body_html(string $text): string {
 $html='';$paragraph=[];$inList=false;
 $inline=static function(string $s): string {
  $parts=preg_split('/(\[[^\]\n]+\]\([^\s)]+\))/', $s,-1,PREG_SPLIT_DELIM_CAPTURE);$out='';
  foreach($parts as $part){if(preg_match('/^\[([^\]]+)\]\(([^\s)]+)\)$/',$part,$m)&&((str_starts_with($m[2],'/')&&!str_starts_with($m[2],'//'))||preg_match('#^https?://#i',$m[2]))){$out.='<a href="'.e($m[2]).'" rel="noopener">'.e($m[1]).'</a>';}else{$out.=e($part);}}return $out;
 };
 $flush=static function()use(&$html,&$paragraph,$inline){if($paragraph){$html.='<p>'.$inline(implode("\n",$paragraph)).'</p>';$paragraph=[];}};
 foreach(explode("\n",str_replace("\r",'',trim($text))) as $line){if(preg_match('/^(#{2,3}) (.+)$/',$line,$m)){$flush();if($inList){$html.='</ul>';$inList=false;}$tag=strlen($m[1])===2?'h2':'h3';$html.='<'.$tag.'>'.$inline($m[2]).'</'.$tag.'>';}elseif(str_starts_with($line,'- ')){$flush();if(!$inList){$html.='<ul>';$inList=true;}$html.='<li>'.$inline(substr($line,2)).'</li>';}else{if($inList){$html.='</ul>';$inList=false;}if(trim($line)==='')$flush();else $paragraph[]=$line;}}
 $flush();if($inList)$html.='</ul>';return $html;
}
set_exception_handler(function(Throwable $e){error_log('Twin CMS '.get_class($e).' at '.basename($e->getFile()).':'.$e->getLine());if(PHP_SAPI==='cli'){fwrite(STDERR,"CMS処理に失敗しました。設定・接続・ログを確認してください。\n");exit(1);}abort_request(500,'処理を完了できませんでした。時間をおいて再度お試しください。');});
if(PHP_SAPI!=='cli'){
 $prod=option('env','production')!=='development';
 if($prod&&(!isset($_SERVER['HTTPS'])||$_SERVER['HTTPS']==='off'))abort_request(503,'本番環境はHTTPS接続で設定してください。');
 secret_key();
 header('X-Content-Type-Options: nosniff');header('Referrer-Policy: strict-origin-when-cross-origin');header('X-Frame-Options: SAMEORIGIN');
 $GLOBALS['csp_nonce']=bin2hex(random_bytes(16));header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-".$GLOBALS['csp_nonce']."'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; form-action 'self'; base-uri 'self'; frame-ancestors 'self'");
 ini_set('session.use_strict_mode','1');ini_set('session.use_only_cookies','1');session_name('twin_cms');session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$prod,'httponly'=>true,'samesite'=>'Lax']);session_start();
}
