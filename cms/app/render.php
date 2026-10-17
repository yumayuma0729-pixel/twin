<?php
declare(strict_types=1);
require_once __DIR__.'/equipment.php';
function render_shell(string $title,string $description,string $body,string $canonicalPath,bool $index=false,?array $structured=null,?array $keywordTags=null): void {
 $source=file_get_contents(site_root().'/company/index.html');$source=preg_replace('#<script type="application/ld\+json" data-discovery>.*?</script>#s','',$source);$start=strpos($source,'<main');$end=strpos($source,'</main>',$start)+7;
 $source=substr($source,0,$start).'<main id="main" class="cms-public-main">'.$body.'</main>'.substr($source,$end);
 // Always emit page-specific metadata, rather than inheriting company-page keywords.
 if($keywordTags===null){$keywordTags=[];$metadataFile=site_root().'/'.trim($canonicalPath,'/').'/index.html';if(is_file($metadataFile)&&preg_match('/<meta name="keywords" content="([^"]*)">/',file_get_contents($metadataFile),$matches))$keywordTags=array_filter(array_map('trim',explode(',',html_entity_decode($matches[1],ENT_QUOTES,'UTF-8'))));}
 $source=preg_replace('/<meta name="keywords" content="[^"]*">/','',$source);
 if($keywordTags)$source=str_replace('</head>','<meta name="keywords" content="'.e(implode(', ',$keywordTags)).'"></head>',$source);
 $source=preg_replace_callback('#<title>.*?</title>#s',fn()=>'<title>'.e($title).' | Twin Assist</title>',$source,1);
 $source=preg_replace_callback('#<meta name="description" content="[^"]*">#',fn()=>'<meta name="description" content="'.e($description).'">',$source,1);
 $source=preg_replace('#<meta name="robots" content="[^"]*">#','<meta name="robots" content="'.($index?'index,follow':'noindex,follow').'">',$source,1);
 $source=preg_replace_callback('#<nav class="detail-breadcrumb".*?</nav>#s',fn()=>'<nav class="detail-breadcrumb" aria-label="パンくず"><a href="/">ホーム</a><span>/</span><span aria-current="page">'.e($title).'</span></nav>',$source,1);
 $extra='<link rel="canonical" href="'.e(base_url().$canonicalPath).'"><meta property="og:title" content="'.e($title).'"><meta property="og:description" content="'.e($description).'"><meta property="og:url" content="'.e(base_url().$canonicalPath).'"><link rel="stylesheet" href="/vehicle-detail.css"><link rel="stylesheet" href="/cms-public.css">';
 if($structured)$extra.='<script type="application/ld+json" nonce="'.e($GLOBALS['csp_nonce']).'">'.json_encode($structured,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).'</script>';
 $source=live_sidebar($source);$source=str_replace('</head>',$extra.'</head>',$source);$source=str_replace('</body>','<script src="/cms-public.js" defer></script></body>',$source);
 header('Cache-Control: no-store');if(!$index)header('X-Robots-Tag: noindex');echo $source;
}
function media_tag(int $id,string $class='',bool $lazy=true): string {$m=query('SELECT alt,width,height FROM media WHERE id=?',[$id])->fetch();return $m?'<img class="'.e($class).'" src="/media/'.$id.'/" width="'.(int)$m['width'].'" height="'.(int)$m['height'].'" alt="'.e($m['alt']).'"'.($lazy?' loading="lazy"':'').'>':'';}
function public_cards(array $rows): string {
 $html='<div class="cms-public-grid">';foreach($rows as $r){$d=payload($r);$html.='<article class="cms-public-card"><a href="'.e(content_url($r)).'">'.(!empty($d['media_ids'])?media_tag((int)$d['media_ids'][0]):'').'<div>'.''.'<span class="eyebrow">'.e($r['type']==='vehicle'?['kei'=>'軽自動車','commercial'=>'商用車','passenger'=>'乗用車'][$d['category']]:$d['category']).'</span><h2>'.e($d['title']).'</h2>';
  if($r['type']==='vehicle')$html.='<p>'.e($d['year']).'年 · '.number_format((int)$d['km']).'km</p><p class="cms-public-price"><small>支払総額</small><strong>'.rtrim(rtrim(number_format((int)$d['total']/10000,1,'.',''),'0'),'.').'<small>万円</small></strong></p><span class="cms-sale-status">'.e(['available'=>'販売中','negotiating'=>'商談中','sold'=>'売約済み'][$d['sale_status']]).'</span>';
  else $html.='<p>'.e($d['summary']).'</p><time>'.e(datetime_jp($r['published_at'])).'</time>';
  $html.='<span class="cms-card-more">詳しく見る ↗</span></div></a></article>';
 }return $html.'</div>';
}
function render_content(array $row,bool $preview=false): void {
 $d=payload($row,$preview);if(!$preview&&$row['type']==='vehicle')$GLOBALS['current_vehicle_id']=(int)$row['id'];$body=$preview?'<div class="cms-public-preview">管理者用の下書きプレビューです。公開中のページとは異なる場合があります。<a href="/admin/edit/?id='.(int)$row['id'].'">編集に戻る →</a></div>':'';
 $body.='<article class="cms-public-detail"><span class="eyebrow">'.e(CONTENT_LABELS[$row['type']]).'</span><h1>'.e($d['title']).'</h1>'.'';
 if($row['type']==='vehicle'){
  $body.='<div class="cms-real-vehicle"><div class="cms-real-gallery">';if($d['media_ids']){$body.='<button type="button" class="cms-real-photo" data-real-photo aria-label="選択中の写真を拡大する">'.media_tag((int)$d['media_ids'][0],'',false).'<span>拡大する ↗</span></button><div class="cms-real-thumbs">';foreach($d['media_ids'] as $i=>$id)$body.='<button type="button" data-real-thumb aria-label="画像'.($i+1).'を表示" aria-pressed="'.($i===0?'true':'false').'">'.media_tag((int)$id).'</button>';$body.='</div>';}$body.='</div><aside class="cms-real-price"><span class="cms-sale-status">'.e(['available'=>'販売中','negotiating'=>'商談中','sold'=>'売約済み'][$d['sale_status']]).'</span><p>支払総額（税込）</p><strong>'.rtrim(rtrim(number_format((int)$d['total']/10000,1,'.',''),'0'),'.').'<small>万円</small></strong><dl><div><dt>車両本体価格</dt><dd>'.number_format((int)$d['body_price']).'円</dd></div><div><dt>諸費用</dt><dd>'.number_format((int)$d['fees']).'円</dd></div></dl><p>'.nl2br(e($d['price_condition'])).'</p>'.($d['sale_status']!=='sold'?'<a class="button dark" href="/contact/?type=vehicle&amp;vehicle_id='.(int)$row['id'].'">この車について相談 ↗</a>':'<p>この車両は売約済みです。</p>').'<a class="text-link" href="/buy/#order">ほかの車種は注文販売へ →</a></aside></div><section class="cms-public-specs"><h2>基本情報</h2><dl>';
  foreach(['メーカー'=>$d['maker'],'車名'=>$d['model'],'グレード'=>$d['grade'],'年式'=>$d['year'].'年','走行距離'=>number_format((int)$d['km']).'km','カラー'=>$d['color'],'ミッション'=>$d['transmission'],'車検'=>$d['inspection'],'修復歴'=>['none'=>'なし','yes'=>'あり','unknown'=>'未確認'][$d['repair_history']]] as $label=>$value)$body.='<div><dt>'.e($label).'</dt><dd>'.e($value).'</dd></div>';
  $body.='</dl></section><section class="cms-public-equipment"><h2>装備・車両のポイント</h2>'.equipment_board($d['equipment']).'</section>';
 }else{
  $body.='<div class="cms-public-meta"><time>公開：'.e(datetime_jp($row['published_at'])).'</time>'.($d['author']?'<span>著者：'.e($d['author']).'</span>':'').'</div>';if($d['media_ids'])$body.='<figure class="cms-public-cover">'.media_tag((int)$d['media_ids'][0],'',false).'</figure>';
 }
 $body.=answer_blocks($d).'<div class="cms-article-body">'.body_html($d['body']).'</div>'.evidence_blocks($d).topic_block(effective_tags($d,$row['type']),($d['target_region']??'')==='taisho',$row['type']);
 if($row['type']!=='vehicle'&&count($d['media_ids'])>1){$body.='<div class="cms-public-inline-images">';foreach(array_slice($d['media_ids'],1) as $id)$body.=media_tag((int)$id);$body.='</div>';}
 $related=[];if($row['type']==='vehicle'&&$d['total']!==''){$candidates=query("SELECT * FROM contents WHERE type='vehicle' AND id<>? AND published IS NOT NULL AND deleted_at IS NULL ORDER BY public_updated_at DESC LIMIT 100",[$row['id']])->fetchAll();$related=array_values(array_filter($candidates,fn($c)=>abs((int)payload($c)['total']-(int)$d['total'])<=300000&&payload($c)['sale_status']!=='sold'));usort($related,fn($a,$b)=>abs((int)payload($a)['total']-(int)$d['total'])<=>abs((int)payload($b)['total']-(int)$d['total']));$related=array_slice($related,0,3);}
 elseif(!empty($d['related_ids']))foreach($d['related_ids'] as $id){$r=query('SELECT * FROM contents WHERE id=? AND published IS NOT NULL AND deleted_at IS NULL AND id<>?',[$id,$row['id']])->fetch();if($r)$related[]=$r;}
 if(!$related&&$row['type']!=='vehicle'){$tags=array_values(array_diff(effective_tags($d,$row['type']),TARGET_REGIONS));$scores=[];foreach(query("SELECT * FROM contents WHERE type<>'vehicle' AND id<>? AND published IS NOT NULL AND deleted_at IS NULL ORDER BY public_updated_at DESC LIMIT 200",[$row['id']])->fetchAll() as $candidate){$score=count(array_intersect($tags,effective_tags(payload($candidate),$candidate['type'])));if($score)$scores[]=['score'=>$score,'row'=>$candidate];}usort($scores,fn($a,$b)=>$b['score']<=>$a['score']);$related=array_column(array_slice($scores,0,3),'row');}
 if($related)$body.='<section class="cms-public-related"><h2>'.($row['type']==='vehicle'?'同じ価格帯の車':'関連記事').'</h2>'.public_cards($related).'</section>';
 $body.='<a class="text-link" href="/'.['vehicle'=>'stock','story'=>'story','news'=>'news','column'=>'column'][$row['type']].'/">一覧へ戻る →</a></article><dialog id="cms-real-photo-dialog" class="cms-real-photo-dialog"><button type="button" data-real-close aria-label="写真を閉じる">×</button><img src="" alt=""><div><button type="button" data-real-prev>← 前へ</button><span data-real-count></span><button type="button" data-real-next>次へ →</button></div></dialog>';
 $structured=null;if($row['type']!=='vehicle'&&!$preview&&$row['published_at']){$structured=['@context'=>'https://schema.org','@type'=>'Article','headline'=>$d['title'],'datePublished'=>(new DateTimeImmutable($row['published_at'],new DateTimeZone('UTC')))->format(DATE_ATOM),'dateModified'=>(new DateTimeImmutable($row['public_updated_at'],new DateTimeZone('UTC')))->format(DATE_ATOM),'mainEntityOfPage'=>base_url().content_url($row)];if($d['author'])$structured['author']=['@type'=>'Person','name'=>$d['author']];if($d['media_ids'])$structured['image']=base_url().'/media/'.(int)$d['media_ids'][0].'/';}
 $entities=[];if($structured){$structured['keywords']=effective_tags($d,$row['type']);$structured['description']=$d['description'];$structured['inLanguage']='ja';$structured['publisher']=['@type'=>'Organization','name'=>'Twin Assist'];if($d['sources_text'])$structured['citation']=array_column(paired_lines($d['sources_text'],10,true),1);$entities[]=$structured;}
 $entities[]=['@type'=>'BreadcrumbList','itemListElement'=>[['@type'=>'ListItem','position'=>1,'name'=>'ホーム','item'=>base_url().'/'],['@type'=>'ListItem','position'=>2,'name'=>$d['title'],'item'=>base_url().content_url($row)]]];
 $faq=paired_lines($d['faq_text']??'',8);if($faq)$entities[]=['@type'=>'FAQPage','mainEntity'=>array_map(fn($pair)=>['@type'=>'Question','name'=>$pair[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$pair[1]]],$faq)];
 $structured=$preview?null:['@context'=>'https://schema.org','@graph'=>$entities];
 render_shell($d['seo_title']?:$d['title'],$d['description'],$body,content_url($row),!$preview&&publishable_index($d),$structured,effective_tags($d,$row['type']));
}

function live_sidebar(string $html): string {
 if(!enabled('inquiries_enabled'))return $html;
 $_SESSION['contact_started']=$_SESSION['contact_started']??time();
 $vehicleId=(int)($GLOBALS['current_vehicle_id']??0);
 $html=str_replace('<form id="rail-inquiry-form" method="post" action="/contact/">','<form id="rail-inquiry-form" method="post" action="/contact/review/" data-server-processing="1">'.csrf_field().'<input type="hidden" name="vehicle_id" value="'.$vehicleId.'">',$html);
 $html=str_replace('<fieldset id="rail-inquiry-fields" disabled>','<fieldset id="rail-inquiry-fields">',$html);
 $html=str_replace('入力・確認までのプレビューです。<br>現在は送信・予約できません。','入力内容の確認後に送信できます。<br>来店日時は別途ご相談のうえ調整します。',$html);
 if($vehicleId){$r=query("SELECT type,published FROM contents WHERE id=? AND published IS NOT NULL AND deleted_at IS NULL",[$vehicleId])->fetch();if($r)$html=str_replace('<p id="rail-vehicle-context" hidden></p>','<p id="rail-vehicle-context">ご覧の車両：'.e(payload($r)['title']).'</p>',$html);}
 return $html;
}
