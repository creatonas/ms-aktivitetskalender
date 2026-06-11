<?php
/**
 * Plugin Name: Aktivitetskalender
 * Description: v4.7 - Full clean (stabil + styling + ajax + nav fix)
 * Version: 4.7
 * Author: Creato Design AS
 */

if (!defined('ABSPATH')) exit;

/* CPT */
add_action('init', function() {
    register_post_type('aktiviteter', [
        'labels' => ['name'=>'Aktiviteter','singular_name'=>'Aktivitet'],
        'public'=>true,
        'has_archive'=>true,
        'rewrite'=>['slug'=>'aktiviteter'],
        'menu_icon'=>'dashicons-calendar-alt',
        'supports'=>['title']
    ]);
});

/* ACF */
add_action('acf/init', function() {
    if (!function_exists('acf_add_local_field_group')) return;

    acf_add_local_field_group([
        'key'=>'group_ms_activities',
        'title'=>'Aktivitetsdata',
        'fields'=>[
            ['key'=>'f1','label'=>'Dato fra','name'=>'date_from','type'=>'date_picker','return_format'=>'Y-m-d'],
            ['key'=>'f2','label'=>'Dato til','name'=>'date_to','type'=>'date_picker','return_format'=>'Y-m-d'],
            ['key'=>'f3','label'=>'Status','name'=>'status','type'=>'select',
                'choices'=>['ledig'=>'Ledig','fullt'=>'Fullt','lukket'=>'Lukket'],
                'default_value'=>'ledig'
            ],
            ['key'=>'f4','label'=>'Link','name'=>'link','type'=>'url']
        ],
        'location'=>[[['param'=>'post_type','operator'=>'==','value'=>'aktiviteter']]]
    ]);
});

