<?php

use Illuminate\Database\Capsule\Manager as DB;

class TagController extends CController {

    private $share_url  = '';
    private $data_meta_index_tag = '';
    private $_page      = '';

    function __construct()
    {
        parent::__construct();
        $this->model(array('news_model', 'tags_model', 'jsview_model', 'what_happen_model', 'tag_news_model', 'today_tags_model'));
        $this->model(['TagNews', 'Tag', 'News'], null, true);
        $this->library(array('table', 'lib_date'));
        $this->helper('mongodb');
    }

    function index($tags_abjad = '', $halaman = '')
    {
        $url_filter = $_SERVER["REQUEST_URI"];
        $cacheKey   = 'desktop_tag_news_content-' . $url_filter . $halaman;
        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        $tag_filter     = '';

        if ( !empty($tags_abjad) )
        {
            $TE = 'Alphabetical tag';
        }
        else
        {
            $TE = 'Index all tags';
        }

        // GET ALL DATA INDEX TAGS
        $list_tag = $this->list_tag($TE, $tags_abjad, $halaman);

        if(count($list_tag) > 2)
        {
            $data_meta_tags = $list_tag[0]['LIST_LEFT'];
        }
        else
        {
            $data_meta_tags['NEWS_SYNOPSIS'] = 'Tag dan topik populer tentang segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan | Brilio.net';
            $data_meta_tags['NEWS_ENTRY']    = date('H:i:s');
        }

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


        $meta =
        [
            'meta_title'        => $meta_title,
            'meta_description'  => 'Tag dan topik populer tentang segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan | Brilio.net',
            'expires'           => date("D,j M Y G:i:s T", strtotime($data_meta_tags['NEWS_ENTRY'])),
            'meta_keywords'     => str_replace(' ', ', ', $data_meta_tags['NEWS_SYNOPSIS']). ' | Brilio.net',
            'og_url'            => /*isset($data_meta_tags['OG_URL']) ? $data_meta_tags['OG_URL'] : */'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
            'og_image'          => isset($data_meta_tags['NEWS_IMG']) ? 'http://cdn.klimg.com/newshub.id/'. substr($data_meta_tags['NEWS_IMG'], strlen($this->config['klimg_url'])) : $this->config['base_url'] . 'assets_v2/img/logo-atas.png',
            'og_image_secure'   => isset($data_meta_tags['NEWS_IMG']) ? 'https://cdns.klimg.com/newshub.id/'. substr($data_meta_tags['NEWS_IMG'], strlen($this->config['klimg_url'])) :$this->config['base_url'] . 'assets_v2/img/logo-atas.png',
            'img_url'           => $this->config['assets_image_url'],
            'last_modifed'      => date("D,j M Y G:i:s T", strtotime($data_meta_tags['NEWS_ENTRY'])),
            'chartbeat_sections'=> 'Popular Tags and Topic',
            'chartbeat_authors' => 'Brilio.net',
            'meta_alternate'    => $this->config['m_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        ];
        $data=
        [
            'meta'              => $meta,
            'url'               => $url,
            'nama_halaman'      => 'Popular Tags and Topic',
            'TE'                => $TE,
            'full_url'          => $url,
            'breadcrumb'        => $this->breadcumb($tags_abjad),
            'list_tag'          => $list_tag,
            'list_alphabet'     => $this->list_alphabet($TE, $tags_abjad, $halaman),
            'collect_email'     => $this->view('desktop/box/_email', [], TRUE),
            'editor_picks'      => $this->view('desktop/box/right_editors_pick', $this->_editor_picks("desktop-editor-picks-tag-index", $TE, 7), TRUE),
            'popular_tags'      =>$this->view('/desktop/box/right_popular_tags', $this->_popular_tags("desktop-popular-tags-tag-index", $TE), TRUE),
            'just_update'       => $this->view('desktop/box/right_just_update', $this->_just_update("desktop-just-update-tag-index", $TE), TRUE),
            'check_this_out'    => $this->view('/desktop/box/right_check_this_out', $this->_check_this_out($TE, 7), TRUE),
        ];

        $interval = WebCache::App()->get_config('cachetime_short');
        $ret      = $this->_render('desktop/tag/index_tag', $data);
        setCache($cacheKey, $ret, $interval);
        return $ret;
    }

    function breadcumb($tags_abjad)
    {
        $cacheKey = 'desktop_breadcrumb-tag'.$tags_abjad;
        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        if (empty($tags_abjad))
        {
            $view_breadcumb['LIST_BREADCUMB'] = '';
        }
        else
        {
            $page_full = explode("index", $tags_abjad);
            if (empty($page_full[1]))
            {
                if ($tags_abjad == '')
                {
                    $view_breadcumb['LIST_BREADCUMB'] = '';
                }
                else
                {
                    $data_breadcumb['BREADCUMB_SIMBOL'] = '&raquo;';
                    $data_breadcumb['BREADCUMB_TITLE']  = ucwords($tags_abjad);
                    $data_breadcumb['BREADCUMB_URL']    = $this->config['base_url'] . "tag/" . $tags_abjad . "/";
                    $view_breadcumb['LIST_BREADCUMB']   = $data_breadcumb;
                }
            }
            else
            {
                $view_breadcumb['LIST_BREADCUMB'] = '';
            }
        }

        $ret        = $view_breadcumb;
        $interval   = WebCache::App()->get_config('cachetime_short');
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
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
                    $c['TAGS_AKTIF'] = "active";
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
        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'desktop_list_tag_' . $tags_abjad . '_page_' . $halaman . $_SERVER['REQUEST_URI'];

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
                $filter_halaman     = explode(".html", $hide_index_halaman);
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
            $filter_name    = '';
            $hide_index     = $page_full[1];
            $filter         = explode(".html", $hide_index);
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



        $base_url   = $this->config['base_url'];
        $img_url    = $this->config['assets_image_url'];

        if (empty($filter_name))
        {
            $url_pagination = $this->config['base_url'] . 'tag/index{PAGE}.html';
            $base_url_first = $this->config['base_url'] . 'tag/';
        }
        else
        {
            $url_pagination = $this->config['base_url'] . 'tag/' . $tags_abjad . '/index{PAGE}.html';
            $base_url_first = $this->config['base_url'] . 'tag/' . $tags_abjad . '/';
        }

        $per_pages = 9;
        $offset = ($page - 1) * $per_pages;

//        WebCache::App()->start_cache('mongocache');
        $list_all_tag = cache("desktop_query_".$cacheKey."list_tag_box-".$tags_abjad.$page, function() use ($tags_abjad, $offset){
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
                    ->with('Tag')
                    ->take(9)
                    ->skip($offset)
                    ->get()
                    ->toArray();

            return ['rows' => $rows, 'total' => $total];
        }, $interval);


        $pConfig = array(
            'total_rows'        => $list_all_tag['total'],
            'page'              => $page,
            'per_page'          => $per_pages,
            'total_side_link'   => 5,
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

        $recent_tags = $list_all_tag['rows'];

        $ret['PESAN'] = '';

        if(empty($recent_tags))
        {
            $list_tags_list['PESAN'] = "Maaf, belum ada tag di halaman ini";
        }
        else
        {
            // create data that needed for "LEFT"
            $no = 0;
            foreach ($recent_tags as $data)
            {
                $all_data_news = cache($no."_desktop_query_".$cacheKey."news_by_tags", function () use ($data) {
                    return newsByTag($data['tag']['tag_url'], $this->config['mongo_prefix'], 1, 5);
                }, $interval);

                $news = $this->generate_news_url($all_data_news['data']);

                if(!empty($news))
                {
                    $data_list_left["TAG_LINK_URL"]     = $news[0]['tag_url_full'];
                    $data_list_left['TAGS_NEWS_TAGS']   = $news[0]['tag_name'];
                    $data_list_left['NEWS_URL']         = $news[0]['news_url_full'];
                    $data_list_left['NEWS_ENTRY']       = $news[0]['news_date_publish_indo'];
                    $data_list_left['NEWS_TITLE']       = $news[0]['news_title'];
                    $data_list_left['NEWS_IMG']         = $news[0]['news_image_location'] . '360x180-' . $news[0]['news_image_secondary'];
                    $data_list_left['OG_URL']           = $news[0]['news_url_with_base'];
                    $data_list_left['NEWS_SYNOPSIS']    = $news[0]['news_synopsis'];

                    $list_tags_list[$no]['LIST_LEFT']      = $data_list_left;

                    // init for data_list_right is empty array
                    $right_list = [];

                    // checking a right datas
                    // create data that needed for "RIGHT"
                    if(count($news) > 1)
                    {
                        $skip = 1;
                        foreach ($news as $data_right)
                        {
                            if($skip != 1)
                            {
                                $data_list_right['NEWS_TITLE_RIGHT']  = $data_right['news_title'];
                                $data_list_right['NEWS_URL']          = $data_right['news_url_full'];
                                $data_list_right['TAG_TITLE']         = $data_right['tag_name'];
                                if ( is_numeric($data_right['news_date_publish']) ) {
                                  $data_list_right['NEWS_TIME_RIGHT']   = $this->lib_date->indo(date("Y-m-d H:i:s", $data_right['news_date_publish']));
                                }
                                else {
                                  $data_list_right['NEWS_TIME_RIGHT'] = $data_right['news_date_publish'];
                                }

                                $right_list[] = $data_list_right;
                            }
                            $skip++;
                        }
                    }

                    $list_tags_list[$no]['LIST_RIGHT']      = $right_list;
                    if ($all_data_news['total'] <= 10)
                    {
                        $list_tags_list[$no]['TOTAL_NEWS']  = '';
                    }
                    else
                    {
                        $list_tags_list[$no]['TOTAL_NEWS'] = $all_data_news['total'] - 5;
                    }
                    $list_tags_list[$no]['MORE_UPDATE_URL'] = $news[0]['tag_url_full'];
                    $no++;
                }
            }

        }

        $list_tags_list['PAGINATION'] = $this->table->link_pagination_beta($TE);

        $ret = $list_tags_list;

        $interval = WebCache::App()->get_config('cachetime_short');
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    //SOSMET
    function sosmed()
    {
        $cacheKey = 'sosmed-tag-name'.  md5($this->share_url);
        if ($ret = checkCache($cacheKey)) return $ret;

        $share_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        if(!empty($this->share_url)) {
            $share_url = $this->share_url;
        }

        $sosmed_data['SOSMED_URL'] = $share_url;
        $ret = $this->view('box/_sosmed', $sosmed_data, true);
        $interval = WebCache::App()->get_config('cachetime_short');
        setCache($cacheKey, $ret, $interval);
        return $ret;
    }

}

?>
