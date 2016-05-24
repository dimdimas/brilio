<?php

class DevelHomeController extends CController {

    private $headline_id = [];
    private $var_stream_news = [];
    private $sticky_id = [];
    private $total_sticky = 0;

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

        //get_sticky news
        $get_sticky = $this->get_featured_content();

        //get headline news
        $get_headline = $this->get_headline();

        //get stream news
        $stream_news = $this->get_stream_news();

        // start merging sticky into stream news
        $_arr = [];

        $no= 0 ;
        foreach ($stream_news as $key => $val) {
            if (isset($get_sticky[$key]) ) {
              $_array[$no] = $get_sticky[$key];
              $no++;
            }else{
              $_array[$no] = $val;
              $no++;
            }
        }
        //end of merging sticky into stream

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
            'headline'          => $get_headline,
            'sticky_news'       => $get_sticky,
            'stream_news'       => $_array,
            'share_story'       => $this->view('desktop/box/_email', [], TRUE),
            'trending'          => $this->view('desktop/box/right_trending', $this->_trending(7, $TE), TRUE),
            'check_this_out'    => $this->view('desktop/box/right_check_this_out', $this->_check_this_out($TE, 8), TRUE),
            'bottom_menu'       => $this->view('desktop/box/bottom_menu', ['TE' => $TE ], TRUE),
        ];

        $this->_render('desktop/home/develindex', $data);
    }

    function get_headline(){
      $interval = WebCache::App()->get_config('cachetime_short');
      $cacheKey = 'desktop_index_home_headline_news';

      if ($ret = checkCache($cacheKey))
      {
          return unserialize($ret);
      }

      $_headline = cache("query_".$cacheKey, function() {
           return News::
                  where('news_domain_id', '=', $this->config['domain_id'])
                  ->where('news_level', '=', '1')
                  ->where('news_top_headline', 1)
                  ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                  ->whereNotIn('news_id', $this->sticky_id)
                  ->orderBy('news_date_publish', 'DESC')
                  ->take(3)
                  ->with('news_tag_list')
                  ->get()
                  ->toArray();
      }, $interval);

      if ($_headline) {
        $_headline = $this->generate_news_url($_headline);

        foreach ($_headline as $key => $val) {
          //set headline id for exclusion
          $this->headline_id[] = $val['news_id'];

          $tmp['news_title'] = $val['news_title'];
          $tmp['news_url'] = $val['news_url_full'];
          $tmp['news_date_publish'] = $val['news_date_publish_indo'];
          $tmp['news_image_location_full'] = $val['news_image_location_full'];

          if(!empty($val['news_tag_list'][0]))
          {
              $tmp['tag_title'] = $val['news_tag_list'][0]['tag_news_tags'];
              $tmp['tag_url'] = $val['news_tag_list'][0]['tag_url_full'];
          }
          else
          {
              $tmp['tag_title'] = '';
              $tmp['tag_url']   = '';
          }

          $headline[] = $tmp;
        }
      }

      $ret = $headline;
      setCache($cacheKey, serialize($ret), $interval);
      return $ret;
    }

    function get_stream_news()
    {
        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'desktop_index_home_stream_news';

        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $exclude_id  = array_merge($this->sticky_id, $this->headline_id);
        $limit = 54 - $this->total_sticky;

        $stream_news = cache("query_".$cacheKey, function()  use( $exclude_id, $limit){
             $news = News::
                    where('news_domain_id', '=', $this->config['domain_id'])
                    ->where('news_level', '=', '1')
                    ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                    ->whereNotIn('news_id', $exclude_id)
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
            $list_stream_news['news_image_location_255_170'] = $this->config['klimg_url'].$data['news_image_location_raw'].'278x185-'. $data['news_image'];
            $data_stream_news[] = $list_stream_news;
        }

        $ret = $data_stream_news;
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    function get_featured_content(){

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
                ->with('featured_news.news_tag_list')
                ->get()
                ->toArray();
          return $news;
      }, $interval);

      foreach ($_sql as $key => $val) {
        //generate image path
        if(!empty($val['image'])){
          $_sql[$key]['image'] = $this->config['klimg_url'].$val['image'];
        }

        //put sticky news id into global,for exclusion in function load_news()
        if(!empty($val['news'])){
          $this->sticky_id[] = $val['news'];
        }

        if (!empty($val['schedule_start'])) {
          $_sql[$key]['schedule_start'] = $this->lib_date->indo($val['schedule_start']);
        }

        if (!empty($val['featured_news'][0])) {
          //change featured_news into data format homepage;
          /*
              [tag_title] => sosial
              [tag_url] => /brilionet/www/tag/s/sosial/
              [news_title] => Jomblo Hunt Ep. 1 - Dari Mata Turun Ke Hati
              [news_url] => /brilionet/www/video/cinta/jomblo-hunt-ep-1-dari-mata-turun-ke-hati-1605178.html
              [news_image_url] => http://192.168.0.253/newshubid/media/klimg/video/2016/05/17/41730//300x150-secondary-jomblo-hunt-ep-1-dari-mata-turun-ke-hati-1605178.jpg
              [news_image_location_full] => http://192.168.0.253/newshubid/media/klimg/video/2016/05/17/41730/750xauto-jomblo-hunt-ep-1-dari-mata-turun-ke-hati-1605178.jpg
              [news_date_publish] => 17 Mei 2016 17:18
              [news_image_location_255_170] => http://192.168.0.253/newshubid/media/klimg/video/2016/05/17/41730//278x185-jomblo-hunt-ep-1-dari-mata-turun-ke-hati-1605178.jpg
          */

            //generate news
            $tmp_news = $this->generate_news_url($val['featured_news']);
            $_sql[$key]['featured_news'] = $tmp_news;

            $news = $_sql[$key]['featured_news'][0];

            $tmp['tag_title'] = '';
            $tmp['tag_url'] = '#';
            $tmp['tag_sponsor_brand_url'] = '';
            $tmp['tag_sponsor_brand_img'] = '';

            $tmp['order'] = $val['order'];
            $tmp['news_title'] = $news['news_title'];
            $tmp['news_url'] = $news['news_url_full'];
            $tmp['news_date_publish'] = $news['news_date_publish_indo'];
            $tmp['news_image_location_full'] = $news['news_image_location_full'];
            $tmp['news_image_location_255_170'] = $this->config['klimg_url'].$news['news_image_location_raw'].'278x185-'. $news['news_image'];

            if (!empty($news['news_tag_list'][0])) {
              $tmp['tag_title'] = $news['news_tag_list'][0]['tag_news_tags'];
              $tmp['tag_url'] = $news['news_tag_list'][0]['tag_url_full'];
            }

            if (!empty($news['sponsor_tag'])) {
              $tmp['tag_title'] = $news['sponsor_tag']['tag_name'];
              $tmp['tag_url'] = $news['sponsor_tag']['tag_brand_url'];
              $tmp['tag_sponsor_brand_url'] = $news['sponsor_tag']['brand_url'];
              $tmp['tag_sponsor_brand_img'] = $news['sponsor_tag']['brand_image'];
            }

            $_sql[$key] = $tmp;
        }
        else
        {
            //promoted
            $_dmp['order'] = $val['order'];
            $_dmp['tag_title'] = !empty($val['tag']) ? $val['tag'] : 'PROMOTED' ;
            $_dmp['tag_url'] = '#';
            $_dmp['news_title'] = !empty($val['title']) ? $val['title'] : '' ;
            $_dmp['news_url'] = !empty($val['url']) ? $val['url'] : '' ;
            $_dmp['news_date_publish'] = !empty($val['schedule_start']) ? $this->lib_date->indo($val['schedule_start']) : '' ;
            $_dmp['news_image_location_full'] = !empty($val['image']) ? $this->config['klimg_url'].$val['image'] : '';
            $_dmp['news_image_location_255_170'] = !empty($val['image']) ? $this->config['klimg_url'].$val['image'] : '';
            $_sql[$key] = $_dmp;
        }
      }
      //set total sticky
      $this->total_sticky = count($_sql);

      //generate key sticky
      foreach ($_sql as $key => $val) {
        $_arr[$val['order']-1] = $val;
      }

      setCache($cacheKey, serialize($_sql), $interval);
      return $_arr;
    }

}
