<?php

class TagsController extends CController {


    function __construct()
    {
        parent::__construct();
        $this->model(['TagNews', 'SponsorTag','Tag', 'News'], null, true);
        $this->library(array('table', 'lib_date'));
        $this->helper('mongodb');
    }

    function index($tags_abjad = '', $tags_nama = '', $halaman = ''){

    	$cacheKey = 'mobile_name_index_tag_'.$tags_abjad."_".$tags_nama."_".$halaman;
        $interval = WebCache::App()->get_config('cachetime_short');
        if ($ret = checkCache($cacheKey))
        {
        	return $ret;
        }
        $temp_halaman = $halaman;
        $TE_1 = 'Menu';
        $TE_2 = $tags_nama;
        $TE_3 = 'Tag pages';

        if (strpos($halaman, '-') !== false)
        {
            //condition no index
            $keyword = str_replace('.html', '', $halaman);
            $news =$this->get_tag_news_special_headline($tags_nama, $keyword);
            $halaman = 1 ;
        }
        else
        {
            $halaman = preg_replace('/index|.html+/', '', $halaman);
            $get_news = [];
            if ($halaman == 1 || empty($halaman))
            {
                $news = newsByTag($tags_nama, $this->config['mongo_prefix'], 1, 6);
            }
            else
            {
                if ( !is_numeric($halaman)) {
                    Output::App()->show_404();
                }
                $news = newsByTag($tags_nama, $this->config['mongo_prefix'], $halaman, 5);
            }
        }

        //check no data
        if ( count($news['data']) == 0) {
            Output::App()->show_404();
        }

		$_dump = [];
		$no = 1;
        foreach($news['data'] as $key=>$val)
        {
            $_dump[$key] = $news['data'][$key];
            $first_char = $val['tag_alphabet'];
            $category_meta  = $this->_category($val['news_category']);
            $category       = $category_meta['CATEGORY_URL'];
            $datetime           = explode(" ", $val['news_entry']);
            $datetime_clear     = explode("-", $datetime[0]);
            $year               = $datetime_clear[0];
            $month              = $datetime_clear[1];
            $date               = $datetime_clear[2];

            if ($key['news_type'] == 0)
            {
                $url_type = $category;
            }
            elseif ($key['news_type'] == 1)
            {
                $url_type = 'photo/' . $category;
            }
            else
            {
                $url_type = 'video/' . $category;
            }

            $_dump[$key]['NO'] = $no;
            $_dump[$key]['news_category_fix'] = $category;
            $_dump[$key]['news_url_full'] = $this->config['rel_url'] . $url_type . '/' . $val['news_url'] . '.html';
            $_dump[$key]['news_entry_fix'] = $val['news_entry'];
            // $_dump[$key]['news_url_full'] = $this->config['base_url'] . $category .'/' . $val['news_url'] . '.html';
            $_dump[$key]['news_image_url_full'] =  $this->config['klimg_url'] . 'news' . '/' . $year . '/' . $month . '/' . $date . '/' . $val['news_id'] . '/360x180-' . $val['news_image_secondary'];
            $no++;
        }


        if ( $halaman == 1 || empty($halaman))
        {
            $tmp_dump = $_dump; //copy array,biar tidak conflict di data_meta_tags
            $list_tag['list_tag_first'][] = reset($tmp_dump) ;
            unset($tmp_dump[0]);
            $list_tag['list_tag_second'] = $tmp_dump;
            $pagination_per_page = 6;
        }
        else
        {
        	$list_tag['list_tag_first'] = '';
            $list_tag['list_tag_second'] = $_dump;
            $pagination_per_page = 5;
        }

        //function for meta tags,
        $tmp = $_dump[0];
        if ($halaman == 1 || empty($halaman) )
        {
            if (!empty($keyword))
                $data_meta_tags['tag_title'] = $tmp['news_title'];
            else
                $data_meta_tags['tag_title'] = str_replace('-', ' ', ucwords($tags_nama));
        }
        else
        {
            $data_meta_tags['tag_title'] = str_replace('-', ' ', ucwords($tags_nama)). ' - halaman '. $halaman;
        }
        $data_meta_tags['url_full'] = $this->config['www_url'] . substr($tmp['news_url_full'], strlen($this->config['rel_url'])) ;
        $data_meta_tags['image_url_full'] = $tmp['news_image_url_full'];
        $data_meta_tags['news_synopsis'] = $tmp['news_synopsis'];
        $data_meta_tags['news_entry'] = $tmp['news_entry'];
        //EOF meta tags

        $breadcrumb['tag_alphabet'] = substr($tags_nama, 0, 1);
        $breadcrumb['tag_title'] = str_replace('-', ' ', ucwords($tags_nama));
        //EOF meta tag

        $breadcrumb['tag_alphabet'] = $news['data'][0]['tag_alphabet'];
        $breadcrumb['tag_title'] = str_replace('-', ' ', $tags_nama);

        $pConfig = array(
            'total_rows'      => $news['total'],
            'page'            => $halaman,
            'per_page'        => $pagination_per_page,
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
            'meta_title'        => $data_meta_tags['tag_title'] . ' | Brilio.net',
            'meta_description'  => isset($data_meta_tags['news_synopsis'])? $data_meta_tags['news_synopsis'] :'Tag dan topik populer tentang segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan',
            // 'og_url'            => $data_meta_tags['url_full'],
            'og_url'            => 'https://'.$_SERVER['HTTP_HOST'].$this->config['rel_url'].'tag/'.$breadcrumb['tag_alphabet']."/".str_replace(' ', '-', $breadcrumb['tag_title']).'/'.$temp_halaman,
            'og_image'          => 'http://cdn.klimg.com/newshub.id/' . substr($data_meta_tags['image_url_full'], strlen($this->config['klimg_url'])),
            'og_image_secure'   => $data_meta_tags['image_url_full'],
            'expires'           => date(DATE_RFC1036),
            'meta_keywords'     => str_replace(' ', ', ', $data_meta_tags['news_synopsis']),
            'last_modifed'      => date("D,j M Y G:i:s T", strtotime($data_meta_tags['news_entry'])),
            'img_url'           => $this->config['assets_image_url'],
            'chartbeat_sections'=> 'Popular Tags and Topic',
            'chartbeat_authors' => 'Brilio.net',
            'meta_alternate'    => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        ];



        $data =
        [
    		'meta'                 => $meta,
    		'url'                  => $data_meta_tags['url_full'],
    		'nama_halaman'         => 'Popular Tags and Topic',
    		'TE_2'                 => $TE_1,
    		'full_url'             => $data_meta_tags['url_full'],
    		'breadcrumb'           => $breadcrumb,
    		'list_tag'             => $list_tag,
    		'pagination'           =>$this->table->link_pagination_tags_name(ucwords(strtolower($tags_nama)))
        ];


        $ret = $this->_mobile_render('mobile/tag/nametag', $data);

        //WebCache::App()->stop_cache('mongocache');
        setCache($cacheKey, $ret, $interval);
        return $ret;
    }