/* STYLES */
add_action('wp_head', function(){
echo '<style>

.ms-day[onclick]:hover {
    background: #f3f4f6;
}

/* TABLE */
.ms-table th,.ms-table td{ text-align:left !important; }
.ms-table thead{background:#181e36;color:#fff;}
.ms-table tbody tr:hover{background:rgba(24,30,54,0.1);}

/* STATUS */
.ms-status{display:inline-flex;gap:6px;align-items:center;}
.ms-dot{width:8px;height:8px;border-radius:50%;display:inline-block;}
.ms-dot.ledig{background:#1a7f37;}
.ms-dot.fullt{background:#c62828;}
.ms-dot.lukket{background:#b78103;}

/* LINK */
.ms-link-icon{font-size:12px;margin-left:4px;opacity:0.7;}

/* CALENDAR */
.ms-calendar{display:grid;grid-template-columns:repeat(7,1fr);gap:10px;}
.ms-calendar-head{display:grid;grid-template-columns:repeat(7,1fr);gap:10px;font-weight:600;margin-top:10px;}
.ms-day{border:1px solid #e5e7eb;min-height:110px;padding:10px;background:#fff;border-radius:6px;}
.ms-day.today{border:2px solid #a38b6b;}

/* NAV GRID */
.ms-calendar-nav {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    margin: 20px 0 10px;
}
.ms-nav-left { text-align:left; }
.ms-nav-center { text-align:center; font-weight:600; }
.ms-nav-right { text-align:right; }

/* BUTTON */
.grve-btn.grve-bg-primary-2{background:#181e36 !important;border-color:#181e36 !important;}
.grve-btn.grve-bg-primary-2:hover{background:#a18c6f !important;border-color:#a18c6f !important;}
.ms-active{background:#a18c6f !important;border-color:#a18c6f !important;}

/* MOBILE */
@media(max-width:768px){
.ms-table thead{display:none;}
.ms-table tr{display:block;margin-bottom:12px;border:1px solid #eee;padding:10px;border-radius:6px;}
.ms-table td{display:block;}
.ms-table td:before{content:attr(data-label);font-weight:bold;display:block;margin-bottom:3px;}
}

</style>';
});

/* HELPERS */
function ms_status($s){ return $s=='booket'?'fullt':$s; }
function ms_date($from,$to){
$f=strtotime($from);
$t=$to?strtotime($to):null;
if($t && date('Y-m',$f)==date('Y-m',$t)){
return date_i18n('j',$f).'-'.date_i18n('j F',$t);
}
return date_i18n('j F',$f);
}

/* AJAX */
add_action('wp_ajax_ms_load_calendar','ms_load_calendar');
add_action('wp_ajax_nopriv_ms_load_calendar','ms_load_calendar');
function ms_load_calendar(){
echo ms_render_calendar(intval($_POST['m']),intval($_POST['y']));
wp_die();
}

/* CALENDAR RENDER */
function ms_render_calendar($m,$y){

$q=new WP_Query(['post_type'=>'aktiviteter','posts_per_page'=>-1]);
$events=[];

while($q->have_posts()){
$q->the_post();
$from=get_field('date_from');
$to=get_field('date_to');
$status=ms_status(get_field('status'));
$title=get_the_title();
$link=get_field('link');

$start=strtotime($from);
$end=$to?strtotime($to):$start;

for($t=$start;$t<=$end;$t+=86400){
$key=date('Y-m-d',$t);
$events[$key][]=['title'=>$title,'status'=>$status,'link'=>$link];
}
}

$first=mktime(0,0,0,$m,1,$y);
$days=date('t',$first);
$start=date('N',$first);

ob_start();

echo '<div class="ms-calendar-nav">
<div class="ms-nav-left">
<button onclick="msPrevMonth()" class="grve-btn grve-round grve-bg-primary-2 grve-text-white grve-btn-medium">â†</button>
</div>
<div class="ms-nav-center">'.date_i18n('F Y',$first).'</div>
<div class="ms-nav-right">
<button onclick="msNextMonth()" class="grve-btn grve-round grve-bg-primary-2 grve-text-white grve-btn-medium">â†’</button>
</div>
</div>';

echo '<div class="ms-calendar-head">
<span>Man</span><span>Tir</span><span>Ons</span><span>Tor</span><span>Fre</span><span>LÃ¸r</span><span>SÃ¸n</span>
</div>';

echo '<div class="ms-calendar">';
for($i=1;$i<$start;$i++){echo '<div></div>';}

for($d=1;$d<=$days;$d++){
$date=$y.'-'.str_pad($m,2,'0',STR_PAD_LEFT).'-'.str_pad($d,2,'0',STR_PAD_LEFT);
$class='ms-day';
if($date==date('Y-m-d')) $class.=' today';

$link_for_day = '';

if(isset($events[$date])){
    foreach($events[$date] as $e){
        if(!empty($e['link'])){
            $link_for_day = $e['link'];
            break;
        }
    }
}

if($link_for_day){
    echo '<div class="'.$class.'" onclick="window.location.href=\''.esc_url($link_for_day).'\'" style="cursor:pointer;">';
} else {
    echo '<div class="'.$class.'">';
}

echo '<strong>'.$d.'</strong>';
    
if(isset($events[$date])){
foreach($events[$date] as $e){
$label=esc_html($e['title']);
if($e['link']){
$label='<a href="'.esc_url($e['link']).'">'.$label.' <span class="ms-link-icon">ðŸ”—</span></a>';
}
echo '<span class="ms-status '.$e['status'].'"><span class="ms-dot '.$e['status'].'"></span>'.$label.'</span>';
}
}

echo '</div>';
}

echo '</div>';
wp_reset_postdata();
return ob_get_clean();
}

/* LIST */
add_shortcode('ms_activities', function(){

$q=new WP_Query([
'post_type'=>'aktiviteter',
'posts_per_page'=>-1,
'meta_key'=>'date_from',
'orderby'=>'meta_value',
'order'=>'ASC'
]);

ob_start();

echo '<table class="ms-table">';
echo '<thead><tr><th>'.date('Y').'</th><th>Aktivitet</th><th>Status</th></tr></thead><tbody>';

while($q->have_posts()){
$q->the_post();
$from=get_field('date_from');
$to=get_field('date_to');
$status=ms_status(get_field('status'));
$link=get_field('link');

$title=get_the_title();
if($link){
$title='<a href="'.esc_url($link).'">'.$title.' <span class="ms-link-icon">ðŸ”—</span></a>';
}

echo '<tr>';
echo '<td data-label="Dato">ðŸ“… '.ms_date($from,$to).'</td>';
echo '<td data-label="Aktivitet">'.$title.'</td>';
echo '<td data-label="Status"><span class="ms-status '.$status.'"><span class="ms-dot '.$status.'"></span>'.$status.'</span></td>';
echo '</tr>';
}

echo '</tbody></table>';

wp_reset_postdata();
return ob_get_clean();
});

/* CALENDAR SHORTCODE */
add_shortcode('ms_calendar', function(){
$m=date('n');$y=date('Y');

ob_start();
echo '<div id="ms-calendar-wrap">'.ms_render_calendar($m,$y).'</div>';

echo '<script>
let msMonth='.$m.';
let msYear='.$y.';

function msLoad(){
fetch("'.admin_url('admin-ajax.php').'",{
method:"POST",
headers:{"Content-Type":"application/x-www-form-urlencoded"},
body:"action=ms_load_calendar&m="+msMonth+"&y="+msYear
})
.then(r=>r.text())
.then(html=>{
document.getElementById("ms-calendar-wrap").innerHTML=html;
});
}

function msNextMonth(){msMonth++;if(msMonth>12){msMonth=1;msYear++;}msLoad();}
function msPrevMonth(){msMonth--;if(msMonth<1){msMonth=12;msYear--;}msLoad();}
</script>';

return ob_get_clean();
});

/* TOGGLE */
add_shortcode('ms_activities_toggle', function(){

ob_start();

echo '<div class="grve-btn-wrapper" style="margin-bottom:20px;display:flex;gap:10px;">
<a href="#" id="btnList" onclick="msShowTable();return false;" class="grve-btn grve-round grve-bg-primary-2 grve-text-white grve-btn-medium"><span>Liste</span></a>
<a href="#" id="btnCal" onclick="msShowCalendar();return false;" class="grve-btn grve-round grve-bg-primary-2 grve-text-white grve-btn-medium"><span>MÃ¥ned</span></a>
</div>';

echo '<div id="ms-table">'.do_shortcode('[ms_activities]').'</div>';
echo '<div id="ms-calendar" style="display:none;">'.do_shortcode('[ms_calendar]').'</div>';

echo '<script>
const btnList=document.getElementById("btnList");
const btnCal=document.getElementById("btnCal");

function msShowTable(){
document.getElementById("ms-table").style.display="block";
document.getElementById("ms-calendar").style.display="none";
btnList.classList.add("ms-active");
btnCal.classList.remove("ms-active");
}

function msShowCalendar(){
document.getElementById("ms-table").style.display="none";
document.getElementById("ms-calendar").style.display="block";
btnCal.classList.add("ms-active");
btnList.classList.remove("ms-active");
}

msShowTable();
</script>';

return ob_get_clean();
});
