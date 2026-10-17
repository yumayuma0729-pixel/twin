<?php
declare(strict_types=1);
const CONTENT_LABELS=['vehicle'=>'在庫車','story'=>'カーストーリー','news'=>'お知らせ','column'=>'コラム'];
require_once __DIR__.'/discovery.php';
const INQUIRY_LABELS=['general'=>'一般相談','vehicle'=>'車両のお問い合わせ','visit'=>'来店相談','order'=>'注文販売','appraisal'=>'買取・下取り','inspection'=>'車検','maintenance'=>'整備・修理','road'=>'ロードサービス'];
function default_content(string $type): array {return ['title'=>'','slug'=>'','body'=>'','summary'=>'','seo_title'=>'','description'=>'','category'=>'','author'=>'','related_ids'=>[],'media_ids'=>[],'indexable'=>false,'is_sample'=>false,'consent_checked'=>false,'maker'=>'','model'=>'','grade'=>'','year'=>'','km'=>'','body_price'=>'','fees'=>'','total'=>'','price_condition'=>'','inspection'=>'','repair_history'=>'unknown','transmission'=>'AT','color'=>'','sale_status'=>'available','featured'=>false,'tags'=>[],'auto_tags'=>true,'target_region'=>in_array($type,['column','vehicle'],true)?'taisho':'','answer_summary'=>'','key_points'=>'','faq_text'=>'','sources_text'=>'','reviewed_on'=>'','equipment'=>array_fill_keys(array_keys(EQUIPMENT_LABELS),null)];}
function posted_content(string $type): array {
 $d=default_content($type);
 foreach(['title','slug','body','summary','seo_title','description','category','author','maker','model','grade','year','km','body_price','fees','total','price_condition','inspection','repair_history','transmission','color','sale_status','target_region','answer_summary','key_points','faq_text','sources_text','reviewed_on'] as $key){$raw=$_POST[$key]??'';if(!is_string($raw))throw new InvalidArgumentException('入力形式を確認してください。');$d[$key]=trim($raw);}
 foreach(['indexable','is_sample','consent_checked','featured'] as $key)$d[$key]=isset($_POST[$key]);
 foreach(['media_ids','related_ids'] as $key){$raw=$_POST[$key]??'';if(!is_string($raw)||strlen($raw)>1000)throw new InvalidArgumentException('画像・関連記事の指定が長すぎます。');$parts=array_filter(array_map('trim',explode(',',$raw)),fn($v)=>$v!=='');if(count($parts)>20||array_filter($parts,fn($v)=>!ctype_digit($v)||(int)$v<1))throw new InvalidArgumentException('画像・関連記事IDは正の数値を20件以内で指定してください。');$d[$key]=array_values(array_unique(array_map('intval',$parts)));}
 $d['tags']=parse_tags($_POST['tags']??'');$d['auto_tags']=isset($_POST['auto_tags']);
 $equipment=$_POST['equipment']??[];if(!is_array($equipment))throw new InvalidArgumentException('装備の入力形式を確認してください。');
 foreach(EQUIPMENT_LABELS as $key=>$label){$value=$equipment[$key]??'';if(!is_string($value)||!in_array($value,['','1','0'],true))throw new InvalidArgumentException('装備は未確認・あり・なしから選択してください。');$d['equipment'][$key]=$value===''?null:$value==='1';}
 validate_discovery($d);
 return $d;
}
function validate_content(array $d,string $type,bool $publish,?array $existing): void {
 if($d['title']===''||text_length($d['title'])>200)throw new InvalidArgumentException('タイトルを200文字程度以内で入力してください。');
 if(!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/',$d['slug'])||strlen($d['slug'])>160||str_starts_with($d['slug'],'demo-'))throw new InvalidArgumentException('URLは半角英小文字・数字・ハイフンで入力してください。demo-で始まるURLは既存サンプル用のため使えません。');
 if($existing&&$existing['published_at']&&$d['slug']!==$existing['slug'])throw new InvalidArgumentException('公開履歴のある記事・在庫のURLは変更できません。');
 foreach(['summary'=>2000,'seo_title'=>600,'description'=>1500,'body'=>100000,'category'=>100,'author'=>150,'maker'=>100,'model'=>200,'grade'=>200,'price_condition'=>2000,'inspection'=>200,'color'=>100,'transmission'=>100] as $key=>$max)if(strlen($d[$key])>$max)throw new InvalidArgumentException('「'.$key.'」の入力が長すぎます。');
 foreach($d['media_ids'] as $id){if(!query('SELECT id FROM media WHERE id=?',[$id])->fetch())throw new InvalidArgumentException('存在しない画像が選択されています。');}
 foreach($d['related_ids'] as $id){if(!query("SELECT id FROM contents WHERE id=? AND type<>'vehicle' AND deleted_at IS NULL",[$id])->fetch()||($existing&&(int)$existing['id']===$id))throw new InvalidArgumentException('関連記事IDを確認してください。');}
 if($type==='vehicle'){
  if(!in_array($d['category'],['kei','commercial','passenger'],true))throw new InvalidArgumentException('車両カテゴリを選択してください。');
  if(!in_array($d['sale_status'],['available','negotiating','sold'],true)||!in_array($d['repair_history'],['none','yes','unknown'],true))throw new InvalidArgumentException('販売状態・修復歴を確認してください。');
  foreach(['year','km','body_price','fees','total'] as $key)if($d[$key]!==''&&(!ctype_digit($d[$key])||strlen($d[$key])>10))throw new InvalidArgumentException('年式・距離・価格は0以上の整数で入力してください。');
  if($d['year']!==''&&((int)$d['year']<1950||(int)$d['year']>(int)date('Y')+1))throw new InvalidArgumentException('年式を確認してください。');
  foreach(['km','body_price','fees','total'] as $key)if($d[$key]!==''&&(int)$d[$key]>1000000000)throw new InvalidArgumentException('距離・価格が設定可能な範囲を超えています。');
  if($publish){foreach(['maker','model','year','km','body_price','fees','total','price_condition','inspection'] as $key)if($d[$key]==='')throw new InvalidArgumentException('公開にはメーカー・車名・年式・走行距離・各価格・総額の適用条件・車検情報が必要です。');if((int)$d['total']<1||(int)$d['total']!==(int)$d['body_price']+(int)$d['fees'])throw new InvalidArgumentException('支払総額は車両本体価格＋諸費用と一致させてください。');if($d['repair_history']==='unknown')throw new InvalidArgumentException('公開前に修復歴の有無を確認してください。');}
 }
 if($publish){if($type!=='news'&&!$d['media_ids'])throw new InvalidArgumentException('公開には少なくとも1枚の画像が必要です。');if(!$d['consent_checked'])throw new InvalidArgumentException('事実・掲載権利・許諾の確認をチェックしてください。');if(strlen($d['body'])<30)throw new InvalidArgumentException('公開前に本文・説明を入力してください。');if(!$d['description'])throw new InvalidArgumentException('公開には検索結果用の説明文が必要です。');}
}
function save_content(string $type,?array $existing,string $action): int {
 $d=posted_content($type);$publish=$action==='publish';validate_content($d,$type,$publish,$existing);
 $pdo=db();$pdo->beginTransaction();
 try{
  if($existing){$locked=query('SELECT * FROM contents WHERE id=? FOR UPDATE',[$existing['id']])->fetch();if(!$locked||$locked['deleted_at']||(int)$locked['version']!==(int)($_POST['version']??0))throw new InvalidArgumentException('他の操作で更新されています。再読み込みして最新内容を確認してください。');$id=(int)$existing['id'];query('UPDATE contents SET slug=?,title=?,draft=?,updated_by=?,updated_at=UTC_TIMESTAMP(),version=version+1 WHERE id=?',[$d['slug'],$d['title'],json_data($d),current_user()['id'],$id]);
  }else{query('INSERT INTO contents(type,slug,title,draft,updated_by) VALUES(?,?,?,?,?)',[$type,$d['slug'],$d['title'],json_data($d),current_user()['id']]);$id=(int)$pdo->lastInsertId();}
  replace_media_refs($id,$d['media_ids'],'draft');
  if($publish){query('UPDATE contents SET published=?,published_at=COALESCE(published_at,UTC_TIMESTAMP()),public_updated_at=UTC_TIMESTAMP() WHERE id=?',[json_data($d),$id]);replace_media_refs($id,$d['media_ids'],'published');}
  audit($publish?'publish':'save_draft',$type,$id);$pdo->commit();return $id;
 }catch(Throwable $e){$pdo->rollBack();if($e instanceof PDOException&&$e->getCode()==='23000')throw new InvalidArgumentException('同じ種類でそのURLは既に使われています。別のURLにしてください。');throw $e;}
}
function replace_media_refs(int $id,array $images,string $state): void {query('DELETE FROM content_media WHERE content_id=? AND state=?',[$id,$state]);foreach($images as $image)query('INSERT INTO content_media(content_id,media_id,state) VALUES(?,?,?)',[$id,$image,$state]);}
function content_action(array $row,string $action): void {
 db()->beginTransaction();try{
  $locked=query('SELECT * FROM contents WHERE id=? FOR UPDATE',[$row['id']])->fetch();if((int)$locked['version']!==(int)($_POST['version']??0))throw new InvalidArgumentException('他の操作で更新されています。再読み込みしてください。');
  if($action==='unpublish'){query('UPDATE contents SET published=NULL,version=version+1,updated_at=UTC_TIMESTAMP(),updated_by=? WHERE id=?',[current_user()['id'],$row['id']]);query("DELETE FROM content_media WHERE content_id=? AND state='published'",[$row['id']]);}
  elseif($action==='trash'){if($locked['published'])throw new InvalidArgumentException('削除前に公開停止してください。');query('UPDATE contents SET deleted_at=UTC_TIMESTAMP(),version=version+1 WHERE id=?',[$row['id']]);}
  elseif($action==='restore'){query('UPDATE contents SET deleted_at=NULL,version=version+1 WHERE id=?',[$row['id']]);}
  else throw new InvalidArgumentException('操作を確認してください。');
  audit($action,$row['type'],(int)$row['id']);db()->commit();
 }catch(Throwable $e){db()->rollBack();throw $e;}
}
function upload_media(): int {
 if(!isset($_FILES['photo'])||is_array($_FILES['photo']['error'])||$_FILES['photo']['error']!==UPLOAD_ERR_OK)throw new InvalidArgumentException('画像を選択してください。アップロード上限も確認してください。');
 $file=$_FILES['photo'];if($file['size']>8*1024*1024||$file['size']<1||!is_uploaded_file($file['tmp_name']))throw new InvalidArgumentException('画像は8MB以下にしてください。');
 $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);$ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;$size=@getimagesize($file['tmp_name']);
 if(!$ext||!$size||$size['mime']!==$mime||$size[0]>6000||$size[1]>6000||$size[0]*$size[1]>20000000)throw new InvalidArgumentException('JPEG・PNG・WebPの画像（各辺6000px以下、2000万画素以下）を選択してください。SVG等は登録できません。');
 $alt=trim((string)($_POST['alt']??''));if($alt===''||text_length($alt)>250)throw new InvalidArgumentException('写真の説明を250文字程度以内で入力してください。');
 $name=bin2hex(random_bytes(24)).'.'.$ext;$root=media_root();if(!is_dir($root)&&!mkdir($root,0700,true))throw new RuntimeException('Media directory unavailable');$target=$root.'/'.$name;
 if(!move_uploaded_file($file['tmp_name'],$target))throw new RuntimeException('Media write failed');chmod($target,0600);
 try{query('INSERT INTO media(filename,mime,width,height,bytes,alt,created_by) VALUES(?,?,?,?,?,?,?)',[$name,$mime,$size[0],$size[1],$file['size'],$alt,current_user()['id']]);$id=(int)db()->lastInsertId();audit('upload','media',$id);return $id;}catch(Throwable $e){unlink($target);throw $e;}
}
