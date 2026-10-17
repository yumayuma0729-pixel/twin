<?php
declare(strict_types=1);
function route_request(): void {
 $path=rawurldecode((string)parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH));
 if(str_contains($path,"\0")||str_contains($path,'..')||str_contains($path,'\\'))abort_request(404,'ページが見つかりません。');
 if($path==='/admin')redirect('/admin/');
 if(str_starts_with($path,'/admin/')){route_admin($path);return;}
 if(preg_match('#^/media/([1-9][0-9]*)/?$#',$path,$m)){
  $id=(int)$m[1];$media=query('SELECT * FROM media WHERE id=?',[$id])->fetch();$visible=allowed('content')||(bool)query("SELECT cm.media_id FROM content_media cm JOIN contents c ON c.id=cm.content_id WHERE cm.media_id=? AND cm.state='published' AND c.published IS NOT NULL AND c.deleted_at IS NULL LIMIT 1",[$id])->fetch();
  if(!$media||!$visible)abort_request(404,'画像が見つかりません。');$file=media_root().'/'.$media['filename'];if(!is_file($file))abort_request(404,'画像が見つかりません。');header('Content-Type: '.$media['mime']);header('Content-Length: '.filesize($file));header('Cache-Control: no-store');header('X-Robots-Tag: noindex');readfile($file);return;
 }
 if(in_array($path,['/cms-admin.css','/cms-admin.js','/cms-public.css','/cms-public.js'],true)){header('Content-Type: '.(str_ends_with($path,'.css')?'text/css':'application/javascript').'; charset=UTF-8');readfile(CMS_ROOT.'/public'.$path);return;}
 if($path==='/sitemap.xml'){render_sitemap();return;}
 if($path==='/robots.txt'){header('Content-Type: text/plain; charset=UTF-8');echo enabled('indexing_enabled')?"User-agent: *\nDisallow: /admin/\nDisallow: /contact/\nSitemap: ".base_url()."/sitemap.xml\n":"User-agent: *\nDisallow: /\n";return;}
 if(str_starts_with($path,'/contact/')){route_contact($path);return;}
 if(preg_match('#^/(stock|story|column|news)/$#',$path,$m)){render_listing(['stock'=>'vehicle','story'=>'story','column'=>'column','news'=>'news'][$m[1]]);return;}
 if(preg_match('#^/(stock|story|column|news)/([a-z0-9-]+)/$#',$path,$m)){$type=['stock'=>'vehicle','story'=>'story','column'=>'column','news'=>'news'][$m[1]];$row=query('SELECT * FROM contents WHERE type=? AND slug=?',[$type,$m[2]])->fetch();if($row){if(!$row['published']||$row['deleted_at'])abort_request(404,'このページは公開されていません。');render_content($row);return;}}
 if($path==='/'){render_home();return;}
 // Only original static public assets/pages are served; private application files are never exposed.
 $root=realpath(site_root());$file=realpath(site_root().($path===''?'/':$path).(str_ends_with($path,'/')?'index.html':''));
 if(!$root||!$file||!str_starts_with($file,$root.DIRECTORY_SEPARATOR)||!is_file($file)||preg_match('#/\.[^/]+#',$path))abort_request(404,'ページが見つかりません。');
 $ext=strtolower(pathinfo($file,PATHINFO_EXTENSION));$types=['html'=>'text/html; charset=UTF-8','css'=>'text/css; charset=UTF-8','js'=>'application/javascript; charset=UTF-8','png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','webp'=>'image/webp','svg'=>'image/svg+xml','ttf'=>'font/ttf','woff2'=>'font/woff2','json'=>'application/json; charset=UTF-8'];
 if(!isset($types[$ext]))abort_request(404,'ページが見つかりません。');header('Content-Type: '.$types[$ext]);if($ext==='html')header('X-Robots-Tag: noindex');readfile($file);
}
function render_sitemap(): void {
 header('Content-Type: application/xml; charset=UTF-8');header('Cache-Control: no-cache');echo '<?xml version="1.0" encoding="UTF-8"?>' ."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
 if(enabled('indexing_enabled'))foreach(query('SELECT * FROM contents WHERE published IS NOT NULL AND deleted_at IS NULL ORDER BY id')->fetchAll() as $row){$d=payload($row);if(!publishable_index($d))continue;echo '<url><loc>'.e(base_url().content_url($row)).'</loc><lastmod>'.(new DateTimeImmutable($row['public_updated_at'],new DateTimeZone('UTC')))->format(DATE_ATOM).'</lastmod></url>';}
 echo '</urlset>';
}
function render_listing(string $type): void {
 $rows=query('SELECT * FROM contents WHERE type=? AND published IS NOT NULL AND deleted_at IS NULL ORDER BY published_at DESC,id DESC LIMIT 500',[$type])->fetchAll();$title=CONTENT_LABELS[$type].'一覧';$params=$_GET;
 if($type==='vehicle'){
  $category=(string)($params['category']??'');$keyword=trim((string)($params['keyword']??''));$price=(string)($params['price']??'');
  $rows=array_values(array_filter($rows,static function($row)use($category,$keyword,$price){$d=payload($row);if(in_array($category,['kei','commercial','passenger'],true)&&$d['category']!==$category)return false;if($keyword!==''&&stripos($d['title'].' '.$d['maker'].' '.$d['model'].' '.$d['grade'],$keyword)===false)return false;$total=(int)$d['total'];if($price==='under100'&&$total>=1000000)return false;if($price==='100to150'&&($total<1000000||$total>1500000))return false;if($price==='over150'&&$total<=1500000)return false;return true;}));
  $sort=(string)($params['sort']??'newest');if(in_array($sort,['price-asc','price-desc'],true))usort($rows,fn($a,$b)=>((int)payload($a)['total']<=>(int)payload($b)['total'])*($sort==='price-asc'?1:-1));
  if(in_array($category,['kei','commercial','passenger'],true))$title=['kei'=>'軽自動車','commercial'=>'商用車・トラック','passenger'=>'乗用車'][$category].'の在庫一覧';
 }
 $tag=is_string($_GET['tag']??'')?trim($_GET['tag']??''):'';if($tag!==''){$rows=array_values(array_filter($rows,fn($row)=>in_array($tag,effective_tags(payload($row),$row['type']),true)));$title.='｜'.$tag;}
 $body='<section class="cms-public-list section"><span class="eyebrow">'.strtoupper($type).'</span><h1>'.e($title).'</h1>';
 if($type==='vehicle')$body.='<nav class="cms-category-nav" aria-label="車種カテゴリ"><a href="/stock/">すべて</a><a href="/stock/?category=kei">軽自動車</a><a href="/stock/?category=commercial">商用車・トラック</a><a href="/stock/?category=passenger">乗用車</a></nav><details class="cms-public-refine"><summary>条件を変更する</summary><form><div>'.input_field('keyword','キーワード',$_GET['keyword']??'').select_field('category','カテゴリ',[''=>'すべて','kei'=>'軽自動車','commercial'=>'商用車・トラック','passenger'=>'乗用車'],$_GET['category']??'').select_field('price','価格',[''=>'すべて','under100'=>'100万円未満','100to150'=>'100〜150万円','over150'=>'150万円超'],$_GET['price']??'').select_field('sort','並び順',['newest'=>'新着順','price-asc'=>'安い順','price-desc'=>'高い順'],$_GET['sort']??'').'</div><button class="button dark">条件を適用 →</button></form></details>';
 $body.='<p>'.count($rows).'件の公開情報</p>'.($rows?public_cards($rows):'<div class="cms-public-empty"><h2>現在、公開中の'.e(CONTENT_LABELS[$type]).'はありません。</h2><p>準備ができ次第ご案内します。</p></div>');
 if($type==='vehicle')$body.='<section class="inventory-order"><div><span class="eyebrow">ORDER YOUR CAR</span><h2>ご希望の車種も、掲載にない一台も。</h2><p>注文販売についてもご相談いただけます。</p></div><a class="button dark" href="/buy/#order">注文販売へ ↗</a></section>';
 $body.='</section>';render_shell($title,'Twin Assistの'.CONTENT_LABELS[$type].'をご案内します。',$body,'/'.['vehicle'=>'stock','story'=>'story','news'=>'news','column'=>'column'][$type].'/',false);
}
function render_home(): void {
 $html=file_get_contents(site_root().'/index.html');
 $rows=query("SELECT * FROM contents WHERE type='vehicle' AND published IS NOT NULL AND deleted_at IS NULL ORDER BY published_at DESC,id DESC LIMIT 100")->fetchAll();$featured=array_values(array_filter($rows,fn($r)=>!empty(payload($r)['featured'])));$rows=array_slice($featured?:$rows,0,6);
 $a=strpos($html,'<div class="vehicle-grid home-vehicle-grid"');$b=strpos($html,'<a class="home-stock-more"',$a);
 if($a!==false&&$b!==false){$cards=$rows?str_replace('class="cms-public-grid"','class="cms-public-grid cms-home-stock"',public_cards($rows)):'<div class="cms-public-empty"><h3>在庫情報は準備中です。</h3><p>ご希望のお車は注文販売でもご相談いただけます。</p></div>';$html=substr($html,0,$a).$cards.substr($html,$b);}
 $html=preg_replace('#<p class="inventory-demo-notice">.*?</p>#s','',$html,1);
 foreach(['story'=>'story','column'=>'column'] as $type=>$section){$rows=query('SELECT * FROM contents WHERE type=? AND published IS NOT NULL AND deleted_at IS NULL ORDER BY published_at DESC,id DESC LIMIT 3',[$type])->fetchAll();$pattern=$section==='story'?'#<div class="customer-story-grid".*?<p class="story-disclaimer">.*?</p>#s':'#<div class="column-grid">.*?</div>\s*</section>#s';$replacement=$rows?str_replace('class="cms-public-grid"','class="cms-public-grid cms-home-articles"',public_cards($rows)):'<p class="cms-public-empty">記事は準備中です。</p>';if($section==='column')$replacement.='</section>';$html=preg_replace_callback($pattern,fn()=>$replacement,$html,1);}
 $html=str_replace('</head>','<link rel="stylesheet" href="/cms-public.css"></head>',$html);header('Cache-Control: no-store');header('X-Robots-Tag: noindex');echo live_sidebar($html);
}
function route_contact(string $path): void {
 header('Cache-Control: no-store, private');header('X-Robots-Tag: noindex');
 if(!enabled('inquiries_enabled')){if($path==='/contact/'){readfile(site_root().'/contact/index.html');return;}abort_request(503,'お問い合わせは入力・確認のみのプレビューです。実際の受付は準備中です。');}
 if($path==='/contact/thanks/'){if(empty($_SESSION['last_submitted_inquiry']))redirect('/contact/');render_shell('お問い合わせを受け付けました','お問い合わせ受付結果','<section class="section cms-contact"><h1>お問い合わせを受け付けました。</h1><p>ご来店や契約の確定ではありません。内容を確認のうえご案内します。</p><a class="button dark" href="/">トップページへ →</a></section>','/contact/thanks/');return;}
 $error='';$data=['type'=>'general','name'=>'','email'=>'','phone'=>'','message'=>'','vehicle_id'=>0,'vehicle_title'=>''];
 if($path==='/contact/submit/'){
  if($_SERVER['REQUEST_METHOD']!=='POST')abort_request(405,'確認画面から送信してください。');verify_csrf();if(!enabled('inquiries_enabled'))abort_request(503,'お問い合わせ受付は準備中です。');
  $pending=$_SESSION['pending_inquiry']??null;if(!$pending||!isset($_POST['request_key'])||!is_string($_POST['request_key'])||!hash_equals($pending['key'],$_POST['request_key'])||time()-$pending['time']>1800)abort_request(422,'入力の有効期限が切れました。最初から入力してください。');
  $existing=query('SELECT id FROM inquiries WHERE submission_key=?',[$pending['key']])->fetch();
  if(!$existing){if(!rate_allowed('inquiry-send',$_SERVER['REMOTE_ADDR']??'',5,3600))abort_request(429,'送信回数が多いため、時間をおいてください。');$d=$pending['data'];db()->beginTransaction();try{query('INSERT INTO inquiries(submission_key,type,name,email,phone,message,vehicle_id,vehicle_title,note,consent_version) VALUES(?,?,?,?,?,?,?,?,?,?)',[$pending['key'],$d['type'],$d['name'],$d['email'],$d['phone'],$d['message'],$d['vehicle_id']?:null,$d['vehicle_title'],'','2026-10-16-draft']);$id=(int)db()->lastInsertId();query('INSERT INTO notifications(inquiry_id) VALUES(?)',[$id]);db()->commit();}catch(Throwable $e){db()->rollBack();if(!($e instanceof PDOException&&$e->getCode()==='23000'))throw $e;}}
  $_SESSION['last_submitted_inquiry']=$pending['key'];unset($_SESSION['contact_edit']);redirect('/contact/thanks/');
 }
 if(!in_array($path,['/contact/','/contact/review/'],true))abort_request(404,'ページが見つかりません。');
 if($_SERVER['REQUEST_METHOD']==='POST'){
  verify_csrf();foreach(['type','name','email','phone','message'] as $key){if(!is_string($_POST[$key]??''))abort_request(422,'入力形式をご確認ください。');$data[$key]=trim((string)($_POST[$key]??''));}$data['vehicle_id']=(int)($_POST['vehicle_id']??0);
  if($data['vehicle_id']){$vehicle=query("SELECT id,type,published FROM contents WHERE id=? AND type='vehicle' AND published IS NOT NULL AND deleted_at IS NULL",[$data['vehicle_id']])->fetch();if($vehicle)$data['vehicle_title']=payload($vehicle)['title'];else{$data['vehicle_id']=0;}}
  if(!in_array($data['type'],array_keys(INQUIRY_LABELS),true)||$data['name']===''||text_length($data['name'])>100||!filter_var($data['email'],FILTER_VALIDATE_EMAIL)||strlen($data['email'])>254||strlen($data['phone'])>30||$data['message']===''||strlen($data['message'])>9000||!isset($_POST['consent']))$error='お名前・メール・ご相談内容と、個人情報の取り扱いの確認を入力してください。';
  elseif(!empty($_POST['website'])||time()-(int)($_SESSION['contact_started']??time())<1)$error='入力内容をご確認ください。';
  else{
   $_SESSION['contact_edit']=$data;$key=bin2hex(random_bytes(32));$_SESSION['pending_inquiry']=['key'=>$key,'data'=>$data,'time'=>time()];
   $body='<section class="section cms-contact"><span class="eyebrow">REVIEW YOUR REQUEST</span><h1>入力内容の確認</h1><p>まだ送信されていません。来店・契約の確定ではありません。</p><dl class="cms-status-list">';foreach(['相談内容'=>INQUIRY_LABELS[$data['type']],'対象車両'=>$data['vehicle_title'],'お名前'=>$data['name'],'メール'=>$data['email'],'電話'=>$data['phone'],'ご相談内容'=>$data['message']] as $label=>$value)$body.='<div><dt>'.e($label).'</dt><dd>'.nl2br(e($value)).'</dd></div>';
   $body.='</dl><a class="button light" href="/contact/?edit=1">← 入力を修正する</a>';
   if(enabled('inquiries_enabled'))$body.='<form method="post" action="/contact/submit/">'.csrf_field().'<input type="hidden" name="request_key" value="'.e($key).'"><button class="button dark">この内容で送信する →</button></form>';
   else $body.='<p class="cms-public-preview">現在は確認までのプレビューです。送信・予約はできません。</p><button class="button dark" disabled>送信機能は準備中です</button>';
   $body.='</section>';render_shell('お問い合わせ内容の確認','お問い合わせの確認画面です。',$body,'/contact/');return;
  }
 }elseif(isset($_GET['edit'])&&isset($_SESSION['contact_edit']))$data=$_SESSION['contact_edit'];
 else{$type=(string)($_GET['type']??'general');if(isset(INQUIRY_LABELS[$type]))$data['type']=$type;$id=(int)($_GET['vehicle_id']??0);if($id){$vehicle=query("SELECT * FROM contents WHERE id=? AND type='vehicle' AND published IS NOT NULL AND deleted_at IS NULL",[$id])->fetch();if($vehicle){$data['vehicle_id']=$id;$data['vehicle_title']=payload($vehicle)['title'];}}}
 $_SESSION['contact_started']=time();
 $body='<section class="section cms-contact"><span class="eyebrow">CONTACT</span><h1>ご予約・お問い合わせ</h1><p>'.(enabled('inquiries_enabled')?'内容を確認してから送信できます。来店日時は別途ご相談のうえ調整します。':'制作プレビュー：入力・確認までです。送信・予約はできません。').'</p>'.($error?'<div class="cms-error" role="alert">'.e($error).'</div>':'').'<form method="post" action="/contact/review/">'.csrf_field().select_field('type','ご相談の内容',INQUIRY_LABELS,$data['type']).($data['vehicle_title']?'<p>対象車両：'.e($data['vehicle_title']).'</p>':'').'<input type="hidden" name="vehicle_id" value="'.(int)$data['vehicle_id'].'">'.input_field('name','お名前',$data['name'],'text',true).input_field('email','メールアドレス',$data['email'],'email',true).input_field('phone','電話番号（任意）',$data['phone'],'tel').text_field('message','ご相談内容',$data['message'],'来店希望の場合は、ご希望日時もお書きください。').'<label class="cms-honeypot" aria-hidden="true">この欄は空欄のままにしてください<input name="website" tabindex="-1" autocomplete="off"></label><label class="cms-check"><input type="checkbox" name="consent" required><span><a href="/privacy/" target="_blank" rel="noopener">プライバシーポリシー</a>を確認しました</span></label><button class="button dark">入力内容を確認する →</button></form></section>';
 render_shell('ご予約・お問い合わせ','車両・注文販売・買取・車検・整備等についてご相談ください。',$body,'/contact/');
}
