#!/usr/bin/env python3
"""Render contextual visible topics; no meta-keyword stuffing or fabricated addresses."""
from pathlib import Path
import argparse
import re,json,html
from urllib.parse import quote
args=argparse.ArgumentParser();args.add_argument('--base-url',default='https://twin.yumayuma-0729.workers.dev');options=args.parse_args();
root=Path(__file__).resolve().parents[1]/'public'
map_topics={
'/':['中古車','車販売','車検','整備','車買取','ロードサービス'],
'/stock/':['中古車','車販売','軽自動車','商用車','乗用車'],
'/buy/':['車販売','中古車','注文販売'],'/sell/':['車買取','買取','下取り'],
'/inspection/':['車検','点検'],'/maintenance/':['整備','修理','点検','タイヤ'],
'/road-assistance/':['ロードサービス','レッカー'],
'/business/':['車販売','車買取','車検','整備','ロードサービス'],
'/strengths/':['車販売','車買取','車検','整備','ロードサービス'],
'/story/':['中古車','車選び','カーライフ'],
'/company/':['自動車販売','車検','整備'],'/contact/':['車の相談','注文販売','車検','整備'],
'/column/':['中古車','車検','整備','車買取'],
'/column/used-car-total-price/':['中古車','支払総額','車販売'],
'/column/inspection-and-maintenance/':['車検','整備','点検'],
'/column/selling-car-checklist/':['車買取','買取','査定','下取り'],
}
for file in root.rglob('*.html'):
 path='/'+str(file.relative_to(root)).replace('index.html','')
 if path.startswith('/stock/demo-'):tags=['中古車','車販売', '軽自動車' if 'kei' in path else '商用車' if 'commercial' in path else '乗用車']
 else:tags=map_topics.get(path)
 if not tags:continue
 s=file.read_text();s=re.sub(r'<!-- contextual-topics:start -->.*?<!-- contextual-topics:end -->','',s,flags=re.S)
 base=list(dict.fromkeys(tags));tags=base+['大正','大正区','大阪市大正区']+['大正区 '+t for t in base[:2]]+['大正 '+t for t in base[:2]]
 block='<!-- contextual-topics:start --><section class="content-topics static-content-topics" aria-label="関連キーワード"><h2>このページのテーマ</h2><div>'+''.join('<span>'+html.escape(t)+'</span>' for t in tags)+'</div><p>大正区の方向けの情報テーマです。店舗所在地・出動可能エリアを示すものではありません。</p></section><!-- contextual-topics:end -->'
 s=s.replace('</main>',block+'</main>',1)
 # Breadcrumb and editorial entities use only information visible in the actual page.
 s=re.sub(r'<script type="application/ld\+json" data-discovery>.*?</script>','',s,flags=re.S)
 title=re.search(r'<h1[^>]*>(.*?)</h1>',s,re.S)
 heading=html.unescape(re.sub('<[^>]+>','',title.group(1))) if title else 'Twin Assist'
 base_url=options.base_url.rstrip('/')
 graph=[{'@type':'BreadcrumbList','itemListElement':[{'@type':'ListItem','position':1,'name':'ホーム','item':base_url+'/'},{'@type':'ListItem','position':2,'name':heading,'item':base_url+path}]}] if path!='/' else []
 if path.startswith('/column/') and path!='/column/':
  desc=re.search(r'<meta name="description" content="([^"]*)"',s)
  graph.insert(0,{'@type':'Article','headline':heading,'inLanguage':'ja','keywords':tags,'mainEntityOfPage':base_url+path,'description':html.unescape(desc.group(1)) if desc else '', 'publisher':{'@type':'Organization','name':'Twin Assist'}})
 if path=='/':
  faqs=[]
  for summary,answer in re.findall(r'<details[^>]*>\s*<summary>(.*?)</summary>\s*<div class="faq-answer">(.*?)</div>\s*</details>',s,re.S):
   summary=re.sub(r'<span[^>]*aria-hidden="true"[^>]*>.*?</span>','',summary,flags=re.S)
   para=re.search(r'<p>(.*?)</p>',answer,re.S)
   if not para:continue
   clean=lambda text:html.unescape(re.sub(r'<[^>]+>',' ',text)).strip()
   faqs.append({'@type':'Question','name':clean(summary),'acceptedAnswer':{'@type':'Answer','text':clean(para.group(1))}})
  if faqs:graph.append({'@type':'FAQPage','mainEntity':faqs})
 if graph:s=s.replace('</head>','<script type="application/ld+json" data-discovery>'+json.dumps({'@context':'https://schema.org','@graph':graph},ensure_ascii=False).replace('<','\\u003c')+'</script></head>')
 file.write_text(s)
