<?php
declare(strict_types=1);
$catalog=json_decode(file_get_contents(site_root().'/data/cms-catalog.json'),true,512,JSON_THROW_ON_ERROR);
define('EQUIPMENT_GROUPS',$catalog['equipment_groups']);
$equipment=[];foreach(EQUIPMENT_GROUPS as $group)$equipment=array_merge($equipment,$group);
define('EQUIPMENT_LABELS',$equipment);define('CONTENT_TOPICS',$catalog['topics']);define('TARGET_REGIONS',$catalog['regions']);
function parse_tags(mixed $raw): array {
 if(!is_string($raw)||strlen($raw)>4000)throw new InvalidArgumentException('タグをカンマ区切りで入力してください。');
 $tags=array_values(array_unique(array_filter(array_map('trim',preg_split('/[,、\n]+/u',$raw)?:[]),fn($t)=>$t!=='')));
 if(count($tags)>24)throw new InvalidArgumentException('手動タグは24件までにしてください。');
 foreach($tags as $tag)if(text_length($tag)>40||preg_match('/[<>\x00-\x1F]/u',$tag))throw new InvalidArgumentException('タグは40文字以内の通常の文字で指定してください。');return $tags;
}
function effective_tags(array $d,string $type='column'): array {
 $tags=$d['tags']??[];$auto=$d['auto_tags']??true;
 if($auto){
  $text=implode(' ',array_map(fn($k)=>(string)($d[$k]??''),['title','body','summary','category','model','answer_summary']));
  $scored=[];foreach(CONTENT_TOPICS as $topic){$score=0;foreach($topic['aliases'] as $term){if(str_contains($text,$term))$score=max($score,1);if(str_contains((string)($d['title']??''),$term))$score=max($score,10);}if($score)$scored[]=['label'=>$topic['label'],'score'=>$score];}usort($scored,fn($a,$b)=>$b['score']<=>$a['score']);$matched=array_column($scored,'label');
  if($type==='vehicle')$matched=array_merge(['中古車','車販売'],array_filter([['kei'=>'軽自動車','commercial'=>'商用車','passenger'=>'乗用車'][$d['category']??'']??null]));
  foreach(array_unique($matched) as $term){$tags[]=$term;if($term==='車買取')$tags[]='買取';}
  if(($d['target_region']??'')==='taisho'){
   $tags=array_merge($tags,TARGET_REGIONS);
   // Two contextual service combinations, not every region × every keyword.
   foreach(array_slice(array_unique($matched),0,2) as $term){$tags[]='大正区 '.$term;$tags[]='大正 '.$term;}
  }
 }
 return array_slice(array_values(array_unique($tags)),0,24);
}
function paired_lines(string $raw,int $limit,bool $urls=false): array {
 $out=[];foreach(preg_split('/\R/u',trim($raw))?:[] as $line){if(trim($line)==='')continue;$pair=array_map('trim',explode('|',$line,2));if(count($pair)!==2||$pair[0]===''||$pair[1]==='')throw new InvalidArgumentException('FAQ・参考資料は「質問または資料名 | 回答またはURL」の形式にしてください。');
  if(text_length($pair[0])>200||text_length($pair[1])>2000)throw new InvalidArgumentException('FAQ・参考資料の1項目が長すぎます。');
  if($urls&&(!filter_var($pair[1],FILTER_VALIDATE_URL)||!preg_match('#^https?://#i',$pair[1])||parse_url($pair[1],PHP_URL_USER)||parse_url($pair[1],PHP_URL_PASS)))throw new InvalidArgumentException('参考資料には認証情報のないhttp/httpsのURLを指定してください。');$out[]=$pair;
 }
 if(count($out)>$limit)throw new InvalidArgumentException('FAQは8件、参考資料は10件までです。');return $out;
}
function validate_discovery(array $d): void {
 if(!in_array($d['target_region'],['','taisho'],true))throw new InvalidArgumentException('地域指定を確認してください。');
 foreach(['answer_summary'=>3000,'key_points'=>5000,'faq_text'=>20000,'sources_text'=>20000] as $key=>$limit)if(strlen($d[$key])>$limit)throw new InvalidArgumentException('要点・FAQ・出典の入力が長すぎます。');
 if(count(array_filter(preg_split('/\R/u',$d['key_points'])?:[],fn($s)=>trim($s)!==''))>5)throw new InvalidArgumentException('確認ポイントは5件までです。');
 paired_lines($d['faq_text'],8);paired_lines($d['sources_text'],10,true);
 if($d['reviewed_on']!==''){$date=DateTimeImmutable::createFromFormat('!Y-m-d',$d['reviewed_on']);if(!$date||$date->format('Y-m-d')!==$d['reviewed_on']||$d['reviewed_on']>date('Y-m-d'))throw new InvalidArgumentException('確認日は実際に内容を確認した、今日以前の日付を入力してください。');}
}
function topic_block(array $tags,bool $region=false,string $type='column'): string {
 if(!$tags)return '';$prefix=['vehicle'=>'stock','story'=>'story','news'=>'news','column'=>'column'][$type];$html='<section class="content-topics" aria-label="関連キーワード"><h2>このページのテーマ</h2><div>';foreach($tags as $tag)$html.='<a href="/'.$prefix.'/?tag='.rawurlencode($tag).'">'.e($tag).'</a>';
 return $html.'</div>'.($region?'<p>大正区の方向けの情報テーマです。店舗所在地・出動可能エリアを示すものではありません。</p>':'').'</section>';
}
function answer_blocks(array $d): string {
 $html='';if(!empty($d['answer_summary']))$html.='<section class="cms-answer-summary"><h2>このページの要点</h2><p>'.nl2br(e($d['answer_summary'])).'</p></section>';
 if(!empty($d['key_points'])){$html.='<section class="cms-key-points"><h2>確認したいポイント</h2><ul>';foreach(preg_split('/\R/u',$d['key_points']) as $line)if(trim($line)!=='')$html.='<li>'.e(trim($line)).'</li>';$html.='</ul></section>';}return $html;
}
function evidence_blocks(array $d): string {
 $html='';$faq=paired_lines($d['faq_text']??'',8);if($faq){$html.='<section class="cms-evidence-faq"><h2>よくある質問</h2>';foreach($faq as [$q,$a])$html.='<details open><summary>'.e($q).'</summary><p>'.nl2br(e($a)).'</p></details>';$html.='</section>';}
 $sources=paired_lines($d['sources_text']??'',10,true);if($sources){$html.='<section class="cms-sources"><h2>参考資料・出典</h2><ul>';foreach($sources as [$label,$url])$html.='<li><a href="'.e($url).'" target="_blank" rel="noopener noreferrer">'.e($label).' ↗</a></li>';$html.='</ul></section>';}
 if(!empty($d['reviewed_on']))$html.='<p class="cms-reviewed">内容確認日：<time datetime="'.e($d['reviewed_on']).'">'.e($d['reviewed_on']).'</time></p>';return $html;
}
