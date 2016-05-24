<?php

class HomeController extends CController {

    private $var_headline = [];
    private $var_stream_news = [];
    private $sticky_id = [];

    function __construct()
    {
        parent::__construct();
        $this->model(array('news_model', 'today_tags_model', 'jsview_model', 'what_happen_model', 'tag_news_model'));
        $this->model(['News', 'TagNews', 'Tag', 'Jsview', 'Rubrics', 'Featured'], null, true);
        $this->library(array('table', 'lib_date', 'widget'));
        $this->helper('mongodb');
    }

    function index()
    {
        $url = $this->config['base_url'];
        $TE = 'Homepage';
        // $sticky_news_id = ['40215', '40525'];

        $get_sticky = $this->_featured_content();
        $load_news = $this->load_news($this->sticky_id);
        $headline = array_slice($load_news, 0, 3);//HEADLINE 3 lastest update news
        $stream_news = array_slice($load_news, 3); //STREAM NEWS after 3 lastest update news

        $meta =
        [
            'meta_title'        => 'Stories worth sharing - Brilio.net' ,
            'meta_description'  => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'     => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            'og_url'            => $this->config['base_url'],
            'og_image'          => $this->config['base_url']. substr($this->config['assets_image_url'] . 'logo-BRILIO.png', strlen($this->config['rel_url'])),
            'og_image_secure'   => $this->config['base_url']. substr($this->config['assets_image_url'] . 'logo-BRILIO.png', strlen($this->config['rel_url'])),
            'img_url'           => $this->config['assets_image_url'],
            'chartbeat_sections'=> 'Homepage',
            'chartbeat_authors' => 'Brilio.net',
            'meta_alternate'    => $this->config['m_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        ];

        $data =
        [
            'meta'              => $meta,
            'full_url'          => $url,
            'url'               => $url,
            'nama_halaman'      => $TE,
            'TE'                => $TE,
            'headline'          => $headline,
            'sticky_news'       => $get_sticky,
            'stream_news'       => $stream_news,
            'share_story'       => $this->view('desktop/box/_email', [], TRUE),
            'trending'          => $this->view('desktop/box/right_trending', $this->_trending(7, $TE), TRUE),
            'check_this_out'    => $this->view('desktop/box/right_check_this_out', $this->_check_this_out($TE, 8), TRUE),
            'bottom_menu'       => $this->view('desktop/box/bottom_menu', ['TE' => $TE ], TRUE),
        ];

        $this->_render('desktop/home/index', $data);
    }

    function load_news($sticky_id)
    {
        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'desktop_index_stream_news';

        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $limit = 57 - count($this->sticky_id);

        $stream_news = cache("query_desktop_".$cacheKey, function()  use($sticky_id, $limit){
             $news = News::
                    where('news_domain_id', '=', $this->config['domain_id'])
                    ->where('news_level', '=', '1')
                    ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                    ->whereNotIn('news_id', $this->sticky_id)
                    ->orderBy('news_date_publish', 'DESC')
                    ->take($limit)
                    ->with('news_tag_list')
                    ->get()
                    ->toArray();
            return $news;
        }, $interval);

        $stream_news = $this->generate_news_url($stream_news);

        foreach ($stream_news as $data)
        {
            if(!empty($data['news_tag_list']))
            {
                $tag['TAG_TITLE'] = $data['news_tag_list'][0]['tag_news_tags'];
                $tag['TAG_URL'] = $data['news_tag_list'][0]['tag_url_full'];
            }
            else
            {
                $tag['TAG_TITLE'] = '';
                $tag['TAG_URL']   = '';
            }

            $list_stream_news['tag_title']          = $tag['TAG_TITLE'];
            $list_stream_news['tag_url']            = $tag['TAG_URL'];
            $list_stream_news['news_title']         = $data['news_title'];
            $list_stream_news['news_url']           = $data['news_url_full'];
            if (isset($data['news_image_potrait']) && !empty($data['news_image_potrait']))
            {
                $list_stream_news['news_image_url']     = $data['news_image_location']. '/300x150-'. $data['news_image_potrait'];
            }else
            {
                $list_stream_news['news_image_url']     = $data['news_image_thumbnail'];
            }
            $list_stream_news['news_image_location_full']  = $data['news_image_location_full'];
            $list_stream_news['news_date_publish']  = $data['news_date_publish_indo'];
            $list_stream_news['news_image_location_255_170'] = $this->config['klimg_url'].$data['news_image_location_raw'].'/278x185-'. $data['news_image'];
            $data_stream_news[] = $list_stream_news;
        }

        $ret = $data_stream_news;
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    function _featured_content(){

      $interval = WebCache::App()->get_config('cachetime_default');
      $cacheKey = BRILIO_CURRENT_VIEWPORT.'_featured_content';

      if ($ret = checkCache($cacheKey))
      {
          return unserialize($ret);
      }

      $_sql =  cache("query_".$cacheKey, function() {
        return Featured::where('domain_id', $this->config['domain_id'])
                ->where('active', 'y')
                ->where('schedule_start', '<', date('Y-m-d H:i:s'))
                ->where('schedule_end', '>', date('Y-m-d H:i:s'))
                ->orderBy('order', 'asc')
                ->orderBy('schedule_start', 'asc')
                ->with('featured_news.sponsor_tag')
                ->get()
                ->toArray();
          return $news;
      }, $interval);

      foreach ($_sql as $key => $val) {
        if (!empty($val['featured_news'])) {
          $_sql[$key]['featured_news'] = $this->generate_news_url($val['featured_news']);
        }

        //put sticky news id into global,for exclusion in function load_news()
        if(!empty($val['news'])){
          $this->sticky_id[] = $val['news'];
        }

        //generate image path
        if(!empty($val['image'])){
          $_sql[$key]['image'] = $this->config['klimg_url'].$val['image'];
        }

        //generate date published
        // if(!empty($val[''])){
        //
        // }


      }
      echopre($_sql);
      return $_sql;
    }

}
