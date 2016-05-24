<?php

class PhotoDetailController extends CController {

    private $_exclude = [];
    private $_id_cat;

    function __construct()
    {
        parent::__construct();
        $this->model(array('news_model', 'tags_model', 'jsview_model', 'what_happen_model', 'tag_news_model', 'today_tags_model', 'news_paging_model', 'news_related_model', 'photonews_detail_model', 'video_model'));
        $this->model(['News', 'PhotonewsDetail', 'TagNews', 'Tag', 'NewsRelated', 'SponsorTag'], null, true);
        $this->library(array('table', 'lib_date', 'widget'));
        $this->helper('mongodb');
    }

    function index($category='', $url_keyword_news = '', $url_paging_news = '')
    {

        $interval = WebCache::App()->get_config('cachetime_default');
        $url_filter = $_SERVER["REQUEST_URI"];


        $hide_keyword_html = explode('.html', $url_keyword_news);
        $keyword_news      = $hide_keyword_html[0];
        $hide_paging_html  = explode('.html', $url_paging_news);
        $keyword_paging    = $hide_paging_html[0];
        $file              = $keyword_news;
        $TE  = 'Detail foto';

        $split_cat = explode('preview-', $category);

        if ( count($split_cat) > 1 )
        {
            $category = $split_cat[1];
            $preview_flag = TRUE;
            $key_preview = 'preview';
        }
        else
        {
            $category = $split_cat[0];
            $preview_flag = FALSE;
            $key_preview = '';
        }

        $cacheKey   = 'desktop_read_photo_content_' . $url_filter.'_'.$key_preview;
        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        if (empty($url_paging_news))
        {
            $mongo_file = $keyword_news;
        }
        else
        {
            $mongo_file = $keyword_news . '/' . $keyword_paging;
        }

        if ($this->config['json_news_detail'] === TRUE) {
            # code...
            $mongo_data = readDataMongo($mongo_file, $this->config['mongo_prefix'] . "photo");
        }
        else
        {
            $mongo_data = '';

        }
        //FUNCTION DATA READ NEWS PHOTOS
        if (!empty($mongo_data))
        {
            $news_detail = $mongo_data;

            //cek for preview page
            if((date('Y-m-d H:i:s', $news_detail['news_date_publish']) >= date('Y-m-d H:i:s')) && $preview_flag == FALSE){
                Output::App()->show_404();
            }

            if (isset($mongo_data['tag_news'])) {
                $news_detail['news_tag_list'] = $mongo_data['tag_news'];
            }
            else
            {
                $news_detail['news_tag_list'] = null;
            }

            //cek more_stories
            if ( empty($news_detail['related_news_list']) || !isset($news_detail['related_news_list']) ) {
                $news_detail['related_news_list'] = $this->more_stories($news_detail, $TE);
            }

        }
        else
        {
            $news_detail = cache("dekstop_photo_query_more_stories_".$keyword_news, function () use ($keyword_news){
                $rows = News::where('news_domain_id', '=', $this->config['domain_id'])
                            ->where('news_type', '=', '1')
                            ->where('news_level', '=', '1')
                            ->where('news_url', '=', $keyword_news)
                            ->with('news_tag_list')
                            ->with('sponsor_tag')
                            ->get()
                            ->toArray();
                return $rows;
            }, $interval);

            $news_detail = $this->generate_news_url($news_detail);

            if ($news_detail) {
                # code...
                // ADDED FOR NEWS_RUBBRICK
                $news_detail = $news_detail[0];

                //cek for preview page
                if($news_detail['news_date_publish'] >= date('Y-m-d H:i:s') && $preview_flag == FALSE){
                    Output::App()->show_404();
                }

                $category_meta = $this->_category($news_detail['news_category']);

                $news_detail['news_rubrics_rubrics_common'] = ucfirst($category_meta['CATEGORY_URL']);

                // FUNCTION MORE STORIES
                $news_detail['related_news_list'] = $this->more_stories($news_detail, $TE);


            }
            else
            {
                Output::App()->show_404();
            }

        }


        //FUNCTION READ EDITOR & REPORTER
        $reporter_json         = $news_detail['news_reporter'];
        $reporter_json_decode  = json_decode($reporter_json);
        $editor_json        = $news_detail['news_editor'];
        $editor_json_decode = json_decode($editor_json);

        $news_detail['reporter']   = $reporter_json_decode[0]->name;
        $news_detail['style_date'] = '';

        //FUNCTION READ CATEGORY
        $category_meta = $this->_category($news_detail['news_category']);
        $category_url  = strtolower($news_detail['news_category_url']);
        $category_slug  = $category_meta['CATEGORY_URL'];
        $category      = $category_meta['CATEGORY_TITLE'];

        $news_detail['category_url'] = $category_url;

        // FULL_URL
        $news_detail['full_url'] = $this->config['base_url']. 'photo' . '/' . strtolower($news_detail['category_url']) . '/' . $news_detail['news_url'] . '.html';

        //FUNCTION READ Y M D
        $datetime       = explode(" ", $news_detail['news_entry']);
        $datetime_clear = explode("-", $datetime[0]);
        $year           = $datetime_clear[0];
        $month          = $datetime_clear[1];
        $date           = $datetime_clear[2];

        //DATE PUBLISH
        if (is_numeric($news_detail['news_date_publish']) )
        {
            $news_detail['news_date_publish_indo'] = $this->lib_date->indo(date('Y-m-d H:i:s', $news_detail['news_date_publish']));
        }
        else
        {
            $news_detail['news_date_publish_indo'] = $news_detail['news_date_publish'];
        }

        // QUERY ALL PHOTOS
        $cache_news_id = $news_detail['news_id'];
        $all_photos = PhotonewsDetail::where('photonews_newsid', '=', $news_detail['news_id'])->orderby('photonews_id', 'ASC');

        //FUNCTION DATA PHOTO VISIT
        if (!empty($mongo_data['data_photos_visit']) || isset($mongo_data['data_photos_visit']))
        {
            $json['data_photos_visit'] = $mongo_data['data_photos_visit'];
        }
        else
        {
            $data_visit_photos = cache("desktop_query-".$cacheKey."data_visit_photos", function () use($all_photos, $keyword_paging) {
                $photos = clone $all_photos;

                if($keyword_paging == '')
                {
                    $photos = $photos->get()->toArray();
                }
                else
                {
                    $photos = $photos->where('photonews_url', '=', $keyword_paging)->get()->toArray();
                }

                return ['photos' => $photos[0]];
            }, $interval);

            $json['data_photos_visit'] = $data_visit_photos['photos'];
        }

        //FUNCTION TOTAL PHOTO
        if (!empty($mongo_data['total_photos']) || isset($mongo_data['total_photos']))
        {
            $json['total_photos'] = $mongo_data['total_photos'];
        }
        else
        {
            $total_photos = clone $all_photos;
            // $total_photos = $total_photos->count();
            $json['total_photos'] = count($total_photos->get()->toArray());
        }

        // GET VISIT PAGE NUMBER "photonews_url"
        $get_photo_list = clone $all_photos;
        $get_photo_list = $get_photo_list->get()->toArray();

        foreach ($get_photo_list as $v)
        {
            $list_photos_paging[] = $v['photonews_url'];
        }
        $visit_page_no = array_search($json['data_photos_visit']['photonews_url'], $list_photos_paging) + 1;

        //FUNCTION PHOTOS PREV, PHOTO VISIT
        if (!empty($mongo_data['prev_photos_visit']))
        {
            $json['prev_photos_visit'] = $mongo_data['prev_photos_visit'];
        }
        else
        {
            $prev_photos = cache("desktop_query-".$cacheKey."prev_photos_visit", function () use ($visit_page_no, $get_photo_list) {
                if($visit_page_no == 1)
                {
                    return "";
                }
                else
                {
                    return $get_photo_list[$visit_page_no-2];
                }
                return $prev;
            }, $interval);
            $json['prev_photos_visit'] = $prev_photos;
        }

        //FUNCTION PHOTOS NEXT, PHOTOS VISIT
        if (!empty($mongo_data['next_photos_visit']))
        {
            $json['next_photos_visit'] = $mongo_data['next_photos_visit'];
        }
        else
        {
            $next_photos = cache("desktop_query-".$cacheKey."next_photos_visit", function () use ($visit_page_no, $get_photo_list) {

                if($visit_page_no == count($get_photo_list))
                {
                    return "";
                }
                else
                {
                    return $get_photo_list[$visit_page_no];
                }

            }, $interval);
            $json['next_photos_visit'] = $next_photos;

        }

        //FUNCTION LIST PHOTOS SMALL
        if (!empty($mongo_data['get_photo']) || isset($mongo_data['get_photo']))
        {
            $json['get_photo'] = $mongo_data['get_photo'];
        }
        else
        {
            $page   = ceil($visit_page_no / 8);
            $total  = $json['total_photos'];
            $limit  = 9;
            $last   = $json['total_photos'] - $limit;
            $center = 5;


            if ($visit_page_no <= 4)
            {
                $offset = 0;
                $start  = 0;
            }
            elseif ($visit_page_no >= $last)
            {
                $offset = ($page - 1) * $limit;

                if ($visit_page_no <= ($total - 3))
                {
                    $start = ($json['data_photos_visit']['photonews_id']) - ($limit - 3);
                }
                else if ($visit_page_no == $total)
                {
                    $start = ($json['data_photos_visit']['photonews_id']) - $limit;
                }
                elseif ($visit_page_no <= ($total - 2))
                {
                    $start = ($json['data_photos_visit']['photonews_id']) - ($limit - 2);
                }
                else
                {
                    $start = ($json['data_photos_visit']['photonews_id']) - ($limit - 1);
                }
            }
            else
            {
                $offset = ($page - 1) * $limit;
                $start  = ($json['data_photos_visit']['photonews_id']) - ($limit - 3);
            }

            $get_photo_small = cache("query_desktop-".$cacheKey."-get_photo-".$start."-".$limit, function () use ($all_photos, $start, $limit){
                $photo_small = clone $all_photos;
                $photo_small = $photo_small ->where('photonews_id', '>', $start)
                                            ->take($limit)->get()->toArray();
                return $photo_small;
            }, $interval);
            $json['get_photo'] = $get_photo_small;
        }

        $no = 1;
        foreach ($json['get_photo']as $ph)
        {
            if ($json['data_photos_visit']['photonews_id'] == $ph['photonews_id'])
            {
                $class_photos_visited = 'class="active"';
            }
            else {
                $class_photos_visited = '';
            }
            $ls_photos[$no]['class_photos_visited'] = $class_photos_visited;
            $ls_photos[$no]['photonews_url']        = $this->config['rel_url'] . 'photo/' . $news_detail['category_url'] . '/' . $news_detail['news_url'] . '/' . $ph['photonews_url'] . '.html';
            $ls_photos[$no]['photonews_img']        = $this->config['klimg_url'] . 'photonews' . '/' . $year . '/' . $month . '/' . $date . '/' . $ph['photonews_newsid'] . '/105x105-' . basename($ph['photonews_src']);
            $no++;

        }

        $news_detail['list_photos_small'] = $ls_photos;
        //EOF FUNCTION LIST PHOTOS SMALL

        //FUNCTION DATA ARRAY FOR PAGING PHOTO TOP
        $news_detail['paging_photos_top']['visit_images_number'] = $visit_page_no;
        $news_detail['paging_photos_top']['total_images']        = $json['total_photos'];

        if(!empty($json['next_photos_visit']))
        {
            $news_detail['paging_photos_top']['next_photonews_url'] = $this->config['base_url'] . 'photo/' . $category_slug . '/' . $news_detail['news_url'] . '/' . $json['next_photos_visit']['photonews_url'] . '.html';
        }
        else
        {
            $news_detail['paging_photos_top']['next_photonews_url'] = '#';
        }

        if(!empty($json['prev_photos_visit']))
        {
            if( $visit_page_no == 2 )
            {
                $news_detail['paging_photos_top']['prev_photonews_url'] = $this->config['base_url'] . 'photo/' . $category_slug . '/' . $news_detail['news_url'] . '.html';
            }
            else
            {
                $news_detail['paging_photos_top']['prev_photonews_url'] = $this->config['base_url'] . 'photo/' . $category_slug . '/' . $news_detail['news_url'] . '/' . $json['prev_photos_visit']['photonews_url'] . '.html';
            }
        }
        else
        {
            $news_detail['paging_photos_top']['prev_photonews_url'] = '#';
        }
        //EOF CREATE DATA ARRAY FOR PAGING IMAGE TOP

        //FUNCTION DATA PHOTOS WHEN VISITED
        $news_detail['data_photos_visited']['photonews_id']          = $json['data_photos_visit']['photonews_id'];
        $news_detail['data_photos_visited']['photonews_url']         = $this->config['klimg_url'] . 'photonews' . '/' . $year . '/' . $month . '/' . $date . '/' . $json['data_photos_visit']['photonews_newsid'] . '/657xauto-' . basename($json['data_photos_visit']['photonews_src']);
        $news_detail['data_photos_visited']['photonews_title']       = $json['data_photos_visit']['photonews_title'];
        $news_detail['data_photos_visited']['photonews_description'] = $json['data_photos_visit']['photonews_description'];
        $news_detail['data_photos_visited']['photonews_copyright']   = $json['data_photos_visit']['photonews_copyright'];
        //EOF FUNCTION DATA PHOTOS WHEN VISITED

        // INITIAL EDITOR
        if (isset($editor_json_decode[0]->user_fullname) && !empty($editor_json_decode[0]->user_fullname))
        {
            $news_detail['initial_editor'] = '(brl/' . $this->widget->inisial_editor($editor_json_decode[0]->id) . ')';
        }
        else
        {
            $news_detail['initial_editor'] = '';
        }

        //FUNCTION TAGS VISIT NEWS
        //FUNCTION TAGS VISIT NEWS

        if (isset($mongo_data['news_tag_list']) || !empty($mongo_data['news_tag_list']))
        {
            foreach ($mongo_data['news_tag_list'] as $key => $value) {
                    $news_detail['news_tag_list'][$key] = $mongo_data['news_tag_list'][$key];
                    $tag_url    = strtolower( preg_replace('/[#! .]/', '', $value['tag_news_tags'] ) );
                    $huruf_awal = strtolower( $tag_url{0} );
                    $news_detail['news_tag_list'][$key]['tag_url_full'] = $this->config['rel_url']. 'tag/' .$huruf_awal .'/'. $tag_url .'/';
            }

        }
        else
        {
            if (isset($news_detail['news_tag_list']) )
            {
                foreach ($news_detail['news_tag_list'] as $key => $value) {
                    $news_detail['news_tag_list'][$key] = $news_detail['news_tag_list'][$key];
                    $tag_url    = strtolower( preg_replace('/[#! .]/', '', $value['tag_news_tags'] ) );
                    $huruf_awal = strtolower( $tag_url{0} );
                    $news_detail['news_tag_list'][$key]['tag_url_full'] = $this->config['rel_url']. 'tag/' .$huruf_awal .'/'. $tag_url .'/';
                }
            }


        }



        //FUNCTION ADDED KODE TRACK EVENT - LINK INSIDE CONTENT
        $news_detail['news_content'] = $this->add_te_link_in_content($news_detail['news_content'], $TE);
        //end kode track event

        $tmp_content = $news_detail['news_content'];

        //FUNCTION SPONSOR TAG
        if( !empty($news_detail['sponsor_tag']) )
        {
            $news_detail['reporter_status']     = 'brand';
            $news_detail['reporter']            = $this->view('desktop/box/_box_brand', ['brand' => $news_detail['sponsor_tag'], 'TE' => $TE ], true);
            $news_detail['reporter_style_date'] = 'style="margin-top: 28px;"';
        }

        $init_brand = FALSE;
        foreach ($this->config['keyword_brand'] as $key => $value)
        {
            if ($key == $keyword_news )
            {
                $brand                              = array();
                $brand['brand_image']               = $this->config['assets_image_url'] . $this->config['keyword_brand'][$key][0];
                $brand['brand_url']                 = '#';
                $news_detail['reporter']            = $this->view('desktop/box/_box_brand', ['brand' => $brand, 'TE' => $TE], true);
                $news_detail['reporter_style_date'] = 'style="margin-top: 28px;"';
                $news_detail['reporter_status']     = 'brand';
                $init_brand = TRUE;
            }
        }
        //eof brand image reporter

        //function crosslink
        $cross = cache('desktop_news_crosslink_'. $cache_news_id , function () use($news_detail) {
            $cross_ = news_related::where('news_related_news_id',$news_detail['news_id'])->get();
            if ($cross_)
            {
                     return $cross_->toArray();
            }
            else
            {
                     return FALSE;
            }
        });

        if ($cross)
        {
            foreach ($cross as $key=>$value)
            {
                 if (strpos(html_entity_decode($news_detail['news_content']),'['.$value['news_related_code'].']') !== false)
                 {
                      $value['TE'] = $TE;
                      $cross = $this->view('desktop/box/_news_crosslink',['cross' => $value],TRUE);
                      $news_detail['news_content'] = str_replace('['.$value['news_related_code'].']', $cross , $news_detail['news_content']);
                 }
            }
        }
        // end of cross link

        //FUNCTION BREADCUMB
        $data_breadcrumb['CATEGORY_TITLE'] = $category;
        $data_breadcrumb['CATEGORY_URL']   = strtolower($category_url);

        //FUNCTION REDIRECT 404 IF CAN'T READ NEWS PHOTO
        if (count($news_detail) == 0)
        {
            Output::App()->show_404();
        }

        $url_amp = $this->config['base_url']. 'amp/photo' . '/' . strtolower($news_detail['category_url']) . '/' . $news_detail['news_url'] . '.html';

        $meta =
        [
            'meta_title'        => $news_detail['news_title'],
            'meta_description'  => $news_detail['news_synopsis'],
            'meta_keywords'     => str_replace(' ', ', ', $news_detail['news_synopsis']),
            'og_url'            => $news_detail['news_url_with_base'],
            'og_image'          => 'http://cdn.klimg.com/newshub.id/' . substr($news_detail['news_image_location_full'], strlen($this->config['klimg_url'])),
            'og_image_secure'   => $news_detail['news_image_location_full'],
            'img_url'           => $this->config['assets_image_url'],
            'expires'           => date("D,j M Y G:i:s T", strtotime($news_detail["news_date_publish"])),
            'meta_keywords'     => str_replace(' ', ', ', $news_detail['news_synopsis']),
            'last_modifed'      => gmdate("D,j M Y G:i:s e", strtotime($news_detail["news_date_publish"])),
            'chartbeat_sections'=> ucfirst(strtolower($category)),
            'chartbeat_authors' => $reporter_json_decode[0]->name,
            'meta_alternate'    => $this->config['m_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'amphtml'           => isset($url_amp) ? $url_amp : $this->config['base_url'] ,
        ];

        $data =
        [
            'full_url'          => $news_detail['news_url_with_base'],
            'TE'                => $TE,
            'meta'              => $meta,
            'whats_hot'         => $this->_whats_hot($TE),
            'breadcrumb'        => $data_breadcrumb,
            'news_data'         => $news_detail,
            'list_related_news' => $this->view('desktop/photo/_news_more_stories', ['TE' => $TE , 'related_news_list' => $news_detail['related_news_list'] ], TRUE),
            'trending'      => $this->view('desktop/box/right_trending', $this->_trending(7, $TE), TRUE),
            'collect_email'     => $this->view('desktop/box/_email', [], TRUE),
            'bottom_menu'       => $this->view('desktop/box/bottom_menu', ['TE' => $TE ], TRUE),
            'video'                => $this->view('desktop/box/promote_video', ['promote_video' => $this->get_video_sponsor($news_detail['news_sponsorship'], $init_brand) ], TRUE),
        ];

        if (empty($mongo_data) && ($this->config['json_news_detail'] === TRUE))
        {
            $news_detail['news_id'] = (int) $news_detail['news_id'];
            $news_detail['news_content'] = $tmp_content;
            $news_detail['news_date_publish'] = strtotime($news_detail['news_date_publish']);
            $news_detail = array_merge($news_detail, $json);
            writeDataMongo($mongo_file, $news_detail, $this->config['mongo_prefix'] . "photo");
        }

        $ret      = $this->_render('desktop/photo/photodetail', $data);
        setCache($cacheKey, $ret, $interval);
        return $ret;
    }


    function more_stories($news_detail, $TE){
        $interval   = WebCache::App()->get_config('cachetime_default');
        $cacheKey = 'desktop_photo_more_stories_' . $news_detail['news_id'];

        if ($ret = checkCache($cacheKey))
        return unserialize($ret) ;

        if (is_numeric($news_detail['news_date_publish']) )
        {
            $date_publish = date('Y-m-d H:i:s', $news_detail['news_date_publish']);
        }
        else
        {
            $date_publish = $news_detail['news_date_publish'];
        }

        $total_related = 24;

        $_v = cache('query_photo_more_stories_dekstop_'.$news_detail['news_id'], function() use( $date_publish, $total_related) {
                 return News::where('news_domain_id', $this->config['domain_id'])
                        ->where('news_level','1')
                        ->where('news.news_date_publish', '<' ,$date_publish)
                        ->groupBy('news.news_id')
                        ->orderBy('news_date_publish','DESC')
                        ->take($total_related)
                        ->get()->toArray();
            }, $interval );

        $_more_stories = $this->generate_news_url($_v);

        $no = 1;
        foreach ($_more_stories as $v)
        {
           $more_stories_list[$no]['TE'] = $TE;
           $more_stories_list[$no]['news_title'] = $v['news_title'];
           $more_stories_list[$no]['news_url_full'] = $v['news_url_full'];
           $more_stories_list[$no]['news_image'] = $v['news_image'];
           $more_stories_list[$no]['news_image_potrait'] = $v['news_image_potrait'];
           $more_stories_list[$no]['news_image_location'] = $v['news_image_location'];
           $no++;
        }

        setCache($cacheKey, serialize($more_stories_list) , $interval);

        return $more_stories_list;

    }

}
?>
