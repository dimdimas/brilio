<?php

/**
 *  -----------------------------------------------------------------------------------
 *
 *  Konfigurasi umum
 *
 *
 *  -----------------------------------------------------------------------------------
 */
if(BRILIO_CURRENT_VIEWPORT == 'desktop')
{
  if(BRILIO_CURRENT_LANG == 'en'){
  	//define desktop EN
    $config['rel_url']                      = '/brilionet/www/br/en/';
  	$config['base_url']                     = 'http://192.168.0.253/brilionet/www/br/en/';
    // in devel using
    // $config['assets_url']                   = $config['rel_url'].'assets/v2';
  	$config['assets_url']                   = $config['rel_url'].'assets/';
  	$config['assets_url_v2']                = $config['rel_url'].'assets/';
  	$config['view_folder']                	= 'desktop_en';
  }else{
  	//define desktop ID
    $config['rel_url']                      = '/brilionet/www/br/';
  	$config['base_url']                     = 'http://192.168.0.253/brilionet/www/br/';
  	$config['assets_url']                   = $config['rel_url'].'assets/';
  	$config['assets_url_v2']                = $config['rel_url'].'assets/';
  	$config['view_folder']                	= 'desktop';
  }
}
else {
  if(BRILIO_CURRENT_LANG == 'en'){
  	//define mobile EN
  	$config['rel_url']                      = '/brilionet/m/br/en/';
  	$config['base_url']                     = 'http://192.168.0.253/brilionet/m/br/en/';
  	$config['assets_url']                   = $config['rel_url'].'assets/';
  	$config['assets_url_v2']                = $config['rel_url'].'assets/';
  	$config['view_folder']                	= 'mobile_en';
  }else{
    //define mobile ID
  	$config['rel_url']                      = '/brilionet/m/br/';
  	$config['base_url']                     = 'http://192.168.0.253/brilionet/m/br/';
  	$config['assets_url']                   = '/brilionet/m/br/assets/';
  	$config['assets_url_v2']                = '/brilionet/m/br/assets/';
  	$config['view_folder']                	= 'mobile';
  }
}

//global :
$config['assets_image_url']             = $config['assets_url'] .'img/';
$config['assets_css_url']               = $config['assets_url'] .'css/';
$config['assets_css_version']           = '3.0';
$config['assets_js_url']                = $config['assets_url'] .'js/';
$config['assets_font_url']                = $config['assets_url'] .'font/';

// for theme v2 //
$config['assets_image_url_v2']             = $config['assets_url_v2'] .'img/';
$config['assets_css_url_v2']               = $config['assets_url_v2'] .'css/';
$config['assets_css_version_v2']           = '2.1.0';
$config['assets_js_url_v2']                = $config['assets_url_v2'] .'js/';
// end for theme v2 //

$config['klimg_url']                    = 'http://192.168.0.253/newshubid/media/klimg/';
$config['klimg_dir']                    = __DIR__ . '/../../media/klimg/';

$config['json_dir']                     = __DIR__ . '/../../media/data/json/';
$config['json_dir_out']                 = __DIR__ . '/../../media/data/json_outbox/';

$config['asset_img_loading']            = $config['assets_url'] .'img/loading.gif';

$config['www_url']                      = "http://192.168.0.253/brilionet/www/br/";
$config['www_url_en']                   = "http://192.168.0.253/brilionet/www/br/en/";
$config['m_url']                        = "http://192.168.0.253/brilionet/m/br/";
$config['m_url_en']                     = "http://192.168.0.253/brilionet/m/br/en/";

$config['default_news_image']           = 'http://placehold.it/500x300';
$config['default_news_admin_image']     = $config['assets_image_url'] .  'men-admin-img.jpg';
$config['default_news_admin_thumb']     = $config['assets_image_url'] .  'men-admin-thumb300.jpg';
$config['undefined-image']              = $config['assets_image_url'] .  'undefined-image.jpg';

$config['default_news_admin_300x400']   = 'http://placehold.it/300x400';

$config['default_news_image_160']       = 'http://placehold.it/160x80';
$config['default_news_image_200']       = 'http://placehold.it/200x200';
$config['default_news_image_300']       = 'http://placehold.it/300x300';

$config['server_cluster']               = array("http://cms-dev.trigger.co.id/app/");


?>