    public function get_tag_news_special_headline($tags_nama ,$keyword){

        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'mobile_name_index_tag_custom'.$keyword;
        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret) ;
        }

        $get_news = cache($cacheKey, function() use($keyword)
            {
                return News::select('news_id',
                                    'news_entry',
                                    'news_category',
                                    'news_title',
                                    'news_synopsis',
                                    'news_image',
                                    'news_image_thumbnail',
                                    'news_image_potrait',
                                    'news_url',
                                    'news_date_publish',
                                    'news_type')
                            ->where('news_domain_id', '=' , $this->config['domain_id'])
                            ->where('news_level', '=' ,'1')
                            ->where('news_url', $keyword)
                            ->get()->toArray();
            }, $interval );

        if ( empty($get_news[0]) )  {
            Output::App()->show_404();
        }

        //ambil data untuk checking
        $temp_news = newsByTag($tags_nama, $this->config['mongo_prefix'], 1, 20);

        //generate data needed for url
        $_dump = reset($get_news);
        $category_meta  = $this->_category($_dump['news_category']);
        $category       = $category_meta['CATEGORY_URL'];
        $first_char     = substr($tags_nama, 0, 1);
        $_dump['NO'] = 1;
        $_dump['tag_alphabet'] = $first_char;
        $_dump['news_image_secondary'] = $_dump['news_image_potrait'];
        $_dump['domain'] = $this->config['domain_id'];
        $get_news[0] = $_dump;
        //EOF generate url

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
            $news = newsByTag($tags_nama, $this->config['mongo_prefix'], 1, 6);
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
            $news = newsByTag($tags_nama, $this->config['mongo_prefix'], 1, 5);
            array_unshift($news['data'], reset($get_news));
        }


        $ret = $news;
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    function sponsor_brand($tags_abjad = '', $tags_nama = '', $halaman = ''){

        $cacheKey = 'mobile_name_sponsor_brand_'.$tags_abjad."_".$tags_nama."_".$halaman;
        $interval = WebCache::App()->get_config('cachetime_short');
        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        $TE_1 = 'Menu';
        $TE_2 = $tags_nama;
        $TE_3 = 'Brand pages';

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
            $per_pages = 6;
            $offset = 0;

            $news = cache("mobile_query_".$cacheKey."_".$per_pages."_".$offset, function() use($news, $per_pages, $offset) {
                return $news->with('news_tag_list')
                        ->skip($offset)
                        ->take($per_pages)
                        ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                        ->orderBy('news_date_publish', 'desc')
                        ->get()
                        ->toArray();
            }, $interval);

        }
        else
        {
            $per_pages = 5;
            $offset = ($halaman - 1) * $per_pages;

            $news = cache("mobile_query_".$cacheKey."_".$per_pages."_".$offset, function() use($per_pages, $offset, $news) {
                return $news->with('news_tag_list')
                        ->skip($offset)
                        ->take($per_pages)
                        ->where('news_date_publish', '<=', date('Y-m-d H:i:s'))
                        ->orderBy('news_date_publish', 'desc')
                        ->get()
                        ->toArray();
            }, $interval);

            //get promote article in index brand
            if (strpos($halaman, '-') !== false)
            {
                //condition no index || special artikel promotion
                $keyword = str_replace('.html', '', $halaman);

                $promote_article = cache("mobile_query_news_sponsor_brand_".$keyword, function() use($keyword) {
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
        if ( empty($news) ) {
            Output::App()->show_404();
        }

        //generate news
        $news = $this->generate_news_url($news);

        $no = 1;
        foreach($news as $key=>$val)
        {
            $news[$key]['NO'] = $no;
            $news[$key]['news_image_url_full'] =  $val['news_image_location'] . '/320xauto-' . $val['news_image'];
            $no++;
        }

        if ( $halaman == 1 || empty($halaman))
        {
            $tmp_dump = $news; //copy array,biar tidak conflict di data_meta_tags
            $list_tag['list_tag_first'][] = reset($tmp_dump) ;
            unset($tmp_dump[0]);
            $list_tag['list_tag_second'] = $tmp_dump;
        }
        else
        {
            $list_tag['list_tag_first'] = '';
            $list_tag['list_tag_second'] = $news;
        }

        //function for meta tags,
        $tmp = $news[0];
        if ($halaman == 1 || empty($halaman) )
        {
            // if (!empty($keyword))
            //     $data_meta_tags['tag_title'] = $tmp['news_title'];
            // else
                $data_meta_tags['tag_title'] = str_replace('-', ' ', ucwords($sponsor_temp['tag_name']));
        }
        else
        {
            $data_meta_tags['tag_title'] = str_replace('-', ' ', ucwords($tags_nama)). ' - halaman '. $halaman;
        }
        $data_meta_tags['url_full']       = $this->config['www_url'] . substr($tmp['news_url_full'], strlen($this->config['rel_url'])) ;
        $data_meta_tags['image_url_full'] = $tmp['news_image_url_full'];
        $data_meta_tags['news_synopsis']  = $tmp['news_synopsis'];
        $data_meta_tags['news_entry']     = $tmp['news_entry'];
        //EOF meta tags

        //EOF meta tag

        $breadcrumb['tag_alphabet'] = $sponsor_temp['tag_alphabet'];
        $breadcrumb['tag_title']    = str_replace('-', ' ', $sponsor_temp['tag_name']);
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
            'meta_title'        => $data_meta_tags['tag_title'] . ' | Brilio.net',
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
            'meta_alternate'    => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        ];

        $data =
        [
            'meta'                 => $meta,
            'url'                  => $data_meta_tags['url_full'],
            'nama_halaman'         => 'Popular Tags and Topic',
            'TE'                   => $TE_3,
            'TE_2'                 => $TE_2,
            'full_url'             => $data_meta_tags['url_full'],
            'breadcrumb'           => $breadcrumb,
            'list_tag'             => $list_tag,
            'pagination'           =>$this->table->link_pagination_tags_name(ucwords(strtolower($tags_nama)))
        ];


        $ret = $this->_mobile_render('mobile/tag/tagbrand', $data);

        setCache($cacheKey, $ret, $interval);
        return $ret;
    }
}

?>
