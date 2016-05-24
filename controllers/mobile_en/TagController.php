<?php

use Illuminate\Database\Capsule\Manager as DB;

class TagController extends CController {

    private $data_meta_index_tag = '';
    private $_page = '';

    function __construct() {
        parent::__construct();
        $this->model(['TagNews', 'Tag', 'News'], null, true);
        $this->library(array('table', 'lib_date'));
        $this->helper('mongodb');
    }

    function index($tags_abjad = '', $halaman = '') {

        if (empty($tags_abjad))
        {
            $tags_title = 'TAGS';
        }
        else
        {
            $page_full = explode("index", $tags_abjad);
            if (empty($page_full[1]))
            {
                $tags_title = $tags_abjad;
            }
            else
            {
                $tags_title = 'TAGS';
            }
        }

        $TE_1 = 'Menu';
        $TE_2 = $tags_title;
        $TE_3 = 'Tag pages';

        $tag_filter = '';

        $list_tag = $this->list_tag($TE_2, $tags_abjad, $halaman);
        $data_meta_tags = $this->meta_index_tag('mobile_tag_meta_index', $tag_filter, $halaman);

        $datetime       = explode(" ", $data_meta_tags['NEWS_ENTRY']);
        $datetime_clear = explode("-", $datetime[0]);
        $year           = $datetime_clear[0];
        $month          = $datetime_clear[1];
        $date           = $datetime_clear[2];

        $url = $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url']));

        // FOR META TITLE
        if( ($tags_abjad == '') OR (strpos($tags_abjad, 'index') !== false) )
        {
            if ($this->_page == 1)
            {
                $meta_title = 'Popular Tags and Topic | Brilio.net';
            }
            else
            {
                $meta_title = 'Popular Tags and Topic - Halaman '. $this->_page .' | Brilio.net';
            }
        }
        else
        {
            if ($this->_page == 1)
            {
                $meta_title = 'Popular Tags and Topic - Tags '. strtoupper($tags_abjad) .' | Brilio.net';
            }
            else
            {
                $meta_title = 'Popular Tags and Topic - Tags '. strtoupper($tags_abjad) .' - Halaman '. $this->_page .' | Brilio.net';
            }
        }

        $meta = array(
            'meta_title'        => $meta_title,
            'meta_description'  => 'Tag dan topik populer tentang segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan | Brilio.net',
            'meta_keywords'     => str_replace(' ', ', ', $data_meta_tags['NEWS_SYNOPSIS']),
            'og_url'            => $data_meta_tags['OG_URL'],
            'og_image'          => 'http://cdn.klimg.com/newshub.id/'. substr($data_meta_tags['NEWS_IMAGES'], strlen($this->config['klimg_url'])),
            'og_image_secure'   => $data_meta_tags['NEWS_IMAGES'],
            'img_url'           => $this->config['assets_image_url'],
            'expires'           => date("D,j M Y G:i:s T", strtotime($data_meta_tags['NEWS_ENTRY'])),
            'last_modifed'      => date("D,j M Y G:i:s T", strtotime($data_meta_tags["NEWS_ENTRY"])),
            'chartbeat_sections'=> 'Popular Tags and Topic',
            'chartbeat_authors' => 'Brilio.net',
            'meta_alternate'    => $this->config['www_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['m_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        $data = array(
            'full_url'          => $url,
            'meta'              => $meta,
            'TE_2'              => $TE_1,
            'title'             => 'Popular Tags and Topic',
            'box_announcer'     => $this->view('mobile_en/box/_announcer_banner', [], TRUE),
            'BREADCRUMB'        => $this->breadcrumb($tags_abjad),
            'TAGS_TITLE'        => $tags_title,
            'LIST_TAG'          => $list_tag,
            'LIST_ALPHABET'     => $this->list_alphabet($TE_2, $tags_abjad, $halaman),
        );

        $this->_mobile_render('mobile_en/tag/index_tag', $data);
    }

    function meta_index_tag($cacheKey, $tag, $halaman='')
    {
        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }


        $count_index = substr_count($halaman, "index");
        if (empty($halaman))
        {
            $keyword = '';
        }
        else
        {
            if ($count_index != 0)
            {
                $keyword = '';
            }
            else
            {
                $clear_html = explode(".html", $halaman);
                $keyword    = $clear_html[0];
            }
        }

        if(empty($this->data_meta_index_tag))
        {

            $sql= cache($cacheKey."-left_mobile_en_", function() {
                return TagNews::groupBy('tag_news_tag_id')
                    ->with('News')
                    ->with('Tag')
                    ->orderBy('tag_news_id', 'DESC')->whereHas('News', function ($q){
                        $q->where('news_level', '1');
                        $q->where('news_date_publish', '<=',date('Y-m-d H:i:s'));
                        $q->where('news_domain_id', $this->config['domain_id']);
                        if (!empty($tag))
                        {
                            $q->where('tag_url', '=', $tag);
                        }

                        if (!empty($tag))
                        {
                            $q->where('news_url', '=', $keyword);
                        }
                    })
                    ->take(1)
                    ->get()
                    ->toArray();
            }, 300);
            $news_meta_index_tag                    = $sql[0]['news'];
            $news_meta_index_tag['tag_news_tags']   = $sql[0]['tag_news_tags'];
        }
        else
        {
            $news_meta_index_tag                    = $this->data_meta_index_tag[0]['news'];
            $news_meta_index_tag['tag_news_tags']   = $this->data_meta_index_tag[0]['tag_news_tags'];
        }

        $category_meta = $this->_category($news_meta_index_tag['news_category']);
        $category = $category_meta['CATEGORY_URL'];
        $datetime = explode(" ", $news_meta_index_tag['news_entry']);
        $datetime_clear = explode("-", $datetime[0]);
        $year = $datetime_clear[0];
        $month = $datetime_clear[1];
        $date = $datetime_clear[2];

        if ($news_meta_index_tag['news_type'] == '1')
        {
            $news_url = $this->config['rel_url'] . 'photo/' . $category . '/' . $news_meta_index_tag['news_url'] . '.html';
            $og_url = $this->config['www_url_en'] . 'photo/' . $category . '/' . $news_meta_index_tag['news_url'] . '.html';
            $news_type = 'photonews';
        }
        elseif ($news_meta_index_tag['news_type'] == '2')
        {
            $news_url = $this->config['rel_url'] . 'video/' . $category . '/' . $news_meta_index_tag['news_url'] . '.html';
            $og_url = $this->config['www_url_en'] . 'video/' . $category . '/' . $news_meta_index_tag['news_url'] . '.html';
            $news_type = 'video';
        }
        else
        {
            $news_url = $this->config['rel_url'] . $category . '/' . $news_meta_index_tag['news_url'] . '.html';
            $og_url = $this->config['www_url_en'] . $category . '/' . $news_meta_index_tag['news_url'] . '.html';
            $news_type = 'news';
        }

        $ret['NEWS_TITLE']      = $news_meta_index_tag['news_title'];
        $ret['NEWS_ID']         = $news_meta_index_tag['news_id'];
        $ret['NEWS_SYNOPSIS']   = $news_meta_index_tag['news_synopsis'];
        $ret['NEWS_IMAGES']     = $this->config['klimg_url'] . $news_type . '/' . $year . '/' . $month . '/' . $date . '/' . $news_meta_index_tag['news_id'] . '/' . $news_meta_index_tag['news_image'];
        $ret['NEWS_ENTRY']      = $news_meta_index_tag['news_date_publish']; //format 22 April 2015 13.30
        $ret['NEWS_URL']        = $news_url;
        $ret['OG_URL']          = $og_url;
        $ret['TAG_TITLE']       = $news_meta_index_tag['tag_news_tags'];

        $interval = WebCache::App()->get_config('cachetime_default');
        setCache($cacheKey, serialize($ret), $interval);

        return $ret;
    }

    function breadcrumb($tags_abjad) {
        if (empty($tags_abjad)) {
            $view_breadcumb = '';
        } else {
            $page_full = explode("index", $tags_abjad);
            if (empty($page_full[1])) {
                if ($tags_abjad == '') {
                    $view_breadcumb['LIST_BREADCUMB'] = '';
                } else {
                    $data_breadcumb['BREADCUMB_SIMBOL'] = '&raquo;';
                    $data_breadcumb['BREADCUMB_TITLE'] = ucwords($tags_abjad);
                    $data_breadcumb['BREADCUMB_URL'] = $this->config['rel_url'] . "tag/" . $tags_abjad . "/";
                    $view_breadcumb = $data_breadcumb;

                }
            } else {
                $view_breadcumb = '';
            }
        }
        return $view_breadcumb;
    }

    function list_alphabet ($TE, $tags_abjad, $halaman)
    {
        //LIST ALPHABET
        $s = 'a';
        while ($s <= 'z')
        {
            if ($tags_abjad == '')
            {
                $c['TAGS_AKTIF'] = '';
            }
            else
            {
                if ($tags_abjad == $s)
                {
                    $c['TAGS_AKTIF'] = "select_tag";
                }
                else
                {
                    $c['TAGS_AKTIF'] = '';
                }
            }
            $c['ALPHABET'] = $s;
            $s             = chr(ord($s) + 1);

            $list['LIST_ALPHABET'][] = $c;
        }
        // end LIST ALPHABET
        return $list;
    }

    function list_tag($TE, $tags_abjad, $halaman)
    {
        $cacheKey = 'mobile_en_box_index_tag_' . $tags_abjad . '_' . $halaman;

        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $page_full = explode("index", $tags_abjad);

        if (empty($page_full[1]))
        {
            $filter_name = $tags_abjad;
            $halaman_url = explode("index", $halaman);
            if (empty($halaman_url[1]))
            {
                $page = 1;
            }
            else
            {
                $hide_index_halaman = $halaman_url[1];
                $filter_halaman = explode(".html", $hide_index_halaman);
                if (empty($filter_halaman[0]))
                {
                    $page = 1;
                }
                else
                {
                    $page = $filter_halaman[0];
                }
            }
        }
        else
        {
            $filter_name = '';
            $hide_index = $page_full[1];
            $filter = explode(".html", $hide_index);
            if (empty($filter[0]))
            {
                $page = 1;
            }
            else
            {
                $page = $filter[0];
            }
        }

        $this->_page = $page;

        $base_url   = $this->config['rel_url'];
        $img_url    = $this->config['assets_image_url'];

        //pagination controll
        if (empty($filter_name))
        {
            $url_pagination = $this-> config['rel_url'] . 'tag/index{PAGE}.html';
            $base_url_first = $this->config['rel_url'] . 'tag/';
        }
        else
        {
            $url_pagination = $this->config['rel_url'] . 'tag/' . $tags_abjad . '/index{PAGE}.html';
            $base_url_first = $this->config['rel_url'] . 'tag/' . $tags_abjad . '/';
        }

        $offset = ($page - 1) * 11;

        $list_all_tag = cache($cacheKey."-left_mobile_en_", function() use ($tags_abjad, $offset){
            $rows = TagNews::whereHas('News', function ($q){
                        $q->where('news_level', '1');
                        $q->where('news_date_publish', '<', date('Y-m-d H:i:s'));
                        $q->where('news_domain_id', $this->config['domain_id']);
                        $q->orderBy('news_date_publish', 'DESC');
                    })
                    ->whereHas('Tag', function ($q) use ($tags_abjad) {
                        if ($tags_abjad == '')
                        {

                        }
                        elseif (strpos($tags_abjad, 'index') !== false)
                        {

                        }
                        elseif ($tags_abjad == 'num')
                        {
                            $q->where('tag_url', 'REGEXP', '^[[:digit:]].*$');
                        }
                        else
                        {
                            $q->where('tag_url', 'LIKE', $tags_abjad ."%");
                        }
                    });
            $total = clone $rows;
            $total = $total->select(DB::raw("COUNT(DISTINCT tag_news_tag_id) as total"))->first();
            $total = $total->total;

            $rows = $rows->groupBy('tag_news_tag_id')
                    ->orderBy('tag_news_id', 'DESC')
                    ->with(['News' => function ($q) {
                        $q->select('news_id', 'news_category', 'news_title', 'news_url',
                                    'news_date_publish', 'news_type', 'news_image',
                                    'news_entry', 'news_synopsis');
                    }])
                    ->with('Tag')
                    ->take(11)
                    ->skip($offset)
                    ->get();

            return ['rows' => $rows, 'total' => $total];
        }, 3600);

        // init for meta_index_tag
        $this->data_meta_index_tag = $list_all_tag['rows']->toArray();

        $pConfig = array(
            'total_rows'        => $list_all_tag['total'],
            'page'              => $page,
            'per_page'          => 11,
            'total_side_link'   => 4,
            'go_to_page'        => false,
            'next'              => "Next",
            'previous'          => "Prev",
            'first'             => "",
            'last'              => "",
            'reverse_paging'    => false,
            'query_string'      => false,
            'base_url'          => $url_pagination,
            'base_url_first'    => $base_url_first,
        );

        $this->table->set_pagination($pConfig);

        $q_list_tag_left = $list_all_tag['rows']->toArray();

        if (count($q_list_tag_left) < 1)
        {
            $ret['PESAN'] = "Maaf, belum ada tag di halaman ini";
        }
        else
        {
            $no = 0;
            foreach ($q_list_tag_left as $data)
            {
                $initial_tag    = substr(strtolower($data['tag']["tag_url"]), 0, 1);
                $category_meta  = $this->_category($data['news']['news_category']);
                $category       = $category_meta['CATEGORY_URL'];

                if ($data['news']['news_type'] == 0)
                {
                    $url_type = $category;
                }
                elseif ($data['news']['news_type'] == 1)
                {
                    $url_type = 'photo/' . $category;
                }
                else
                {
                    $url_type = 'video/' . $category;
                }

                $datetime       = explode(" ", $data['news']['news_entry']);
                $datetime_clear = explode("-", $datetime[0]);
                $year   = $datetime_clear[0];
                $month  = $datetime_clear[1];
                $date   = $datetime_clear[2];

                $data_list_left['TAG_LINK_URL']     = $this->config['rel_url'] . 'tag/' . $initial_tag . '/' . $data['tag']['tag_url'] . '/';
                $data_list_left['TAGS_NEWS_TAGS']   = ucfirst($data['tag_news_tags']);
                $data_list_left['NEWS_TITLE']       = $data['news']['news_title'];
                $data_list_left['NEWS_URL']         = $this->config['rel_url'] . $url_type . '/' . $data['news']['news_url'] . '.html';
                $data_list_left['NEWS_IMG']         = $this->config['klimg_url'] . 'news' . '/' . $year . '/' . $month . '/' . $date . '/' . $data['tag_news_news_id'] . '/104x70-' . $data['news']['news_image'];

                $list_tags_list[$no]['LIST_LEFT']   = $data_list_left;

                // FUNCTION LIST RIGHT
                $q_list_tag_right = cache($cacheKey.'-right-'.$no, function() use ($data){
                    return newsByTag(strtolower($data['tag']['tag_url']), $this->config['mongo_prefix'], 1, 3);
                });

                // init for data_list_right is empty array
                $right_list = [];

                if($q_list_tag_right['total'] > 1)
                {
                    foreach ($q_list_tag_right['data'] as $data_right)
                    {
                        // skip same
                        if($data_right['news_title'] != $data['news']['news_title'] )
                        {
                            $category_meta = $this->_category($data_right['news_category']);

                            $category_right = $category_meta['CATEGORY_URL'];

                            if ($data_right['news_type'] == 0)
                            {
                                $url_type_fr = $category_right;
                            }
                            elseif ($data_right['news_type'] == 1)
                            {
                                $url_type_fr = 'photo/' . $category_right;
                            }
                            else
                            {
                                $url_type_fr = 'video/' . $category_right;
                            }

                            $data_list_right['NEWS_TITLE']    = $data_right['news_title'];
                            $data_list_right['NEWS_URL']      = $this->config['rel_url'] . $url_type_fr . '/' . $data_right['news_url'] . '.html';
                            if ( is_numeric($data_right['news_date_publish']) )
                            {
                              $data_list_right['NEWS_TIME']     = $this->lib_date->mobile_waktu(date("Y-m-d H:i:s", $data_right['news_date_publish']));
                            }
                            else
                            {
                              $data_list_right['NEWS_TIME']     = $data_right['news_date_publish'];
                            }

                            $right_list[] = $data_list_right;
                        }
                    }
                }

                $list_tags_list[$no]['LIST_RIGHT']   = $right_list;

                $no++;
            }

            $list_tags_list['PAGINATION'] = $this->table->link_pagination_beta($TE);
            $ret = $list_tags_list;
        }

        $interval = WebCache::App()->get_config('cachetime_short');
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    function all_tags_index($TE, $tags_abjad, $halaman) {

        $cacheKey = 'box_index_tag_mobile-' . $tags_abjad . '-' . $halaman;

        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        $page_full = explode("index", $tags_abjad);

        if (empty($page_full[1]))
        {
            $filter_name = $tags_abjad;
            $halaman_url = explode("index", $halaman);
            if (empty($halaman_url[1]))
            {
                $page = 1;
            }
            else
            {
                $hide_index_halaman = $halaman_url[1];
                $filter_halaman = explode(".html", $hide_index_halaman);
                if (empty($filter_halaman[0]))
                {
                    $page = 1;
                }
                else
                {
                    $page = $filter_halaman[0];
                }
            }
        }
        else
        {
            $filter_name = '';
            $hide_index = $page_full[1];
            $filter = explode(".html", $hide_index);
            if (empty($filter[0]))
            {
                $page = 1;
            }
            else
            {
                $page = $filter[0];
            }
        }

        $base_url   = $this->config['rel_url'];
        $img_url    = $this->config['assets_image_url'];

        //pagination controll
        if (empty($filter_name))
        {
            $url_pagination = $this->config['rel_url'] . 'tag/index{PAGE}.html';
            $base_url_first = $this->config['rel_url'] . 'tag/';
        }
        else
        {
            $url_pagination = $this->config['rel_url'] . 'tag/' . $tags_abjad . '/index{PAGE}.html';
            $base_url_first = $this->config['rel_url'] . 'tag/' . $tags_abjad . '/';
        }

        $sql_index_all_tag = TagNews::groupBy('tag_news_tag_id')
                ->orderBy('tag_news_id', 'DESC')->whereHas('News', function ($q) use ($tags_abjad){
                    $q->where('news_level', '1');
                    $q->where('news_date_publish', '<=',date('Y-m-d H:i:s'));
                    $q->where('news_domain_id', $this->config['domain_id']);
                })
                ->with('News')
                ->with('Tag')
                ->get()
                ->toArray();

        $pConfig = array(
            'total_rows' => $this->tag_news_model->index_total_tags($filter_name),
            'page' => $page,
            'per_page' => 3,
            'total_side_link' => 4,
            'go_to_page' => false,
            'next' => "Next",
            'previous' => "Prev",
            'first' => "",
            'last' => "",
            'reverse_paging' => false,
            'query_string' => false,
            'base_url' => $url_pagination,
            'base_url_first' => $base_url_first,
        );

        $this->table->set_pagination($pConfig);

        $offset = ($page - 1) * $pConfig['per_page'];
        $limit = 1;

        $all_tags = $this->tag_news_model->index_list_tag($filter_name, $offset, $limit);

        if (count($all_tags) == 0) {
            $ret['PESAN'] = "Maaf, belum ada tag di halaman ini";
        }
        else {

            $num = 1;
            $records = '';

            if ($tags_abjad == '' || $page == '') {
                $no = 1;
            } else {
                $no = 1 * 100;
            }

            $list_all_tags['LIST_ALL_TAGS'] = '';
            $list_tags_list['LIST_ALL_TAGS'] = '';
            $list_tags_list['LIST_TAG_FIRST'] = '';
            $tag_news_tag_id = '';
            $no = 1;
            if ($all_tags) {
                foreach ($all_tags as $a) {
                    //BERITA
                    $huruf_awal_tags = substr(strtolower($a["tag_url"]), 0, 1);
                    $fl["ID"] = $a["tag_news_tag_id"];
                    $fl["TAG"] = ucwords($a["tag_news_tags"]);
                    $fl["TAG_LINK_URL"] = $this->config['rel_url'] . 'tag/' . $huruf_awal_tags . '/' . $a['tag_url'] . '/';

                    $get_news_tags_left = $this->tag_news_model->get_news_tags_last($a['tag_news_tag_id'], 1);

                    $category_meta = $this->_category($get_news_tags_left['news_category']);
                    $category = $category_meta['CATEGORY_URL'];

                    if ($get_news_tags_left['news_type'] == 0) {
                        $url_type = $category;
                    } elseif ($get_news_tags_left['news_type'] == 1) {
                        $url_type = 'photo/' . $category;
                    } else {
                        $url_type = 'video/' . $category;
                    }

                    //LAST_NEWS_TAGS
                    $fl['TAGS_NEWS_NEWS_ID'] = $get_news_tags_left['tag_news_news_id'];
                    $fl['TAGS_NEWS_TAGS'] = ucfirst($get_news_tags_left['tag_news_tags']);
                    $fl['NEWS_TITLE'] = $get_news_tags_left['news_title'];
                    $fl['NEWS_URL'] = $this->config['rel_url'] . $url_type . '/' . $get_news_tags_left['news_url'] . '.html';
                    $fl['NEWS_ENTRY'] = $get_news_tags_left['news_entry'];

                    $datetime = explode(" ", $get_news_tags_left['news_entry']);
                    $datetime_clear = explode("-", $datetime[0]);
                    $year = $datetime_clear[0];
                    $month = $datetime_clear[1];
                    $date = $datetime_clear[2];

                    $fl['NEWS_IMG'] = $this->config['klimg_url'] . 'news' . '/' . $year . '/' . $month . '/' . $date . '/' . $get_news_tags_left['tag_news_news_id'] . '/104x70-' . $get_news_tags_left['news_image'];

                    $b['NEWS_LIST_LEFT'] = $fl;


                    $news_right_list = '';
                    $get_news_tags_right = $this->tag_news_model->get_news_tags($a['tag_news_tag_id'], $get_news_tags_left['tag_news_news_id'], 3);
                    foreach ($get_news_tags_right as $d) {
                        $category_meta = $this->_category($d['news_category']);

                        $category_right = $category_meta['CATEGORY_URL'];

                        if ($d['news_type'] == 0) {
                            $url_type_fr = $category_right;
                        } elseif ($d['news_type'] == 1) {
                            $url_type_fr = 'photo/' . $category_right;
                        } else {
                            $url_type_fr = 'video/' . $category_right;
                        }
                        $fr['NEWS_TITLE_RIGHT'] = $d['news_title'];
                        $fr['NEWS_URL'] = $this->config['rel_url'] . $url_type_fr . '/' . $d['news_url'] . '.html';
                        $fr['NEWS_TIME_RIGHT'] = $this->lib_date->mobile_waktu($d['news_date_publish']);
                        $fr['ASSETS_IMAGE_URL'] = $this->config['assets_image_url'];
                        $news_right_list[] = $fr;
                    }
                    $b['NEWS_LIST_RIGHT'] = $news_right_list;

                    $list_tags_list['LIST_TAG_FIRST'][] = $b;
                    $tag_news_tag_id = $a['tag_news_tag_id'];
                }

                //ALPA
                $s = 'a';
                $list_alpabet['LIST_ALPABET'] = '';
                while ($s <= 'z') {
                    if ($tags_abjad == '') {
                        $c['TAGS_AKTIF_A'] = '';
                        $c['TAGS_AKTIF_B'] = '';
                    } else {
                        if ($tags_abjad == $s) {
                            $c['TAGS_AKTIF_A'] = "select_tag";
                            $c['TAGS_AKTIF_B'] = '';
                        } else {
                            $c['TAGS_AKTIF_A'] = '';
                            $c['TAGS_AKTIF_B'] = '';
                        }
                    }
                    $c['ALPHABET'] = $s;
                    $s = chr(ord($s) + 1);
                    $list_alpabet['LIST_ALPABET'][] = $c;
                }
                if ($tags_abjad == '') {
                    $list_alpabet['TAGS_AKTIF_A'] = '';
                    $list_alpabet['TAGS_AKTIF_B'] = 'select_tag';
                } else {
                    $list_alpabet['TAGS_AKTIF_A'] = '';
                    $list_alpabet['TAGS_AKTIF_B'] = '';
                }

                $list_tags_list['LIST_ALPABET'][] = $list_alpabet;


                $list_tags_list['LIST_TAG_SECOND'] = '';

                $offset_second = 0;
                $limit_second = 10;
                $query_list_tag_second = $this->tag_news_model->index_list_tag_second($filter_name, $tag_news_tag_id, $offset, $limit_second);
                foreach ($query_list_tag_second as $a) {
                    //BERITA
                    $huruf_awal_tags = substr(strtolower($a["tag_url"]), 0, 1);
                    $fl["ID"] = $a["tag_news_tag_id"];
                    $fl["TAG"] = ucwords($a["tag_news_tags"]);
                    $fl["TAG_LINK_URL"] = $this->config['rel_url'] . 'tag/' . $huruf_awal_tags . '/' . $a['tag_url'] . '/';

                    $get_news_tags_left = $this->tag_news_model->get_news_tags_last($a['tag_news_tag_id'], 1);

                    $category_meta = $this->_category($get_news_tags_left['news_category']);

                    $category = $category_meta['CATEGORY_URL'];

                    if ($get_news_tags_left['news_type'] == 0) {
                        $url_type = $category;
                    } elseif ($get_news_tags_left['news_type'] == 1) {
                        $url_type = 'photo/' . $category;
                    } else {
                        $url_type = 'video/' . $category;
                    }

                    //LAST_NEWS_TAGS
                    $fl['TAGS_NEWS_NEWS_ID'] = $get_news_tags_left['tag_news_news_id'];
                    $fl['TAGS_NEWS_TAGS'] = ucfirst($get_news_tags_left['tag_news_tags']);
                    $fl['NEWS_TITLE'] = $get_news_tags_left['news_title'];
                    $fl['NEWS_URL'] = $this->config['rel_url'] . $url_type . '/' . $get_news_tags_left['news_url'] . '.html';
                    $fl['NEWS_ENTRY'] = $get_news_tags_left['news_entry'];

                    $datetime = explode(" ", $get_news_tags_left['news_entry']);
                    $datetime_clear = explode("-", $datetime[0]);
                    $year = $datetime_clear[0];
                    $month = $datetime_clear[1];
                    $date = $datetime_clear[2];

                    $fl['NEWS_IMG'] = $this->config['klimg_url'] . 'news' . '/' . $year . '/' . $month . '/' . $date . '/' . $get_news_tags_left['tag_news_news_id'] . '/104x70-' . $get_news_tags_left['news_image'];

                    $b['NEWS_LIST_LEFT'] = $fl;


                    $news_right_list = '';
                    $get_news_tags_right = $this->tag_news_model->get_news_tags($a['tag_news_tag_id'], $get_news_tags_left['tag_news_news_id'], 3);
                    foreach ($get_news_tags_right as $d) {
                        $category_meta = $this->_category($d['news_category']);

                        $category_right = $category_meta['CATEGORY_URL'];

                        if ($d['news_type'] == 0) {
                            $url_type_fr = $category_right;
                        } elseif ($d['news_type'] == 1) {
                            $url_type_fr = 'photo/' . $category_right;
                        } else {
                            $url_type_fr = 'video/' . $category_right;
                        }
                        $fr['NEWS_TITLE_RIGHT'] = $d['news_title'];
                        $fr['NEWS_URL'] = $this->config['rel_url'] . $url_type_fr . '/' . $d['news_url'] . '.html';
                        $fr['NEWS_TIME_RIGHT'] = $this->lib_date->mobile_waktu($d['news_date_publish']);
                        $fr['ASSETS_IMAGE_URL'] = $this->config['assets_image_url'];
                        $news_right_list[] = $fr;
                    }
                    $b['NEWS_LIST_RIGHT'] = $news_right_list;

                    $list_tags_list['LIST_TAG_SECOND'][] =  $b;
                }

                $list_tags_list['PAGINATION'] = $this->table->link_pagination_beta($TE);
                $ret = $list_tags_list;
            }

        }
        $interval = WebCache::App()->get_config('cachetime_short');

        setCache($cacheKey, $ret, $interval);

         return $ret;
    }

}
