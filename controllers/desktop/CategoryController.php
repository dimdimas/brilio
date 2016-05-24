<?php

use Illuminate\Database\Capsule\Manager as DB;

class CategoryController extends CController {

    private $_id_cat ;
    private $share_url          = '';
    private $data_meta_index    = '';

    function __construct()
 	 {
   		parent::__construct();
   		$this->library(array('SponsorLinkContent'));
   		$this->model(['News','Tag', 'TagNews', 'NewsRelated', 'NewsPaging', 'SponsorTag'],null, true);
   		$this->helper('mongodb');

 	 }

    function index($category = '', $url_news = '', $url_paging_news = '')
    {
        $TE = 'Index category';

        if (isset($this->_categories['url_to_id'][$category]))
            $this->_id_cat = $this->_categories['url_to_id'][$category];
        else
            Output::App()->show_404();

      	$hide_news_html = explode('.html', $url_news);
      	$keyword_news   = $hide_news_html[0];
      	$page_full      = explode("index", $keyword_news);

        // breadcrumb
      	$breadcumb['BREADCUMB_CATEGORY_TITLE'] = $this->_categories['url_to_name'][$category];
      	$breadcumb['BREADCUMB_CATEGORY_URL']   = strtolower($category);

        if ($url_news == '')
        {
            $page = 1;
        }
        else
        {
            $page_full    = explode("index", $url_news);
            $hide_index   = $url_news;
            $page_no_html = explode(".html", $hide_index);

            if ($page_no_html == '' || $page_no_html[0] == '')
            {
                $page = 1;
            }
            else
            {
                $page = $page_no_html[0];
            }
        }

        if($page==1){
            $headline = $this->headline($category);//render_headline for page 1
        }else{
            $headline = ''; //default for another page 1
        }

        $stream_news = $this->stream_news($category, $page);
        if ((!isset($stream_news['DATA'])||$stream_news['DATA']=='') && $headline=='')
        {
            Output::App()->show_404();
        }
        // CALL HEADLING

        $data_meta      = $this->meta_index_category($category);
        $datetime       = explode(" ", $data_meta['NEWS_ENTRY']);
        $datetime_clear = explode("-", $datetime[0]);
        $year           = $datetime_clear[0];
        $month          = $datetime_clear[1];
        $date           = $datetime_clear[2];

        if ($data_meta['CATEGORY_TITLE'] == 'news')
        {
            $meta_des = 'Segala macam kisah kehidupan, teladan, inspirasi, ilmu pengetahuan ada di sini';
        }
        else
        {
            $meta_des = 'Segala macam informasi gaya hidup anak muda, kesehatan, dan relationship ada di sini';
        }

        $url = $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url']));
        $this->share_url = $data_meta['NEWS_URL'];

        $meta =
        [
            'meta_title'         => $this->_categories['url_to_name'][$category] . ' - Stories worth sharing - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            // 'meta_keywords'   => 'brilio, temukan, segala, kisah, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, di, sini',
            // 'og_url'             => $data_meta['NEWS_URL'],
            'og_url'             => 'https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'],
            'og_image'           => 'http://cdn.klimg.com/newshub.id/'. substr($data_meta['NEWS_IMAGES'], strlen($this->config['klimg_url'])),
            'og_image_secure'    => $data_meta['NEWS_IMAGES'],
            'img_url'            => $this->config['assets_image_url'],
            'expires'            => date("D,j M Y G:i:s T", strtotime($data_meta["NEWS_ENTRY"])),
            'meta_keywords'      => str_replace(' ', ', ', $data_meta['NEWS_SYNOPSIS']),
            'chartbeat_sections' => $category,
            'chartbeat_authors'  => 'Brilio.net',
            'meta_alternate'     => $this->config['m_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'          => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        ];

        $data =
        [
            'meta'              => $meta,
            'url'               => $url,
            'full_url'          => $url,
            'nama_halaman'      => $TE,
            'TE'                => $category,
            'headline'          => (($page == 1) ? $headline : ''),
            'carousel'          => $this->carousel($category),
            'share_story'       => $this->view('desktop/box/_email', [], TRUE),
            'stream_news'       => $stream_news,
            'trending'          => $this->view('desktop/box/right_trending', $this->_trending(7, 'Index category'), TRUE),
            'check_this_out'    => $this->view('/desktop/box/right_check_this_out', $this->_check_this_out('Index category', 8), TRUE),
            'bottom_menu'       => $this->view('desktop/box/bottom_menu', ['TE' => 'Index category' ], TRUE),
        ];

        $this->_render('desktop/category/category', $data);
    }

