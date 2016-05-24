<?php

class MostLikeController extends CController {

    function __construct()
    {
        parent::__construct();
        $this->model(array('news_model', 'jsview_model', 'what_happen_model', 'today_tags_model', 'news_rubric_model'));
        $this->library(array('table', 'lib_date'));
        $this->helper('mongodb');
        $this->ctrler = 'archives';
    }

    function index($thn = '', $bln = '', $tgl = '')
    {
        if (empty($thn))
        {
            $tanggal = date("Y-m-d");
        }
        else
        {
            $tanggal = $thn.'-'.$bln.'-'.$tgl;
        }

        $cacheKey = 'index-most_shared'.$tanggal.'-'.$thn;
        if ($ret  = checkCache($cacheKey)) return $ret;

        $tanngal_sekarang = date("Y-m-d");

        $TE = 'Most Liked';

        // FOR OPEN GRAPH FROM DATA HEADLINE
        $most_liked_news = $this->news_most_liked($thn, $bln, $tgl);
        $news                = $most_liked_news;

        $og_image            = '';
        $og_image_secure     = '';
        $og_url              = '';
        if(!isset($most_liked_news['ERROR']))
        {
            $most_liked_news = $most_liked_news[0];
            $og_url          = $most_liked_news['OG_URL'];
            $sosmed          = $this->sosmed($most_liked_news['OG_URL']);
            $og_image        = 'http://cdn.klimg.com/newshub.id/'. substr($most_liked_news['NEWS_IMAGES'], strlen($this->config['klimg_url']));
            $og_image_secure = 'https://cdn.klimg.com/newshub.id/'. substr($most_liked_news['NEWS_IMAGES'], strlen($this->config['klimg_url']));
        }

        $url = $og_url;

        $meta =
        [
            'meta_title'         => 'Most Liked Articles',
            'meta_description'   => 'Kisah dan cerita kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan paling banyak disukai',
            'og_url'             => $og_url,
            'og_image'           => $og_image,
            'og_image_secure'    => $og_image_secure,
            'expires'            => date(DATE_RFC1036),
            'meta_keywords'      => 'Kisah, dan, cerita, kehidupan, gaya, hidup, renungan, inspirasi, dan, ilmu, pengetahuan, paling, banyak, dibagii',
            'last_modifed'       => date(DATE_RFC1036),
            'img_url'            => $this->config['assets_image_url'],
            'chartbeat_sections' => $TE,
            'chartbeat_authors'  => 'Brilio.net',
            'meta_alternate'    => $this->config['m_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        ];

        $data =
        [
            'meta'              => $meta,
            'url'               => $url,
            'nama_halaman'      => $TE,
            'TE'                => $TE,
            'full_url'          => $url,
            'BREADCRUMB'        => $this->breadcrumb($thn, $bln, $tgl),
            'MENU_PER_DATE'     => $this->menu_per_date($thn, $bln, $tgl),
            'MENU_TYPE_DATE'    => $this->menu_type_date($thn, $bln, $tgl),
            'NEWS'              => $news,
            'SOSMED'            => isset($sosmed) ? $sosmed : '',
            'COLLECT_EMAIL'     => $this->view('desktop/box/_email', [], TRUE),
            'EDITOR_PICKS'      => $this->view('desktop/box/right_editors_pick', $this->_editor_picks("desktop-editor-picks-most-liked", $TE, 7), TRUE),
            'JUST_UPDATE'       => $this->view('desktop/box/right_just_update', $this->_just_update("desktop-just-update-most-liked", $TE), TRUE),
            'CHECK_THIS_OUT'    => $this->view('/desktop/box/right_check_this_out', $this->_check_this_out($TE, 7), TRUE),
        ];

        $ret      = $this->_render('desktop/most-liked/index_most_liked', $data);
        $interval = WebCache::App()->get_config('cachetime_short');
        setCache($cacheKey, $ret, $interval);
        return $ret;

    }

    // BREADCUMB
    function breadcrumb($thn, $bln, $tgl)
    {
        if (empty($thn))
        {
            $tanggal = date("Y-m-d");
        }
        else
        {
            $tanggal = $thn.'-'.$bln.'-'.$tgl;
        }

        $cacheKey = 'desktop-breadcrumb-most_liked'.$tanggal.'-'.$thn;
        if ($ret  = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $data_breadcrumb['TAHUN']          = $thn;
        $data_breadcrumb['BULAN']          = $bln;
        $data_breadcrumb['TANGGAL']        = $tgl;
        $data_breadcrumb['TANGGAL_FORMAT'] = $this->lib_date->tgl_indo($thn.'/'.$bln.'/'.$tgl);
        if ($thn == '')
        {
            $data_breadcrumb['init'] = 'A';
        }
        elseif($thn == 'today'|| $thn =='week')
        {
            $data_breadcrumb['init'] = 'B';
        }
        else
        {
            $data_breadcrumb['init'] = 'C';
        }

        $ret      = $data_breadcrumb;
        $interval = WebCache::App()->get_config('cachetime_short');
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    //MENU TYPE DATE -> TODAY OR WEEK
    function menu_type_date($thn, $bln, $tgl)
    {
        if (empty($thn))
        {
            $tanggal = date("Y-m-d");
        }
        else
        {
            $tanggal = $thn.'-'.$bln.'-'.$tgl;
        }

        $cacheKey = 'menu_type_date-most_liked'.$tanggal.'-'.$thn;
        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

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
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    //MENU PER DATE
    function menu_per_date($thn, $bln, $tgl)
    {
        if (empty($thn))
        {
            $tanggal = date("Y-m-d");
        }
        else
        {
            $tanggal = $thn.'-'.$bln.'-'.$tgl;
        }
        $cacheKey = 'menu_per_date-most_shared'.$tanggal.'-'.$thn;
        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

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
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    // HEADLINE
    function news_most_liked($thn, $bln, $tgl)
    {
        $interval = WebCache::App()->get_config('cachetime_short');

        if (empty($thn))
        {
            $tanggal = date("Y-m-d");
        }
        else
        {
            $tanggal = $thn.'-'.$bln.'-'.$tgl;
        }

        $cacheKey = 'desktop-news_most_liked-'.$tanggal.'-'.$thn;
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
            $end_date   = $thn.'-'.$bln.'-'.$tgl.' 23:59:59';
        }

        $news = cache("desktop-query".$cacheKey, function () use ($start_date, $end_date)
        {
            return newsPopular('like', $this->config['mongo_prefix'], strtotime($start_date), strtotime($end_date), 20);

        }, $interval);

        if(empty($news))
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
                if($no == 1)
                {
                    $data_news['NO']            = $no;
                    $data_news['NEWS_TITLE']    = $data['news_title'];
                    $data_news['NEWS_SYNOPSIS'] = $data['news_synopsis'];
                    $data_news['NEWS_IMAGES']   = $data['news_image_location'] . '/300x150-' . $data['news_image_secondary'];
                    if (is_numeric($data['news_date_publish'])) {
                      $data_news['NEWS_ENTRY']    = $this->lib_date->indo( date('Y-m-d H:i:s', $data['news_date_publish'])); //format 22 April 2015 13.30
                    }else{
                      $data_news['NEWS_ENTRY']    = $this->lib_date->indo($data['news_date_publish']);
                    }
                    $data_news['NEWS_URL']      = $data['news_url_full'];
                    $data_news['OG_URL']        = $data['news_url_with_base'];
                    if(!empty($data['tag_name']))
                    {
                        $data_news["TAGS_TITLE"]    = $data['tag_name'];
                        $data_news["TAGS_URL"]      = $data['tag_url_full'];
                    }
                    else
                    {
                        $data_news["TAGS_TITLE"]    = "";
                        $data_news["TAGS_URL"]      = "";
                    }
                }
                else
                {
                    $data_news['NO']            = $no;
                    $data_news['NEWS_TITLE']    = $data['news_title'];
                    $data_news['NEWS_SYNOPSIS'] = $data['news_synopsis'];
                    $data_news['NEWS_IMAGES']   = $data['news_image_location'] . '/180x90-' . $data['news_image_secondary'];
                    if (is_numeric($data['news_date_publish'])) {
                      $data_news['NEWS_ENTRY']    = $this->lib_date->indo( date('Y-m-d H:i:s', $data['news_date_publish'])); //format 22 April 2015 13.30
                    }else{
                      $data_news['NEWS_ENTRY']    = $this->lib_date->indo($data['news_date_publish']);
                    }
                    $data_news['NEWS_URL']      = $data['news_url_full'];
                    if(!empty($data['tag_name']))
                    {
                        $data_news["TAGS_TITLE"]    = $data['tag_name'];
                        $data_news["TAGS_URL"]      = $data['tag_url_full'];
                    }
                    else
                    {
                        $data_news["TAGS_TITLE"]    = "";
                        $data_news["TAGS_URL"]      = "";
                    }
                }

                $list_news[] = $data_news;
                $no++;
            }
        }

        $ret = $list_news;
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    //SOSMET
    function sosmed($url = ''){
        $cacheKey = 'desktop_sosmed-most_liked'.  md5($url);
        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        $share_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        if(!empty($url))
        {
            $share_url = $url;
        }
        $sosmed_data['SOSMED_URL'] = $share_url;

        $ret = $this->view('desktop/box/_sosmed', $sosmed_data, true);
        $interval = WebCache::App()->get_config('cachetime_short');
        setCache($cacheKey, $ret, $interval);
        return $ret;
    }

}

?>
