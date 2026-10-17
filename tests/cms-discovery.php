<?php
declare(strict_types=1);
$bootstrap=__DIR__.'/../cms/app/bootstrap.php';require is_file($bootstrap)?$bootstrap:'/srv/twin/app/bootstrap.php';
require CMS_ROOT.'/app/content.php';
require CMS_ROOT.'/app/equipment.php';
function check(bool $value,string $label): void {if(!$value)throw new RuntimeException($label);}
function rejects(callable $fn,string $label): void {try{$fn();}catch(InvalidArgumentException){return;}throw new RuntimeException($label);}
check(count(EQUIPMENT_LABELS)===65,'equipment count');
$d=default_content('column');$d['title']='車検と整備の費用を確認';$d['body']='車検の法定費用とメンテナンスの見積りを確認します。';$tags=effective_tags($d,'column');
check(in_array('大正区 車検',$tags,true),'contextual region/service tag');check(in_array('大正区',$tags,true),'region tag');check(!in_array('レッカー',$tags,true),'no irrelevant automatic tags');check(count($tags)<=24,'tag limit');
$d['auto_tags']=false;$d['tags']=['任意のタグ'];check(effective_tags($d,'column')===['任意のタグ'],'manual opt out');
check(parse_tags('車検, 車検、整備')===['車検','整備'],'dedup');rejects(fn()=>parse_tags('<script>'),'tag injection');rejects(fn()=>parse_tags(implode(',',array_map(fn($i)=>'tag'.$i,range(1,25)))),'tag quantity');
$d=default_content('column');$d['faq_text']='費用はどう確認する？ | 本文の見積り条件を確認します。';$d['sources_text']='公式資料 | https://example.com/guide';$d['reviewed_on']=date('Y-m-d');validate_discovery($d);$html=evidence_blocks($d);check(str_contains($html,'費用はどう確認する？')&&str_contains($html,'内容確認日'),'visible evidence');
rejects(fn()=>paired_lines('資料 | javascript:alert(1)',10,true),'unsafe source');rejects(fn()=>paired_lines('資料 | https://user:password@example.com',10,true),'credential source');
$d['reviewed_on']='2099-01-01';rejects(fn()=>validate_discovery($d),'future reviewed date');
$_POST=['title'=>'装備のテスト','slug'=>'equipment-test','category'=>'kei','equipment'=>['camera'=>'1','navigation'=>'0']];$vehicle=posted_content('vehicle');check($vehicle['equipment']['camera']===true&&$vehicle['equipment']['navigation']===false&&$vehicle['equipment']['led']===null,'tri state input');
$board=equipment_board($vehicle['equipment']);check(substr_count($board,'<li ')===2,'unknown equipment omitted');check(str_contains($board,'is-equipped')&&str_contains($board,'is-absent'),'present and absent');
$old=payload(['type'=>'vehicle','published'=>json_data(['title'=>'旧データ','equipment'=>['camera'=>true]])]);check($old['equipment']['camera']===true&&!isset($old['equipment']['led']),'old data preserved');check($old['target_region']==='','old publication region unchanged');
echo "PASS: 65 grouped equipment, tri-state/legacy compatibility, contextual bounded tags, opt-out, input/source/date validation and visible evidence.\n";