    function meta_index_category($category)
    {
        $interval = WebCache::App()->get_config('cachetime_short');
    	$cacheKey = 'desktop-category_meta_index-' . $category;
        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        if(!empty($this->data_meta_index))
        {
            $query_news_category = $this->data_meta_index;
        }
        else
        {
//            $this->big_box_category($category);
//            $query_news_category = $this->data_meta_index;
            $query_big_box = cache("query-".$cacheKey, function() {
                return News::where('news_domain_id', '=', $this->config['domain_id'])
                        ->where('news_level', '=', '1')
                        ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                        ->where('news_category', '=', '["'.$this->_id_cat.'"]')
                        ->orderby('news_date_publish', 'DESC')
                        ->take(1)
                        ->get()
                        ->toArray();

            }, $interval);
            $query_news_category = $query_big_box[0];
            $this->data_meta_index = $query_news_category;
            $news_fix = $this->generate_news_url($query_big_box);
        }

        $news_fix = reset($news_fix);

        $ret['NEWS_ID']        = $news_fix['news_id'];
        $ret['NEWS_SYNOPSIS']  = $news_fix['news_synopsis'];
        $ret['NEWS_IMAGES']    = $news_fix['news_image_location_full'];
        $ret['NEWS_ENTRY']     = $news_fix['news_date_publish']; //format 22 April 2015 13.30
        $ret['NEWS_URL']       = $news_fix['news_url_with_base'];
        $ret["CATEGORY_TITLE"] = $news_fix['news_category_name'];

        $interval = WebCache::App()->get_config('cachetime_short');
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    function headline($category)
    {
        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'desktop-headline-' . $category;
        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $q_headline = cache("desktop_query-".$cacheKey, function() {
            return News::where('news_domain_id', '=', $this->config['domain_id'])
                    ->where('news_level', '=', '1')
                    ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                    ->where('news_category', '=', '["'.$this->_id_cat.'"]')
                    ->orderby('news_date_publish', 'DESC')
                    ->with('news_tag_list')
                    ->take(2)
                    ->get()
                    ->toArray();

        }, $interval);

        $headline = $this->generate_news_url($q_headline);

        $ret = $headline;
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;

    }

    function carousel($category)
    {
        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'desktop_carousel_';
        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $q_carousel = cache("desktop_query-".$cacheKey, function() {
            return News::where('news_domain_id', '=', $this->config['domain_id'])
                    ->where('news_level', '=', '1')
                    ->where('news_editor_pick', '=', '1')
                    ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                    ->whereNotIn('news_category', ['["'.$this->_id_cat.'"]'])
                    ->orderby('news_date_publish', 'DESC')
                    ->take(13)
                    ->get()
                    ->toArray();

        }, $interval);

        $carousel = $this->generate_news_url($q_carousel);
        $ret = $carousel;

        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    function stream_news($category, $page)
    {
        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'desktop_stream_news_category-' . $category . '-' . $page;
        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $offset = ($page - 1) * 24+2;//+2 = for skip 2 news in headline

        $q_stream_news = cache("desktop_query".$cacheKey, function() use ($category, $offset) {
            $rows = News::where('news_domain_id', '=', $this->config['domain_id'])
                        ->where('news_level', '=', '1')
                        ->where('news_category', '=', '["'. $this->_id_cat .'"]')
                        ->with('news_tag_list')
                        ->where('news_date_publish', '<', date('Y-m-d H:i:s'))
                        ->orderBy('news_date_publish', 'DESC');
            $total = clone $rows;
            $total = $total->count();
            $rows  = $rows
                        ->take(24)
                        ->skip($offset)
                        ->get()
                        ->toArray();
            return ['rows' => $rows, 'total' => $total];
        }, $interval);

        $pConfig = array(
            'total_rows'      => $q_stream_news['total'],
            'page'            => $page,
            'per_page'        => 24,
            'total_side_link' => 5,
            'go_to_page'      => false,
            'next'            => "Next",
            'previous'        => "Prev",
            'first'           => "",
            'last'            => "",
            'reverse_paging'  => false,
            'query_string'    => false,
            'base_url'        => $this->config['base_url'] . $category . '/index{PAGE}.html',
            'base_url_first'  => $this->config['base_url'] . $category . '/',
        );

        $this->table->set_pagination($pConfig);

        $stream_news = $this->generate_news_url($q_stream_news['rows']);

        if(!empty($q_stream_news))
        {
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

//                echopre($data);die;

                $list_stream_news['tag_title']          = $tag['TAG_TITLE'];
                $list_stream_news['tag_url']            = $tag['TAG_URL'];
                $list_stream_news['news_title']         = $data['news_title'];
                $list_stream_news['news_url']           = $data['news_url_full'];
//                $list_stream_news['news_image_url']     = $data['news_image_thumbnail_300'];
                $list_stream_news['news_image_url']     = $data['news_image_location']."255x170-".$data['news_image_potrait'];
                $list_stream_news['news_date_publish']  = $data['news_date_publish_indo'];
                $list_stream_news['news_image_location_255_170'] = $this->config['klimg_url'].$data['news_image_location_raw'].'/278x185-'. $data['news_image'];
                $data_stream_news['DATA'][] = $list_stream_news;
            }
        }
        else
        {
            $data_stream_news['DATA'] = '';
        }

        $data_stream_news['PAGINATION'] = $this->table->link_pagination_category($category);

        $ret = $data_stream_news;
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    function sosmed($category)
    {
        if (!empty($this->share_url))
        {
            $sosmed_data['sosmed_url'] = $this->share_url;
        }
        else
        {
            $sosmed_data['sosmed_url'] = $this->config['base_url'] . $category . '/';
        }
        return $this->view('box/_set_box_social_share', $sosmed_data, true);
    }

}

?>
