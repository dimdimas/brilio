<?php

class MostLikedController extends CController {

    function __construct() {
        parent::__construct();
        $this->library(array('table', 'lib_date'));
        $this->helper('mongodb');
        $this->ctrler = 'archives';
    }

    function index($thn = '', $bln = '', $tgl = '') {

        $TE_1 = 'Menu';
        $TE_2 = 'Most Liked';

        if (empty($thn))
        {
            $tanggal = date("Y-m-d");
        }
        else
        {
            $tanggal = $thn.'-'.$bln.'-'.$tgl;
        }

        $cacheKey = 'mobile_en_index_most_liked_'.$tanggal.'-'.$thn;
        if ($ret  = checkCache($cacheKey)) return $ret;

        $tanngal_sekarang = date("Y-m-d");
        //meta tag

        $most_liked = $this->most_liked_news($thn, $bln, $tgl);

        $og_image            = '';
        $og_image_secure     = '';
        $og_url              = '';
        if(!isset($most_liked['ERROR']))
        {
            // $popular_news    = $popular_news[0];
            $og_url          = $most_liked[0]['OG_URL'];
            //$sosmed          = $this->sosmed($popular_news['OG_URL']);
            $og_image        = 'http://cdn.klimg.com/newshub.id/'. substr($most_liked[0]['NEWS_IMAGES'], strlen($this->config['klimg_url']));
            $og_image_secure = 'https://cdn.klimg.com/newshub.id/'. substr($most_liked[0]['NEWS_IMAGES'], strlen($this->config['klimg_url']));

            //set news headline and list;
            $headline = $most_liked[0];
            unset($most_liked[0]);
            $news_list = $most_liked;
        }
        else
        {
            $og_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $og_image  = $this->config['base_url'].substr($this->config['assets_image_url'], strlen($this->config['rel_url'])).'logo.png';
            $og_image_secure = $this->config['base_url'].substr($this->config['assets_image_url'], strlen($this->config['rel_url'])).'logo.png';
            $headline = $most_liked;
            $news_list = '';
        }

        $url = $og_url;

        $meta = array(
            'meta_title'        => 'Most Liked Articles',
            'meta_description'  => 'Kisah dan cerita kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan paling populer',
            'meta_keywords'     => isset($most_liked['NEWS_SYNOPSIS'][0]) ? str_replace(' ', ', ', $most_liked['NEWS_SYNOPSIS'][0]) : 'Kisah, dan, cerita, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, paling, banyak, dikomentari',
            'og_url'            => $og_url,
            'og_image'          => $og_image,
            'og_image_secure'   => $og_image_secure,
            'img_url'           => $this->config['assets_image_url'],
            'expires'           => date(DATE_RFC1036),
            'chartbeat_sections'=> $TE_2,
            'chartbeat_authors' => 'Brilio.net',
            'meta_alternate'    => $this->config['www_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['m_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        $data = array(
            'full_url'          => $url,
            'meta'              => $meta,
            'title'             => 'Most Liked Articles - Brilio.net',
            'TE_2'              => $TE_2,
            'box_announcer'     => $this->view('mobile_en/box/_announcer_banner', [], TRUE),
            'BREADCRUMB'        => $this->breadcrumb($thn, $bln, $tgl),
            'MENU_TYPE_DATE'    => $this->menu_type_date($thn, $bln, $tgl),
            'MENU_PER_DATE'     => $this->menu_per_date($thn, $bln, $tgl),
            'HEADLINE'          => $headline,
            'NEWS_LIST'         => $news_list,
        );

        $ret =  $this->_mobile_render('mobile_en/most-liked/index_most_liked', $data);
        $interval = WebCache::App()->get_config('cachetime_short');
        setCache($cacheKey, $ret, $interval);

        return $ret;
    }

    function breadcrumb($thn, $bln, $tgl) {

        if (empty($thn))
        {
            $tanggal = date("Y-m-d");
        }
        else
        {
            $tanggal = $thn.'-'.$bln.'-'.$tgl;
        }
        $cacheKey = 'mobile_en_breadcrumb_most_liked_'.$tanggal.'-'.$thn;
        if ($ret = checkCache($cacheKey))
            return $ret;

        $data_breadcumb['TAHUN']          = $thn;
        $data_breadcumb['BULAN']          = $bln;
        $data_breadcumb['TANGGAL']        = $tgl;
        $data_breadcumb['TANGGAL_FORMAT'] = $this->lib_date->tgl_indo($thn.'/'.$bln.'/'.$tgl);

        if ($thn == '')
        {
            $data_breadcumb['init'] = 'A';
        }
        else if($thn == 'today'|| $thn =='week')
        {
            $data_breadcumb['init'] = 'B';
        }
        else
        {
            $data_breadcumb['init'] = 'C';
        }

        $ret = $data_breadcumb;
        $interval = WebCache::App()->get_config('cachetime_short');
        setCache($cacheKey, $ret, $interval);

        return $ret;
    }

    //MENU TYPE DATE -> TODAY OR WEEK
    function menu_type_date($thn, $bln, $tgl) {

        if (empty($thn))
        {
            $tanggal = date("Y-m-d");
        }
        else
        {
            $tanggal = $thn.'-'.$bln.'-'.$tgl;
        }

        $cacheKey = 'mobile_en_menu_type_date_most_liked_'.$tanggal.'-'.$thn;
        if ($ret = checkCache($cacheKey))
            return $ret;

        $link_today  = '';
        $link_week   = '';
        $class_today = '';
        $class_week  = '';
        $base_url    = $this->config['rel_url'];

        if (empty($thn))
        {
            $class_today = 'class="active"';
            $class_week  = '';
            $link_today  = '#';
            $link_week   = $base_url . 'most-liked/week';
        }
        else
        {
            if ($thn != 'week')
            {
                $class_today = 'class="active"';
                $link_today  = '#';
                $link_week   = $base_url . 'most-liked/week';
            }
            else
            {
                if ($thn == 'today')
                {
                    $class_today = 'class="active"';
                    $link_today  = '#';
                }
                else
                {
                    $class_today = '';
                    $link_today  = $base_url . 'most-liked/today';
                }
                if ($thn == 'week')
                {
                    $class_week = 'class="active"';
                    $link_week  = '#';
                }
                else
                {
                    $class_most_like = '';
                    $link_week       = $base_url . 'most-liked/week';
                }
            }
        }

        $menu['URL_TODAY']   = $link_today;
        $menu['URL_WEEK']    = $link_week;
        $menu['CLASS_TODAY'] = $class_today;
        $menu['CLASS_WEEK']  = $class_week;

        $ret = $menu;
        $interval = WebCache::App()->get_config('cachetime_short');
        setCache($cacheKey, $ret, $interval);

        return $ret;
    }

    //MENU PER DATE
    function menu_per_date($thn, $bln, $tgl) {

        if (empty($thn))
        {
            $tanggal = date("Y-m-d");
        }
        else
        {
            $tanggal = $thn.'-'.$bln.'-'.$tgl;
        }

        $cacheKey = 'mobile_en_menu_per_date_most_liked_'.$tanggal.'-'.$thn;
        if ($ret = checkCache($cacheKey))
            return $ret;

        $tanggal_sekarang              = date("Y/m/d");
        $data_menu_per_date['TANGGAL'] = $thn.'-'.$bln.'-'.$tgl;
        $data_menu_per_date['TAHUN']   = $thn;
        $data_menu_per_date['BULAN']   = $bln;
        $data_menu_per_date['TANGGAL'] = $tgl;

        if ($thn == '' || $thn == 'today' || $thn.'/'.$bln.'/'.$tgl == $tanggal_sekarang)
        {
            $data_menu_per_date['TANGGAL_SEKARANG_FORMAT']         = $this->lib_date->tgl_indo($tanggal_sekarang);
            $data_menu_per_date['TANGGAL_SEBELUM_SEKARANG_FORMAT'] = $this->lib_date->tgl_sebelumnya($tanggal_sekarang);
            $view_menu_per_date['init']                            = 'sebelum';
            $view_menu_per_date['VIEW_MENU_PER_DATE']              = $data_menu_per_date;
        }
        elseif ($thn == 'week')
        {
            $view_menu_per_date['init']               = 'kosong';
            $view_menu_per_date['VIEW_MENU_PER_DATE'] = '';
        }
        else
        {
            $data_menu_per_date['TANGGAL_VISIT_FORMAT']         = $this->lib_date->tgl_indo($thn.'/'.$bln.'/'.$tgl);
            $data_menu_per_date['TANGGAL_SEBELUM_VISIT_FORMAT'] = $this->lib_date->tgl_sebelumnya($thn.'/'.$bln.'/'.$tgl);
            $data_menu_per_date['TANGGAL_SESUDAH_VISIT_FORMAT'] = $this->lib_date->tgl_berikutnya($thn.'/'.$bln.'/'.$tgl);
            $view_menu_per_date['init']                         = 'sesudah';
            $view_menu_per_date['VIEW_MENU_PER_DATE']           = $data_menu_per_date;
        }

        $ret =  $view_menu_per_date;
        $interval = WebCache::App()->get_config('cachetime_short');
        setCache($cacheKey, $ret, $interval);

        return $ret;
    }

    function most_liked_news($thn, $bln, $tgl) {

        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = 'mobile_en_most_liked_news_'.date("Y-m-d").'-'.$thn;

        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        if($thn == 'week')
        {
            $seven_days_ago = date('Y-m-d',strtotime('- 7 days', time()));
            $start_date = $seven_days_ago.' 00:00:00';
            $end_date   = date('Y-m-d H:i:s');
        }
        elseif($thn == 'today' || $thn == '')
        {
            $start_date = date('Y-m-d').' 00:00:00';
            $end_date   = date('Y-m-d H:i:s');
        }
        else
        {
            $start_date = $thn.'-'.$bln.'-'.$tgl.' 00:00:00';
            $end_date   = $thn.'-'.$bln.'-'.$tgl.' 24:59:59';
        }

        $news = cache("mobile_en_query_liked_".$cacheKey, function () use ($start_date, $end_date)
        {
            return newsPopular('like', $this->config['mongo_prefix'], strtotime($start_date), strtotime($end_date), 20);

        }, $interval);

        if (empty($news))
        {
            $list_news['ERROR']   = "404";
            $list_news['MESSAGE'] = '<span class="pesan-error">Sorry, No good article at this day</span>';
        }
        else
        {
            $news = $this->generate_news_url($news);
            $no = 1;
            foreach ($news as $data)
            {
                # code...
                if($no == 1)
                {
                    $data_news['NO']            = $no;
                    $data_news['NEWS_TITLE']    = $data['news_title'];
                    $data_news['NEWS_SYNOPSIS'] = $data['news_synopsis'];
                    $data_news['NEWS_IMAGES']   = $data['news_image_location']  . $data['news_image_secondary'];
                    if (is_numeric($data['news_entry'])) {
                      $data_news['NEWS_ENTRY']    = $this->lib_date->mobile_waktu( date('Y-m-d H:i:s', $data['news_entry'])); //format 22 April 2015 13.30
                    }else{
                      $data_news['NEWS_ENTRY']    = $this->lib_date->mobile_waktu($data['news_entry']); //format 22 April 2015 13.30
                    }
                    $data_news['NEWS_URL']      = $data['news_url_full'];
                    $data_news['OG_URL']        = $data['news_url_with_base'];
                    $data_news["TAGS_TITLE"]    = $data['tag_name'];
                    // $data_news["TAGS_URL"]      = $data['tag_url_full'];
                }
                else
                {
                    $data_news['NO']            = $no;
                    $data_news['NEWS_TITLE']    = $data['news_title'];
                    $data_news['NEWS_SYNOPSIS'] = $data['news_synopsis'];
                    $data_news['NEWS_IMAGES']   = $data['news_image_location'] . '/100x100-' . $data['news_image_thumbnail'];
                    if (is_numeric($data['news_entry'])) {
                      $data_news['NEWS_ENTRY']    = $this->lib_date->mobile_waktu( date('Y-m-d H:i:s', $data['news_entry'])); //format 22 April 2015 13.30
                    }else{
                      $data_news['NEWS_ENTRY']    = $this->lib_date->mobile_waktu($data['news_entry']); //format 22 April 2015 13.30
                    }
                    $data_news['NEWS_URL']      = $data['news_url_full'];
                    $data_news["TAGS_TITLE"]    = $data['tag_name'];
                    // $data_news["TAGS_URL"]      = $data['tag_url_full'];
                }

                $list_news[] = $data_news;
                $no++;
            }
        }

        $ret = $list_news;
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

}
