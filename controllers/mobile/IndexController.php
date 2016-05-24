<?php

class IndexController extends CController {

    private $var_headline           = [];
    private $var_headline_second    = [];
    private $var_headline_id        = [];
    private $var_headline_second_id = array();

    function __construct() {
        parent::__construct();
        $this->model(['News', 'TagNews', 'Video'], null, true);
        $this->library(array('table', 'lib_date'));
        $this->helper('mongodb');
    }

    function index() {

        $TE_1 = 'Menu';
        $TE_2 = 'Homepage';
        $sticky_news_id = ['34741', '34740'];

        $meta = array(
            'meta_title'        => 'Stories worth sharing - Brilio.net' ,
            'meta_description'  => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'     => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'            => $this->config['base_url'],
            'og_image'          => $this->config['base_url']. substr($this->config['assets_image_url'] . 'BRILIO.png', strlen($this->config['rel_url'])),
            'og_image_secure'   => $this->config['base_url']. substr($this->config['assets_image_url'] . 'BRILIO.png', strlen($this->config['rel_url'])),
            'img_url'           => $this->config['assets_image_url'],
            'chartbeat_sections'=> 'Homepage',
            'chartbeat_authors' => 'Brilio.net',
            'expires'           => date(DATE_RFC1036),
            'last_modifed'      => date(DATE_RFC1036),
            'meta_alternate'    => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        // exlcudetion ID for sticky news
        $_exclude = [];
        $_exclude = array_merge($this->var_headline_second_id, $sticky_news_id);
        $headline =  $this->get_headline($TE_2);

        $data = array
        (
            'full_url'          => $this->config['base_url'],
            'meta'              => $meta,
            'TE'                => $TE_2,
            'box_index_announcer'     => $this->view('mobile/box/_announcer_banner', [], TRUE),
            'headline_data'     => $headline,
            'just_update_data'  => $this->get_just_update($this->var_headline_id),
            'video_data'        => $this->get_video_news($TE_2, $this->var_headline_id),
            'trending_data'     => $this->_trending(30, $TE_2),
            '_list_categories_bottom' => $this->view('mobile/box/_menu_bottom', ['_list' => $this->mobile_menu_bottom($TE_2) ], true),

        );
        $this->_mobile_render('mobile/index/index', $data);
    }

    function get_headline($TE)
    {
        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'mobile_headline_homepage_';

        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $headline = cache('query_mobile_headline_'.$cacheKey.'_', function() {
             $news = News::where('news_domain_id', '=', $this->config['domain_id'])
                    ->where('news_level', '=', '1')
                    ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                    ->where('news_top_headline', '=', '1')
                    ->orderBy('news_date_publish', 'DESC')
                    ->take(3)
                    ->with('news_tag_list')
                    ->get()
                    ->toArray();
            return $news;
        }, $interval);

        if ($headline) {
          $headline = $this->generate_news_url($headline);
        }

        foreach ($headline as $key => $value) {
          $headline[$key]['news_image_headline_big'] = $this->config['klimg_url'].$value['news_image_location_raw'].'720x720-'.$value['news_image'];
          $headline[$key]['news_image_headline_small'] = $this->config['klimg_url'].$value['news_image_location_raw'].'720x304-'.$value['news_image'];
          $this->var_headline_id[] = $value['news_id'];
        }

        $ret = $headline;
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;

    }

    function get_just_update($exclude_id){

      $interval = WebCache::App()->get_config('cachetime_short');
      $cacheKey = 'mobile_just_update_'.implode(',' , $exclude_id);
      $TE = 'Homepage';
      $total_news = 29;

      if (empty($exclude_id)) {
        $exclude_id = '';
      }

      if ($ret = checkCache($cacheKey))
      {
          return unserialize($ret);
      }

      $q_just_update = cache('query_mobile_just_update_'.$cacheKey, function() use ($exclude_id, $total_news) {
          $news_list = News::where('news_domain_id', '=', $this->config['domain_id'])
                      ->where('news_level', '=', '1')
                      ->whereNotIn('news_id', $exclude_id)
                      ->where('news_type', '<>', '2')
                      ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                      ->orderBy('news_date_publish', 'DESC')
                      ->take($total_news)
                      ->with('news_tag_list')
                      ->get()
                      ->toArray();
          return $news_list;
      }, $interval);

      if ($q_just_update) {
        $q_just_update = $this->generate_news_url($q_just_update);
        $no = 1;
        foreach ($q_just_update as $key => $val) {
          $q_just_update[$key]['No'] = $no;
          $q_just_update[$key]['TE'] = $TE;
          $q_just_update[$key]['news_image_location_latest_big'] = $this->config['klimg_url'].$val['news_image_location_raw'].'720x480-'.$val['news_image'];
          $q_just_update[$key]['news_image_location_latest_medium'] = $this->config['klimg_url'].$val['news_image_location_raw'].'300x200-'.$val['news_image'];
          $q_just_update[$key]['news_image_location_latest_small'] = $this->config['klimg_url'].$val['news_image_location_raw'].'226x150-'.$val['news_image'];
          $no++;
        }
      }

      //spliting into several parts
      $_just_update = [];
      foreach ($q_just_update as $key => $val) {
        if ($val['No'] <= 8) {
          //take 8 for first latest after headline
          $_just_update['latest_1'][] = $val;
        }
        if($val['No'] >= 9 && $val['No'] <= 19){
          //take 11 because in latest 2 there is BIG LATEST
          $_just_update['latest_2'][] = $val;
        }
        if($val['No'] >= 20 ){
          //take rest array after spliting
          $_just_update['latest_3'][] = $val;
        }
      }

      $ret = $_just_update;

      setCache($cacheKey, serialize($ret), $interval);
      return $ret;

    }

    function get_video_news($TE, $exclude_id){

      $interval = WebCache::App()->get_config('cachetime_short');
      $cacheKey = 'mobile_get_video_';

      $TE = 'Homepage';

      if (empty($exclude_id)) {
        $exclude_id = '';
      }

      if ($ret = checkCache($cacheKey))
      {
          return unserialize($ret);
      }

      $_video = cache('query_'.$cacheKey, function() use ($exclude_id) {
        return  News::where('news_domain_id', $this->config['domain_id'])
                 ->where('news_type', 2)
                 ->where('news_level', 1)
                 ->whereNotIn('news_id', $exclude_id)
                 ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                 ->where('news_date_publish', '>=', date('Y-m-d H:i:s', strtotime('-3 days')))
                 ->with('video')
                 ->orderBy('news_date_publish', 'DESC')
                 ->take(3)
                 ->get()->toArray();
      }, $interval);

      $fixed_video_data = [];

      if ($_video) {
        $_video = $this->generate_news_url($_video);

        //datae needed in real
        $no = 1;
        foreach ($_video as $key => $val) {
          $fixed_video_data[$key]['NO'] = $no;
          $fixed_video_data[$key]['news_title'] = $val['news_title'];
          $fixed_video_data[$key]['news_url_full'] = $val['news_url_full'];
          $fixed_video_data[$key]['news_url_with_base'] = $val['news_url_with_base'];
          $fixed_video_data[$key]['news_url_full'] = $val['news_url_full'];
          $fixed_video_data[$key]['video'] = $val['video'];
          $no++;
        }

      }

      setCache($cacheKey, serialize($fixed_video_data), $interval);
      return $fixed_video_data;
    }

    function headline($TE, $sticky_news_id)
    {
        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'mobile_headline_homepage_';

        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $headline = cache('query_headline_'.$cacheKey.'_', function() use ($sticky_news_id) {
             $news = News::where('news_domain_id', '=', $this->config['domain_id'])
                    ->where('news_level', '=', '1')
                    ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                    ->whereNotIn('news_id', $sticky_news_id)
                    ->where('news_top_headline', '=', '1')
                    ->orderBy('news_date_publish', 'DESC')
                    ->take(1)
                    ->with('news_tag_list')
                    ->get()
                    ->toArray();
            return $news;
        }, $interval);

        if ($headline) {
          $headline = $this->generate_news_url($headline)[0];
        }

        $headline['news_image_headline'] = $this->config['klimg_url'].$headline['news_image_location_raw'].'657x438-'.$headline['news_image'];

        $this->var_headline_id = $headline['news_id'];

        $ret = $headline;
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;

    }

    function sticky_news($TE, $sticky_news_id)
    {
        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'mobile-sticky_news_index-'.$sticky_news_id;
        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        // ELOQUENT
        $query_sticky = cache($cacheKey, function() use ($sticky_news_id) {
            $news = News::where('news_domain_id', '=', $this->config['domain_id'])
                    ->where('news_level', '=', '1')
                    ->where('news_id', '=', $sticky_news_id)
                    ->where('news_date_publish', '<', 'NOW()')
                    ->with('news_tag_list')
                    ->get()
                    ->toArray();
            return $news;
        }, $interval);

        if ( count($query_sticky) == 0 || empty($query_sticky) )
        {
            $sticky_data = '' ;
            $ret = $this->view('mobile/box/_sticky_news', ['sticky_data' => $sticky_data], true);
            return $ret ;
        }

        $sticky_data = $this->generate_news_url($query_sticky)[0];

        $sticky_data['news_image_sticky'] = $sticky_data['news_image_location'].'120x60-'.$sticky_data['news_image_potrait'];

        $ret = $this->view('mobile/box/_sticky_news', ['sticky_data' => $sticky_data], true);
        setCache($cacheKey, $ret, $interval);
        return $ret;
    }

    function headline_second($TE, $sticky_news_id)
    {

        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'mobile_another_headline_homepage';

        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        if(!empty($this->var_headline))
        {
            $headline_news_id = $this->var_headline_id;
        }
        else
        {
            //get first headline || headline no 1
            $headline = cache('query_get_headline_first_second_'.$cacheKey, function() use ($sticky_news_id) {
                $news = News::where('news_domain_id', '=', $this->config['domain_id'])
                       ->where('news_level', '=', '1')
                       ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                       ->whereNotIn('news_id', $sticky_news_id)
                       ->where('news_top_headline', '=', '1')
                       ->orderBy('news_date_publish', 'DESC')
                       ->take(1)
                       ->with('news_tag_list')
                       ->get()
                       ->toArray();
               return $news;
           }, $interval);
           $headline_news_id = $headline[0]['news_id'];
        }

        $headline_second = cache('query_other_headline_'.$cacheKey, function() use ($sticky_news_id, $headline_news_id) {
             $news = News::where('news_domain_id', '=', $this->config['domain_id'])
                    ->where('news_level', '=', '1')
                    ->where('news_top_headline', '=', '1')
                    ->where('news_id', '<', $headline_news_id)
                    ->whereNotIn('news_id', $sticky_news_id)
                    ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                    ->orderBy('news_date_publish', 'DESC')
                    ->take(11)
                    ->with('news_tag_list')
                    ->get()
                    ->toArray();
            return $news;
        }, $interval);

        foreach ($headline_second as $key => $value) {
          $this->var_headline_second_id[] = $value['news_id'];
        }

        if ($headline_second) {
          $headline_second = $this->generate_news_url($headline_second);

          foreach ($headline_second as $key => $value) {
            $headline_second[$key]['news_image_headline_second'] = $value['news_image_location'].'120x60-'.$value['news_image_potrait'];
            $headline_second[$key]['TE'] = $TE ;
          }
        }

        $ret = $headline_second;
        $interval = WebCache::App()->get_config('cachetime_short');
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    function just_update($exclude_id){

      $interval = WebCache::App()->get_config('cachetime_short');
      $cacheKey = 'mobile_just_update_'.implode(',' , $exclude_id);
      $TE = 'Homepage';

      if ($ret = checkCache($cacheKey))
      {
          return unserialize($ret);
      }

      $q_just_update = cache('query_just_update_'.$cacheKey, function() use ($exclude_id) {
          $news_list = News::where('news_domain_id', '=', $this->config['domain_id'])
                      ->where('news_level', '=', '1')
                      ->whereNotIn('news_id', $exclude_id)
                      ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                      ->orderBy('news_date_publish', 'DESC')
                      ->take(14)
                      ->with('news_tag_list')
                      ->get()
                      ->toArray();
          return $news_list;
      }, $interval);

      if ($q_just_update) {
        $q_just_update = $this->generate_news_url($q_just_update);
        foreach ($q_just_update as $key => $val) {
          $q_just_update[$key]['TE'] = $TE;
        }
      }

      $ret = $q_just_update;
      setCache($cacheKey, serialize($ret), $interval);
      return $ret;

    }

}

?>
