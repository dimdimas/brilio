<?php

class TagNameController extends CController {

    private $_id_cat;
    private $share_url          = '';
    private $data_meta_index    = '';

    function __construct()
    {
        parent::__construct();
        $this->model(array('news_model', 'tags_model', 'jsview_model', 'what_happen_model', 'tag_news_model', 'today_tags_model'));
        $this->library(array('table', 'lib_date'));
        $this->model(['News','Tag', 'TagNews', 'SponsorTag'],null, true);
        $this->helper('mongodb');
    }

    function index($tags_abjad = '', $tags_nama = '', $halaman = ''){
        $interval = WebCache::App()->get_config('cachetime_default');
        $cacheKey = 'desktop_index_tagname_'.$tags_abjad.'_'.$tags_nama.'_'.$halaman;
        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        // WebCache::App()->start_cache('mongocache');
        $TE = 'Tag pages'; //
        $check_halaman = preg_match('/index(\d)|.html+/', $halaman);


        if (strpos($halaman, '-') !== false)
        {
            //condition where there a special news, promote article
            $keyword = str_replace('.html', '', $halaman);
            $news =$this->get_tag_news_special_headline($tags_nama, $keyword);
            $halaman = 1 ;
        }
        else
        {
            $halaman = preg_replace('/index|.html+/', '', $halaman);
            $get_news = [];
            if ( $halaman == 1 || empty($halaman) )
            {
                $news = newsByTag($tags_nama, $this->config['mongo_prefix'], 1, 26);
            }
            else
            {
                if ( !is_numeric($halaman)) {
                    Output::App()->show_404();
                }
                $news = newsByTag($tags_nama, $this->config['mongo_prefix'], $halaman, 26);
            }
        }

        //check no data
        if ( empty( $news['total']) | empty( $news['data']) )  {
            Output::App()->show_404();
        }

        $_dump = [];
        $_dump = $this->generate_news_url($news['data']);

        //generate image
        foreach ($_dump as $k => $v) {
            $_dump[$k]['news_image_location_255_170']      = $this->config['klimg_url'].$v['news_image_location_raw'].'/278x185-'. $v['news_image'];
        }

        if ( $halaman == 1  || empty($halaman) )
        {
            if (empty($keyword) || empty($halaman))
            {
                $list_tag['list_tag_first'] = array_slice(($_dump), 0,2);
                $list_tag['list_tag_second'] = array_slice($_dump, 2, count($_dump));
                $pagination_page = 26;
            }
            else
            {
                $list_tag['list_tag_first'] = array_slice(($_dump), 0,2);
                $list_tag['list_tag_second'] = array_slice($_dump, 2, count($_dump));
                $pagination_page = 26;
            }

        }
        else
        {
            $list_tag['list_tag_first'] = '';
            $list_tag['list_tag_second'] = $_dump;
            $pagination_page = 26;
        }

        if ( !empty( $keyword) ) {
            $tmp = $_dump[0];
            $var = $_dump[1];
        }
        else
        {
            $tmp = $_dump[0];
            $var = $_dump[0];
        }


        if ( $halaman == 1 || empty($halaman) )
        {
            if (!empty($keyword))
                $data_meta_tags['tag_title'] = $tmp['news_title'];
            else
                $data_meta_tags['tag_title'] = str_replace('-', ' ', ucwords($tags_nama));
        }
        else
        {
            $data_meta_tags['tag_title'] = str_replace('-', ' ', ucwords($tags_nama)). ' - halaman ' . $halaman;
        }
        $data_meta_tags['url_full'] = $this->config['base_url'] . substr($tmp['news_url_full'], strlen($this->config['rel_url']));
        $data_meta_tags['image_url_full'] = $tmp['news_image_location_full'];
        $data_meta_tags['news_synopsis'] = $tmp['news_synopsis'];
        $data_meta_tags['news_entry'] = $tmp['news_date_publish_indo'];
        //EOF meta tags


        $breadcrumb['tag_alphabet'] = $var['tag_alphabet'];
        $breadcrumb['tag_title'] = $var['tag_name'];
        $pConfig = array(
            'total_rows'      => $news['total'],
            'page'            => $halaman,
            'per_page'        => $pagination_page,
            'total_side_link' => 5,
            'go_to_page'      => false,
            'next'            => "Next",
            'previous'        => "Prev",
            'first'           => "",
            'last'            => "",
            'reverse_paging'  => false,
            'query_string'    => false,
            'base_url'        => $this->config['base_url'] . 'tag/' . $tags_abjad . '/' . $tags_nama . '/index{PAGE}.html',
            'base_url_first'  => $this->config['base_url'] . 'tag/' . $tags_abjad . '/' . $tags_nama . '/',
        );
        $this->table->set_pagination($pConfig);


     	$meta =
        [
            'meta_title'        => $data_meta_tags['tag_title']. ' | Brilio.net' ,
            'meta_description'  => isset($data_meta_tags['news_synopsis'])? $data_meta_tags['news_synopsis'] :'Tag dan topik populer tentang segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan',
            // 'og_url'            => $data_meta_tags['url_full'],
            'og_url'            => 'https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'],
            'og_image'          => 'http://cdn.klimg.com/newshub.id/' . substr($data_meta_tags['image_url_full'], strlen($this->config['klimg_url'])),
            'og_image_secure'   => $data_meta_tags['image_url_full'],
            'expires'           => date(DATE_RFC1036),
            'meta_keywords'     => str_replace(' ', ', ', $data_meta_tags['news_synopsis']),
            'last_modifed'      => date("D,j M Y G:i:s T", strtotime($data_meta_tags['news_entry'])),
            'img_url'           => $this->config['assets_image_url'],
            'chartbeat_sections'=> 'Popular Tags and Topic',
            'chartbeat_authors' => 'Brilio.net',
            'meta_alternate'    => $this->config['m_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        ];

        $data =
        [
            'meta'              => $meta,
            'url'               => $data_meta_tags['url_full'], //untuk social share
            'nama_halaman'      => 'Popular Tags and Topic',
            'TE'                => $TE,
            'full_url'          => $data_meta_tags['url_full'],
            'breadcrumb'        => $breadcrumb,
            'list_tag'          => $list_tag,
            'carousel'          => $this->carousel($var['tag_id']),
            'share_story'       => $this->view('desktop/box/_email', [], TRUE),
            'trending'          => $this->view('desktop/box/right_trending', $this->_trending(7, $TE), TRUE),
            'check_this_out'    => $this->view('/desktop/box/right_check_this_out', $this->_check_this_out($TE, 8), TRUE),
            'bottom_menu'       => $this->view('desktop/box/bottom_menu', ['TE' => $TE ], TRUE),
            'pagination'        => $this->table->link_pagination_tags_name($tags_nama),
        ];

        $ret = $this->_render('desktop/tag/tagname', $data);

        // WebCache::App()->stop_cache('mongocache');
        setCache($cacheKey, $ret, $interval);
        return $ret;
    }


    public function get_tag_news_special_headline($tags_nama, $keyword){
        $interval = WebCache::App()->get_config('cachetime_default');
        $cacheKey = 'desktop_index_tagname_headline_special_'.$keyword;
        if ($ret = checkCache($cacheKey))
        {
            return unserialize( $ret ) ;
        }

        $get_news = cache("desktop_query_".$cacheKey, function() use($keyword)
            {
                return News::join('tag_news', 'tag_news.tag_news_news_id', '=', 'news.news_id')
                            ->join('tags','tags.id','=','tag_news.tag_news_tag_id')
                            ->where('news_domain_id', '=' , $this->config['domain_id'])
                            ->where('news_level', '=' ,'1')
                            ->where('news_url', $keyword)
                            ->get()->toArray();
            }, $interval);

        if ( empty($get_news[0]) )  {
            Output::App()->show_404();
        }

        $get_news = $this->generate_news_url($get_news);
        $get_news[0]['tag_alphabet'] = $get_news[0]['tag_url']{0};
        $get_news[0]['tag_id'] = $get_news[0]['tag_news_tag_id'] ;

        //ambil data untuk checking
        $temp_news = newsByTag($tags_nama, $this->config['mongo_prefix'], 1, 20);

        //remove duplicate in first page
        $flag = 0;
        $_tmp_check = [];
        foreach ($temp_news['data'] as $key => $val)
        {
            $_tmp_check[$key] = $temp_news['data'][$key];
            if ($get_news[0]['news_id'] == $val['news_id'])
            {
                unset($_tmp_check[$key]);
                $flag = 1; //flag untuk menandakan di halaman pertama ada data news yang sama dengan 'custom' headline
            }
        }

        if ( $flag == 1)
        {

            $news = newsByTag($tags_nama, $this->config['mongo_prefix'], 1, 26);
            $_tmp = [];
            foreach ($news['data'] as $key => $val)
            {
                $_tmp[$key] = $news['data'][$key];
                if ($get_news[0]['news_id'] == $val['news_id'])
                {
                    unset($_tmp[$key]);

                }
            }

            $news['data'] = $_tmp;
            array_unshift($news['data'], reset($get_news)); //add news headline into list
        }
        else
        {
            $news = newsByTag($tags_nama, $this->config['mongo_prefix'], 1, 26);
            array_unshift($news['data'], reset($get_news));
        }


        $ret = $news;
        $interval = WebCache::App()->get_config('cachetime_short');
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    function carousel($tag_news_tag_id)
    {

        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'desktop_carousel_index_tagname_';

        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $q_carousel = cache("desktop_query_".$cacheKey, function() use($tag_news_tag_id) {
            return News::join('tag_news', 'news_id', '=', 'tag_news_news_id')
                    ->join('tags','tags.id','=','tag_news.tag_news_tag_id')
                    ->where('news_domain_id', '=', $this->config['domain_id'])
                    ->where('news_level', '=', '1')
                    ->where('news_editor_pick', '=', '1')
                    ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                    ->whereNotIn('tag_news.tag_news_tag_id', [$tag_news_tag_id])
                    ->orderBy('news_date_publish', 'DESC')
                    ->groupBy('news_id')
                    ->take(13)
                    ->get()
                    ->toArray();
        }, $interval);

        $carousel = $this->generate_news_url($q_carousel);

        $ret = $carousel;
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    function sponsor_brand($tags_abjad = '', $tags_nama = '', $halaman = ''){
        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'desktop_index_sponsor_brand_'.$tags_abjad.'_'.$tags_nama.'_'.$halaman;
        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        $TE = 'Brand pages'; //
        $check_halaman = preg_match('/index(\d)|.html+/', $halaman);

        //get data sponsor tag
        $data_sponsor_tag = SponsorTag::where('url', $tags_nama)
                            ->get()
                            ->first();

        if($data_sponsor_tag)
        {
            $val = $data_sponsor_tag->toArray();
            $split_time                   = explode(' ', $val['created_at']);
            $entry                        = str_replace('-', '/', $split_time[0]);
            $firt_alpabhet                = substr(url_title($val['tag']), 0,1);
            $sponsor_temp['id']           = $val['id'];
            $sponsor_temp['brand_name']   = $val['brand_name'];
            $sponsor_temp['brand_url']    = $val['brand_url'];
            $sponsor_temp['tag_name']     = $val['tag'];
            $sponsor_temp['tag_alphabet'] = $firt_alpabhet;
            $sponsor_temp['tag_brand_url']      = $this->config['rel_url'].'brands/'.$firt_alpabhet.'/'.$val['url'];
            $sponsor_temp['brand_image']        = $this->config['klimg_url']. 'tag-sponsorship/'. $entry.'/'. $val['id'].'/'.$val['image'] ;

            if ($tags_abjad!=$firt_alpabhet)
            {
                echo '<meta http-equiv="Refresh" content="0;URL=' .$sponsor_temp['tag_brand_url']. '">';
                exit;
            }
        }
        else
            Output::App()->show_404();


        $halaman = preg_replace('/index|.html+/', '', $halaman);

        $news = News::where('news_sponsorship', $data_sponsor_tag->id)
                    ->where('news_level', 1)
                    ->where('news_domain_id', $this->config['domain_id'])
                    ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                    ->with('sponsor_tag');

        $count_news = clone $news;
        $count_news = $count_news->count();

        if ( $halaman == 1 || empty($halaman) )
        {
            $per_pages = 26;
            $offset = 0;

            $news = cache("desktop_query_news_sponsor_brand_".$cacheKey."_".$per_pages."_".$offset, function() use($news, $per_pages, $offset) {
                return $news->with('news_tag_list')
                        ->skip($offset)
                        ->take($per_pages)
                        ->orderBy('news_date_publish', 'desc')
                        ->get()
                        ->toArray();
            }, $interval);

        }
        else
        {
            $per_pages = 26;
            $offset = ($halaman - 1) * $per_pages;

            $news = cache("desktop_query_news_sponsor_brand_".$cacheKey."_".$per_pages."_".$offset, function() use($per_pages, $offset, $news) {
                return $news->with('news_tag_list')
                        ->skip($offset)
                        ->take($per_pages)
                        ->orderBy('news_date_publish', 'desc')
                        ->get()
                        ->toArray();
            }, $interval);

            //get promote article in index brand
            if (strpos($halaman, '-') !== false)
            {
                //condition no index || special artikel promotion
                $keyword = str_replace('.html', '', $halaman);

                $promote_article = cache("desktop_query_news_sponsor_brand_".$keyword, function() use($keyword) {
                    return News::where('news_domain_id', '=' , $this->config['domain_id'])
                                ->where('news_level', '=' ,'1')
                                ->where('news_url', $keyword)
                                ->with('sponsor_tag')
                                ->with('news_tag_list')
                                ->get()->toArray();
                }, $interval);

                if ($promote_article)
                {
                    array_unshift($news, reset($promote_article));
                }
                else
                {
                    Output::App()->show_404();
                }
                //set pagination page
                $halaman = 1 ;
            }
        }


        //check no data
        if ( empty( $news ) )
            Output::App()->show_404();

        //generate news url
        $news = $this->generate_news_url($news);

        foreach ($news as $k => $v) {
            $news[$k]['news_image_location_255_170']      = $this->config['klimg_url'].$v['news_image_location_raw'].'/278x185-'. $v['news_image'];
        }
        // generate image

        if ( $halaman == 1  || empty($halaman) )
        {
            if (empty($keyword) || empty($halaman))
            {
                $list_tag['list_tag_first'] = array_slice(($news), 0,2);
                $list_tag['list_tag_second'] = array_slice($news, 2, count($news));
            }
            else
            {
                $list_tag['list_tag_first'] = array_slice(($news), 0,2);
                $list_tag['list_tag_second'] = array_slice($news, 2, count($news));
            }

        }
        else
        {
            $list_tag['list_tag_first'] = '';
            $list_tag['list_tag_second'] = $news;
        }

        if ( !empty( $keyword) ) {
            $tmp = $news[0];
            $var = $news[1];
        }
        else
        {
            $tmp = $news[0];
            $var = $news[0];
        }


        if ( $halaman == 1 || empty($halaman) )
        {
            // if (!empty($keyword))
            //     $data_meta_tags['tag_title'] = $tmp['news_title'];
            // else
                $data_meta_tags['tag_title'] = str_replace('-', ' ', ucwords($tags_nama));
        }
        else
        {
            $data_meta_tags['tag_title'] = str_replace('-', ' ', ucwords($tags_nama)). ' - halaman ' . $halaman;
        }

        $data_meta_tags['url_full']       = $this->config['base_url'] . substr($tmp['news_url_full'], strlen($this->config['rel_url']));
        $data_meta_tags['image_url_full'] = $tmp['news_image_location_full'];
        $data_meta_tags['news_synopsis']  = $tmp['news_synopsis'];
        $data_meta_tags['news_entry']     = $tmp['news_date_publish_indo'];
        //EOF meta tags

        $breadcrumb['tag_alphabet'] = $sponsor_temp['tag_alphabet'];
        $breadcrumb['tag_title']    = $sponsor_temp['tag_name'];
        $breadcrumb['brand_image']  = $sponsor_temp['brand_image'];
        $breadcrumb['brand_url']    = isset($sponsor_temp['brand_url']) ? $sponsor_temp['brand_url'] : '#';

        $pConfig = array(
            'total_rows'      => $count_news,
            'page'            => $halaman,
            'per_page'        => $per_pages,
            'total_side_link' => 5,
            'go_to_page'      => false,
            'next'            => "Next",
            'previous'        => "Prev",
            'first'           => "",
            'last'            => "",
            'reverse_paging'  => false,
            'query_string'    => false,
            'base_url'        => $this->config['base_url'] . 'brands/' . $tags_abjad . '/' . $tags_nama . '/index{PAGE}.html',
            'base_url_first'  => $this->config['base_url'] . 'brands/' . $tags_abjad . '/' . $tags_nama . '/',
        );
        $this->table->set_pagination($pConfig);

        $meta =
        [
            'meta_title'        => $data_meta_tags['tag_title']. ' | Brilio.net' ,
            'meta_description'  => isset($data_meta_tags['news_synopsis'])? $data_meta_tags['news_synopsis'] :'Tag dan topik populer tentang segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan',
            // 'og_url'            => $data_meta_tags['url_full'],
            'og_url'            => 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
            'og_image'          => 'http://cdn.klimg.com/newshub.id/' . substr($data_meta_tags['image_url_full'], strlen($this->config['klimg_url'])),
            'og_image_secure'   => $data_meta_tags['image_url_full'],
            'expires'           => date(DATE_RFC1036),
            'meta_keywords'     => str_replace(' ', ', ', $data_meta_tags['news_synopsis']),
            'last_modifed'      => date("D,j M Y G:i:s T", strtotime($data_meta_tags['news_entry'])),
            'img_url'           => $this->config['assets_image_url'],
            'chartbeat_sections'=> 'Popular Tags and Topic',
            'chartbeat_authors' => 'Brilio.net',
            'meta_alternate'    => $this->config['m_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        ];

        $data =
        [
            'meta'              => $meta,
            'url'               => $data_meta_tags['url_full'], //untuk social share
            'nama_halaman'      => 'Popular Tags and Topic',
            'TE'                => $TE,
            'full_url'          => $data_meta_tags['url_full'],
            'breadcrumb'        => $breadcrumb,
            'list_tag'          => $list_tag,
            'carousel'          => $this->carousel(!empty($var['news_tag_list'][0]['tag_news_tag_id']) ? $var['news_tag_list'][0]['tag_news_tag_id'] : $sponsor_temp['id']),
            'share_story'       => $this->view('desktop/box/_email', [], TRUE),
            'trending'          => $this->view('desktop/box/right_trending', $this->_trending(7, $TE), TRUE),
            'check_this_out'    => $this->view('/desktop/box/right_check_this_out', $this->_check_this_out($TE, 8), TRUE),
            'bottom_menu'       => $this->view('desktop/box/bottom_menu', ['TE' => $TE ], TRUE),
            'pagination'        => $this->table->link_pagination_tags_name($tags_nama),
        ];

        $ret = $this->_render('desktop/tag/tagbrand', $data);

        setCache($cacheKey, $ret, $interval);
        return $ret;
    }

}

?>
