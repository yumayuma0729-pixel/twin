<?php
declare(strict_types=1);
function equipment_board(array $equipment): string {
 $icons=[
  'navigation'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="7" y="8" width="34" height="25" rx="3"/><path d="M17 41h14M24 33v8M16 25l8-12 8 12-8-4z"/></svg>',
  'camera'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="13" width="25" height="23" rx="3"/><circle cx="18" cy="24" r="6"/><path d="m30 20 12-7v23l-12-7M11 8h12"/></svg>',
  'smart_key'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="8" y="7" width="19" height="32" rx="6" transform="rotate(-15 18 23)"/><circle cx="18" cy="17" r="3"/><path d="M16 29h5M33 14q9 10 0 20M32 20q4 4 0 8"/></svg>',
  'bluetooth'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 5v38l13-11L12 13m0 22 23-19L22 5"/></svg>',
  'etc'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="6" y="20" width="36" height="18" rx="3"/><path d="M12 29h18M35 28v3M15 14h18M19 8h10"/></svg>',
  'led'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M26 12c-13 0-20 6-20 12s7 12 20 12V12zM32 12l9-4M32 20h11M32 28h11M32 36l9 4"/></svg>',
  'safety'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m10 25 5-12h18l5 12v13H10V25zM10 25h28M16 32h2M30 32h2M14 38v4M34 38v4M24 3v5M4 14l4 3M44 14l-4 3"/></svg>',
  'power_door'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 10h30v28H8zM23 10v28M13 15h6v9h-6zM27 15h7v9h-7zM29 30h4M3 42h14m-4-4 4 4-4 4M45 5H31m4-4-4 4 4 4"/></svg>',
  'aircon'=>'<svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M24 5v38M8 14l32 20M8 34l32-20M19 9l5 5 5-5M19 39l5-5 5 5M9 20l7-2-1-7M39 28l-7 2 1 7M9 28l7 2-1 7M39 20l-7-2 1-7"/></svg>',
 ];
 $colors=['navigation'=>'#438641','camera'=>'#268477','smart_key'=>'#9b782c','bluetooth'=>'#3777b0','etc'=>'#7863a5','led'=>'#bd703b','safety'=>'#ac6265','power_door'=>'#3d8589','aircon'=>'#5686a3'];
 $html='<div class="equipment-board"><div class="equipment-board-heading"><span>EQUIPMENT</span><p class="equipment-legend"><span>カラー：装備あり</span><span>白黒：装備なし</span></p></div><ul class="equipment-grid" aria-label="装備一覧">';
 foreach(EQUIPMENT_LABELS as $key=>$label){$on=!empty($equipment[$key]);$html.='<li class="equipment-tile '.($on?'is-equipped':'is-absent').'" style="--equipment-color:'.$colors[$key].'">'.$icons[$key].'<strong>'.e($label).'</strong><span class="equipment-status">'.($on?'装備あり':'装備なし').'</span></li>';}
 return $html.'</ul></div>';
}
