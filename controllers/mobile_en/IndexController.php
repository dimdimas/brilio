<?php

class IndexController extends CController {

    private $var_headline           = [];
    private $var_headline_second    = [];
    private $var_headline_id        = [];
    private $var_headline_second_id = array();

    function __construct() {
        parent::__construct();
        $this->model(['News', 'TagNews'], null, true);
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
            'og_image'          => $this->config['www_url_en']. substr($this->config['assets_image_url'] . 'logo-BRILIO.png', strlen($this->config['rel_url'])),
            'og_image_secure'   => $this->config['www_url_en']. substr($this->config['assets_image_url'] . 'logo-BRILIO.png', strlen($this->config['rel_url'])),
            'img_url'           => $this->config['assets_image_url'],
            'chartbeat_sections'=> 'Homepage',
            'chartbeat_authors' => 'Brilio.net',
            'expires'           => date(DATE_RFC1036),
            'last_modifed'      => date(DATE_RFC1036),
            'meta_alternate'    => $this->config['www_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['m_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        //get first headline
        $headline_data = $this->headline($TE_2, $sticky_news_id);
        //get other headline
        $other_headline_data = $this->headline_second($TE_2, $sticky_news_id);

        $_exclude = [];
        $news_exclude ='';
        $_exclude = array_merge($this->var_headline_second_id, $sticky_news_id);

        $data = array
        (
            'full_url'          => $this->config['base_url'],
            'meta'              => $meta,
            'TE_2'              => $TE_2,
            'announcer_index'   => $this->view('mobile_en/box/_announcer_banner', [], TRUE),
            'headline_big'      => $headline_data,
            'headline_list'     => $other_headline_data,
            'sticky_news_1'     => $this->sticky_news($TE_2, $sticky_news_id[0]), // sticky location under headline
            'sticky_news_2'     => $this->sticky_news($TE_2, $sticky_news_id[1]), // sticky location small headline number 3
            'popular'           => $this->_trending(6, $TE_2),
            'just_update'       => $this->just_update($_exclude),
            'today_tags'        => $this->view('mobile_en/box/_tags_bottom', ['list_tags_bottom' => $this->_today_tags(0, 10, 0, $TE_2, 'EN') ], true),
        );

        $this->_mobile_render('mobile_en/index/index', $data);
    }

    function headline($TE, $sticky_news_id)
    {
        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'mobile_en_headline_homepage_';

        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $headline = cache('query_mobile_en_headline_'.$cacheKey.'_', function() use ($sticky_news_id) {
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
        $cacheKey = 'mobile_en_sticky_news_index_'.$sticky_news_id;
        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        // ELOQUENT
        $query_sticky = cache('query_mobile_en_'.$cacheKey, function() use ($sticky_news_id) {
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
            $ret = $this->view('mobile_en/box/_sticky_news', ['sticky_data' => $sticky_data], true);
            return $ret ;
        }

        $sticky_data = $this->generate_news_url($query_sticky)[0];

        $sticky_data['news_image_sticky'] = $sticky_data['news_image_location'].'120x60-'.$sticky_data['news_image_potrait'];

        $ret = $this->view('mobile_en/box/_sticky_news', ['sticky_data' => $sticky_data], true);
        setCache($cacheKey, $ret, $interval);
        return $ret;
    }

    function headline_second($TE, $sticky_news_id)
    {

        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'mobile_en_another_headline_homepage';

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
            $headline = cache('query_mobile_en_get_headline_first_second_'.$cacheKey, function() use ($sticky_news_id) {
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

        $headline_second = cache('query_mobile_en_other_headline_'.$cacheKey, function() use ($sticky_news_id, $headline_news_id) {
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
      $cacheKey = 'mobile_en_just_update_'.implode(',' , $exclude_id);
      $TE = 'Homepage';

      if ($ret = checkCache($cacheKey))
      {
          return unserialize($ret);
      }

      $q_just_update = cache('query_mobile_en_just_update_'.$cacheKey, function() use ($exclude_id) {
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
