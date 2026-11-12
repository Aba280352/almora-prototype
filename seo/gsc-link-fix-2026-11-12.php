// ALMORA - one-time internal link fix (Search Console audit 2026-11-12). Snippet id 49 on the live site, now INACTIVE.
// What it did: POST /wp-json/almora-tmp/v1/fixlinks?apply=1 (admin only) walked the Elementor JSON of pages 7752 (home),
// 8755 (moissanite), 7051 (calculator) and template 8107 (header) and, for values that are exactly one internal URL,
// removed the trailing slash before the query (https://almora.co.il/best-sellers/ -> https://almora.co.il/best-sellers),
// because the site 301-redirects the slash form. 16 values changed. Originals saved in options
// almora_backup_gsc_links_{7752,8107,8755}_20261112 (raw _elementor_data).
if(!function_exists('almora_gsc_fix_walk')){
function almora_gsc_fix_walk($v,&$chg){
  if(is_array($v)){ foreach($v as $k=>$x){ $v[$k]=almora_gsc_fix_walk($x,$chg); } return $v; }
  if(is_string($v) && preg_match('~^https://almora\.co\.il(/[^?#]*[^/?#])/(\?[^#]*)?$~u',$v,$m)){
    $n='https://almora.co.il'.$m[1].(isset($m[2])?$m[2]:'');
    $chg[]=array($v,$n);
    return $n;
  }
  return $v;
}
}
add_action('rest_api_init', function(){
  register_rest_route('almora-tmp/v1','/fixlinks',array('methods'=>'POST','permission_callback'=>function(){ return current_user_can('manage_options'); },'callback'=>function($req){
    $apply=(bool)$req->get_param('apply');
    $ids=array(7752,8107,8755,7051);
    $report=array();
    foreach($ids as $id){
      $raw=get_post_meta($id,'_elementor_data',true);
      if(!is_string($raw)||$raw==='') { $report[$id]='no data'; continue; }
      $data=json_decode($raw,true);
      if(!is_array($data)){ $report[$id]='not json'; continue; }
      $chg=array();
      $new=almora_gsc_fix_walk($data,$chg);
      $report[$id]=array('changes'=>$chg);
      if($apply && $chg){
        update_option('almora_backup_gsc_links_'.$id.'_20261112',$raw,false);
        $enc=wp_json_encode($new);
        update_post_meta($id,'_elementor_data',wp_slash($enc));
        delete_post_meta($id,'_elementor_element_cache');
        $report[$id]['applied']=true;
      }
    }
    if($apply){
      if(class_exists('\Elementor\Plugin')){ \Elementor\Plugin::$instance->files_manager->clear_cache(); }
      if(function_exists('rocket_clean_domain')){ @rocket_clean_domain(); }
    }
    return $report;
  }));
});
