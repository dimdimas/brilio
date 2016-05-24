<?php

use Illuminate\Database\Capsule\Manager as DB;

class CategoryController extends CController {

    private $_exclude = [];
    private $_id_cat;
    private $data_meta_index = '';

    function __construct() {
        parent::__construct();
        $this->model(['News', 'TagNews', 'Tag'], null, true);
        $this->library(array('table', 'lib_date', 'widget'));
        $this->helper('mongodb');
    }

    function index($category = '', $halaman = '')
    {
        if (isset($this->_categories['url_to_id'][$category]))
        {
            $this->_id_cat = $this->_categories['url_to_id'][$category];
        }
        else
        {
            Output::App()->show_404();
        }

        $TE_2           = ucwords($category);
        $id_cat = $this->_id_cat;
        $url = $this->config['base_url'].$category ;

        //get news list
        $news_list = $this->news_list($category, $halaman);

        if ($halaman = '') {
          $data_meta = reset($news_list);
        }
        else {
          $data_meta = reset($news_list['stream_news']);
        }

        $data_breadcumb['CATEGORY_TITLE']   = $this->_categories['url_to_name'][$category];
        $data_breadcumb['CATEGORY_URL']     = strtolower($category);

        $meta = array(
            'meta_title'         => $this->_categories['url_to_name'][$category] . ' - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => str_replace(' ', ', ', $data_meta['news_synopsis']),
            'og_url'             => 'https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'],
            'og_image'           => $data_meta['news_image_location'].'657x438-'.$data_meta['news_image'],
            'og_image_secure'    => $data_meta['news_image_location'].'657x438-'.$data_meta['news_image'],
            'expires'            => date("D,j M Y G:i:s T", strtotime($data_meta["news_entry"])),
            'last_modifed'       => date("D,j M Y G:i:s T", strtotime($data_meta["news_entry"])),
            'chartbeat_sections' => $category,
            'chartbeat_authors'  => 'Brilio.net',
            'meta_alternate'     => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'          => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        $data = array(
            'full_url'      => $url,
            'meta'          => $meta,
            'TE_2'          => $TE_2,
            'breadcrumb'    => $data_breadcumb,
            'box_announcer' => $this->view('mobile/box/_announcer_banner', [], TRUE),
            'news_list'     => $news_list,
            'img_url'       => $this->config['assets_image_url'],
            'pagination'    => $this->table->link_pagination_category($category),
        );

        $this->_mobile_render('mobile/category/category', $data);

    }

    function news_list($category, $halaman='')
    {
        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'mobile_index_stream_news_' . $category . '-' . $halaman;

        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $page = $halaman;
        $base_url       = $this->config['rel_url'];
        $img_url        = $this->config['assets_image_url'];

        if (empty($halaman)) {
          $total_news = 31 ;
          $offset     = 0 ;
        }else{
          $total_news = 31 ;
          $offset     = ($page - 1) * $total_news;
        }

        $query_news_list = cache('mobile_query-'.$cacheKey.$offset.$total_news, function() use ($category, $offset , $total_news) {
            $rows = News::where('news_domain_id', '=', $this->config['domain_id'])
                        ->where('news_level', '=', '1')
                        ->where('news_category', '=', '["'. $this->_id_cat .'"]')
                        ->where('news_date_publish', '<', date('Y-m-d H:i:s'))
                        ->orderBy('news_date_publish', 'DESC');

                        //get total news for pagination
                        $total = clone $rows;
                        $total = $total->count();

                        //take news with limit
                        $rows  = $rows
                        ->skip($offset)
                        ->take($total_news)
                        ->with('news_tag_list')
                        ->get()
                        ->toArray();
            return ['rows' => $rows, 'total' => $total];
        }, $interval);

        if ($query_news_list['total']<1)
        {
            Output::App()->show_404();
        }

        if ($query_news_list) {
          $query_news_list['rows'] = $this->generate_news_url($query_news_list['rows']);

          $no = 1;
          foreach ($query_news_list['rows'] as $key => $val)
          {
            $query_news_list['rows'][$key]['NO'] = $no;
            $query_news_list['rows'][$key]['TE'] = ucwords($category);
            $no++;
          }
        }

        if (empty($halaman) || $halaman == 1)
        {
          $news_list['headline'] = array_slice($query_news_list['rows'], 0, 1);
          $news_list['stream_news'] = array_slice($query_news_list['rows'], 1);
          $news_list['headline'][0]['news_image_headline'] = $news_list['headline'][0]['news_image_location'].'657x438-'.$news_list['headline'][0]['news_image'];
        }
        else
        {
          $news_list['headline'] = '';
          $news_list['stream_news'] = $query_news_list['rows'];
        }

        $pConfig = array(
            'total_rows'        => $query_news_list['total'],
            'page'              => $page,
            'per_page'          => 30,
            'total_side_link'   => 4,
            'base_url'          => '',
            'go_to_page'        => false,
            'next'              => "Next",
            'previous'          => "Prev",
            'first'             => "",
            'last'              => "",
            'reverse_paging'    => false,
            'query_string'      => false,
            'base_url'          => $this->config['rel_url'] . $category . '/index{PAGE}.html',
            'base_url_first'    => $this->config['rel_url'] . $category . '/',
        );

        $this->table->set_pagination($pConfig);

        $ret = $news_list;
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }


}
