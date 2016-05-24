<?php

class PhotoDetailController extends CController {

    function __construct() {
        parent::__construct();
        $this->model(['News', 'PhotonewsDetail', 'NewsRelated', 'TagNews', 'SponsorTag'], null, true);
        $this->library(array('table', 'lib_date', 'widget'));
        $this->helper('mongodb');
    }

    function index($category= '', $url_keyword_news = '', $url_paging_news = '')
    {
        $interval          = WebCache::App()->get_config('cachetime_default');
        $url_filter        = $_SERVER["REQUEST_URI"];
        $TE_1              = 'Detail foto';
        $TE_2              = 'Detail foto';
        $hide_keyword_html = explode('.html', $url_keyword_news);
        $keyword_news      = $hide_keyword_html[0];
        $hide_paging_html  = explode('.html', $url_paging_news);
        $keyword_paging    = $hide_paging_html[0];
        $split_cat         = explode('preview-', $category);

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

        if (empty($url_paging_news))
        {
            $file = $keyword_news;
        }
        else
        {
            $file = $keyword_news . '/' . $keyword_paging;
            $file = str_replace(".html", "", $file);
        }

        $cacheKey   = 'mobile_en_read_photo_content_' . $url_filter.'_'.$key_preview;
        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        //CONFIG MONGO DB - development - production
        if ($this->config['json_news_detail'] === TRUE)
        {
            $mongo_data = readDataMongo($file, $this->config['mongo_prefix'] . "photo");
        }
        else
        {
            $mongo_data = '';
        }

        //FUNCTION DATA READ PHOTOS
        if (!empty($mongo_data))
        {
            $news_detail = $mongo_data;

            //cek for preview page
            if((date('Y-m-d H:i:s', $news_detail['news_date_publish']) >= date('Y-m-d H:i:s')) && $preview_flag == FALSE){
                Output::App()->show_404();
            }

            //cek more_stories
            if ( empty($news_detail['related_news_list']) ) {
                //get more stories
                $news_detail['related_news_list']=$this->more_stories($news_detail, $TE_2);
            }
        }
        else
        {

            $news_detail = cache("query_mobile_en_".$keyword_news, function () use ($keyword_news){
                $rows = News::where('news_domain_id', '=', $this->config['domain_id'])
                            ->where('news_type', '=', '1')
                            ->where('news_level', '=', '1')
                            ->where('news_url', '=', $keyword_news)
                            ->with('sponsor_tag')
                            ->get()
                            ->toArray();
                return $rows;
            }, $interval);

            $news_detail = $this->generate_news_url($news_detail);

            if (empty($news_detail))
            {
                Output::App()->show_404();
            }

            $news_detail = $news_detail[0];

            //cek for preview page
            if($news_detail['news_date_publish'] >= date('Y-m-d H:i:s') && $preview_flag == FALSE){
                Output::App()->show_404();
            }

            //get more stories
            $news_detail['related_news_list']=$this->more_stories($news_detail, $TE_2);
        }

        $reporter_json = $news_detail['news_reporter'];
        $reporter_json_decode = json_decode($reporter_json);
        $editor_json = $news_detail['news_editor'];
        $editor_json_decode = json_decode($editor_json);

        $news_detail['reporter']   = $reporter_json_decode[0]->name;
        $news_detail['style_date'] = '';

        //FUNCTION EDITOR
        if (isset($editor_json_decode[0]->user_fullname) && !empty($editor_json_decode[0]->user_fullname))
        {
            $news_detail['editor'] = 'Editor : ' . $editor_json_decode[0]->user_fullname . '';
            $news_detail['inisial_editor'] = '(brl/' . $this->widget->inisial_editor($editor_json_decode[0]->id) . ')';
        }
        else
        {
            $news_detail['editor'] = '';
            $news_detail['inisial_editor'] = '';
        }
        //eof FUNCTION EDITOR

        //FUNCTION READ CATEGORY
        $category_meta = $this->_category($news_detail['news_category']);
        $category_url  = str_replace("!", "", $category_meta['CATEGORY_URL']);
        $category      = $category_meta['CATEGORY_TITLE'];

        $news_detail['category_url'] = $category_url;

        // URL SHARE (FOR BOX SHARE)
        $news_detail['url_share'] = $news_detail['news_url_with_base'];
        $url = $news_detail['url_share'];

        // FB, TWITTER, WA SHARE
        $news_detail['FACEBOOK_SHARE'] = "https://www.facebook.com/sharer/sharer.php?u=" . $news_detail['url_share'] . "";
        $news_detail['TWITTER_SHARE'] = "https://twitter.com/intent/tweet?text=" . $news_detail['news_title'] . "&url=" . $news_detail['url_share'] . "&via=brilio.net";
        $news_detail['WA_SHARE'] = "whatsapp://send?text=" . "Brilio.net | " . $news_detail['news_title'] . " " . $news_detail['url_share'];

        // NEWS DATE
        if(is_numeric($news_detail['news_date_publish']))
        {
            $news_detail['news_date'] = $this->lib_date->mobile_waktu(date("Y-m-d H:i:s", $news_detail['news_date_publish']));
        }
        else
        {
            $news_detail['news_date'] = $this->lib_date->mobile_waktu($news_detail['news_date_publish']);
        }


        $datetime = explode(" ", $news_detail['news_entry']);
        $datetime_clear = explode("-", $datetime[0]);
        $year = $datetime_clear[0];
        $month = $datetime_clear[1];
        $date = $datetime_clear[2];

        // QUERY ALL PHOTOS
        $cache_news_id = $news_detail['news_id'];
        $all_photos = PhotonewsDetail::where('photonews_newsid', '=', $news_detail['news_id'])
                                    ->orderby('photonews_id', 'ASC');

        //FUNCTION DATA PHOTO VISIT
        if (!empty($mongo_data['data_photos_visit']))
        {
             $news_detail['data_photos_visit'] = $mongo_data['data_photos_visit'];
        }
        else
        {
            $data_visit_photos = cache("query_mobile_en_".$cacheKey."data_visit_photos", function () use($all_photos, $keyword_paging) {
                $photos = clone $all_photos;

                if($keyword_paging == '')
                {
                    $photos = $photos->get()
                                  ->toArray();
                }
                else
                {
                    $photos = $photos->where('photonews_url', '=', $keyword_paging)
                                  ->get()
                                  ->toArray();
                }

                return ['photos' => $photos[0]];
            }, $interval);
            $news_detail['data_photos_visit'] = $data_visit_photos['photos'];
        }

        //FUNCTION TOTAL PHOTO
        if (!empty($mongo_data['total_photos']) || isset($mongo_data['total_photos']))
        {
             $news_detail['total_photos'] = $mongo_data['total_photos'];
        }
        else
        {
//            $news_detail['total_photos'] = $this->photonews_detail_model->total_list_photos($news_detail['news_id']);
            $total_photos = clone $all_photos;
            $total_photos = $total_photos->count();
            $news_detail['total_photos'] = $total_photos;
        }

        $get_photo_list = clone $all_photos;
        $get_photo_list = $get_photo_list->get()
                                         ->toArray();

        foreach ($get_photo_list as $v)
        {
            $list_photos_paging[] = $v['photonews_url'];
        }
        $visit_page_no = array_search($news_detail['data_photos_visit']['photonews_url'], $list_photos_paging) + 1;


        //FUNCTION PHOTOS PREV, PHOTO VISIT
        if (!empty($mongo_data['prev_photos_visit']) || isset($mongo_data['prev_photos_visit']))
        {
            $news_detail['prev_photos_visit'] = $mongo_data['prev_photos_visit'];
        }
        else
        {
            $prev_photos = cache("query_mobile_en_".$cacheKey."prev_photos_visit", function () use ($visit_page_no, $get_photo_list) {
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
            $news_detail['prev_photos_visit'] = $prev_photos;
        }

        //FUNCTION PHOTOS NEXT, PHOTOS VISIT
        if (!empty($mongo_data['next_photos_visit']) || isset($mongo_data['next_photos_visit']))
        {
            $news_detail['next_photos_visit'] = $mongo_data['next_photos_visit'];
        }
        else
        {
            $next_photos = cache("query_mobile_en_".$cacheKey."next_photos_visit", function () use ($visit_page_no, $get_photo_list) {

                if($visit_page_no == count($get_photo_list))
                {
                    return "";
                }
                else
                {
                    return $get_photo_list[$visit_page_no];
                }

            }, $interval);
            $news_detail['next_photos_visit'] = $next_photos;
        }

        //FUNCTION IMAGES HEADLINE
        if($visit_page_no == 1)
        {
            $news_image_url = $this->config['klimg_url'] . 'photonews' . '/' . $year . '/' . $month . '/' . $date . '/' . $news_detail['news_id'] . '/657xauto-' . $news_detail['news_image'];
            if (empty($news_detail['news_imageinfo']))
            {
                $news_imageinfo = "Brilio.net";
            }
            else
            {
                $news_imageinfo = $news_detail['news_imageinfo'];
            }
            $photonews_copyright = $news_detail['data_photos_visit']['photonews_copyright'];

            $news_detail['images_headline']['news_image_url'] = $news_image_url;
            $news_detail['images_headline']['news_imageinfo'] = $news_imageinfo;
            $news_detail['images_headline']['photonews_copyright'] = $photonews_copyright;
        }

        //FUNTION CREATE [DATA_PHOTOS_VISITED]
        $photonews_image_url = $this->config['klimg_url'] . 'photonews' . '/' . $year . '/' . $month . '/' . $date . '/' . $news_detail['data_photos_visit']['photonews_newsid'] . '/657xauto-' . basename($news_detail['data_photos_visit']['photonews_src']);
        $news_detail['data_photos_visited']['visit_image_number'] = $visit_page_no;
        $news_detail['data_photos_visited']['photonews_image_url'] = $photonews_image_url;
        $news_detail['data_photos_visited']['photonews_title'] = $news_detail['data_photos_visit']['photonews_title'];
        $news_detail['data_photos_visited']['photonews_description'] = $news_detail['data_photos_visit']['photonews_description'];
        $news_detail['data_photos_visited']['photonews_copyright'] = $news_detail['data_photos_visit']['photonews_copyright'];

        //FUNCTION CREATE [NAV_PAGING_PHOTOS]
        $news_detail['nav_paging_photos']['visit_images_number'] = $visit_page_no;
        $news_detail['nav_paging_photos']['total_images'] = $news_detail['total_photos'];

        if(!empty($news_detail['next_photos_visit']))
        {
            $news_detail['nav_paging_photos']['next_photonews_url'] = $this->config['rel_url'] . 'photo/' . strtolower($news_detail['category_url']) . '/' . $news_detail['news_url'] . '/' . $news_detail['next_photos_visit']['photonews_url'] . '.html';
        }
        else
        {
            $news_detail['nav_paging_photos']['next_photonews_url'] = '#';
        }

        if(!empty($news_detail['prev_photos_visit']))
        {
            $news_detail['nav_paging_photos']['prev_photonews_url'] = $this->config['rel_url'] . 'photo/' . strtolower($news_detail['category_url']) . '/' . $news_detail['news_url'] . '/' . $news_detail['prev_photos_visit']['photonews_url'] . '.html';
        }
        else
        {
            $news_detail['nav_paging_photos']['prev_photonews_url'] = '#';
        }

        // FUNCTION INITIAL EDITOR
        if (isset($editor_json_decode[0]->user_fullname) && !empty($editor_json_decode[0]->user_fullname))
        {
            $news_detail['initial_editor'] = '(brl/' . $this->widget->inisial_editor($editor_json_decode[0]->id) . ')';
        }
        else
        {
            $news_detail['initial_editor'] = '';
        }

        // FUNTION LIST TAG
        if (!empty($mongo_data['tag_news']))
        {
            $news_detail['tag_news'] = $mongo_data['tag_news'];
        }
        else
        {
            $tag_news = cache("query_mobile_en_". $cache_news_id ."-tag_news", function () use ($news_detail){
                $tags = TagNews::leftJoin('tags','tags.id','=','tag_news.tag_news_tag_id')
                                ->where('tag_news_news_id',$news_detail['news_id'])
                                ->get()
                                ->toArray();
                return $tags;
            }, $interval);

            $news_detail['tag_news'] = $tag_news;
        }

        //for pure news content from DB
        $tmp_news_content = $news_detail['news_content'];

        //FUNCTION SPONSOR TAG
        if( !empty($news_detail['sponsor_tag']) )
        {
            $news_detail['reporter_status']     = 'brand';
            $news_detail['reporter']            = $this->view('mobile_en/box/_box_brand', ['brand' => $news_detail['sponsor_tag'], 'TE' => $TE_2 ], true);
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
                $news_detail['reporter']            = $this->view('mobile_en/box/_box_brand', ['brand' => $brand, 'TE' => $TE_2 ], true);
                $news_detail['reporter_style_date'] = 'style="margin-top: 28px;"';
                $news_detail['reporter_status']     = 'brand';
                $init_brand = TRUE;
            }
        }

        //PROMOTE VIDEO
    		if ( !empty($news_detail['news_sponsorship']) || $init_brand == TRUE )
    		{
    				$promote_video = '';
    		}
    		else
    		{
    				$promote_video = $this->config['video_sponsor'];
    		}
        //eof brand image reporter

        //FUNCTION ADDED KODE TRACK EVENT - LINK INSIDE CONTENT
        $news_detail['news_content'] = html_entity_decode($news_detail['news_content']);
        $get_content_link = preg_match('/<a href=".*?">/', $news_detail['news_content'], $matches);
        if (count($get_content_link) > 0) {

            preg_match_all('/<a href="([^>"]*)"/', $news_detail['news_content'], $url_link);
            foreach ($url_link[1] as $key) {
                preg_match('/(<a href="[^>"]*")/', $news_detail['news_content'], $old_link);
                $kode_onclick = "<a href='$key' onclick=\"ga('send', 'event', '$TE_2', 'Hyperlink content', '$key')\"";
                $news_detail['news_content'] = str_replace($old_link, $kode_onclick , $news_detail['news_content']);
            }
        }
        //end kode track event

        // function crosslink
        $cross_view = '';
        $cross = cache('mobile_en_news_crosslink_'. $cache_news_id , function () use($news_detail) {
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
                    $cross_view = $this->view('mobile_en/detail/_crosslink',['cross' => $value],TRUE);
                    $news_detail['news_content'] = str_replace('['.$value['news_related_code'].']',$cross_view, $news_detail['news_content']);
                }
            }
        }
        else
        {
            $news_detail['news_content'] = html_entity_decode($news_detail['news_content']);
        }

        $meta = array(
            'meta_title'         => $news_detail['news_title']. ' - Brilio.net',
            'meta_description'   => $news_detail['news_synopsis'],
            'meta_keywords'      => str_replace(' ', ', ', $news_detail['news_synopsis']),
            'og_url'             => $news_detail['news_url_with_base'],
            'og_image'           => 'http://cdn.klimg.com/newshub.id/' . substr($news_detail['news_image_location_full'], strlen($this->config['klimg_url'])),
            'og_image_secure'    => $news_detail['news_image_location_full'],
            'img_url'            => $this->config['assets_image_url'],
            'expires'            => date("D,j M Y G:i:s T", strtotime($news_detail["news_date_publish"])),
            'last_modifed'       => date("D,j M Y G:i:s", strtotime($news_detail["news_date_publish"])),
            'chartbeat_sections' => ucfirst(strtolower($category_url)),
            'chartbeat_authors'  => $reporter_json_decode[0]->name,
            'meta_alternate'    => $this->config['www_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['m_url_en'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),

        );

        $data = array(
            'full_url'      => $url,
            'meta'          => $meta,
            'TE_2'          => $TE_1,
            'news_data'     => $news_detail,
            'box_announcer' => $this->view('mobile_en/box/_announcer_banner', [], TRUE),
            'related_news'  => array_slice($news_detail['related_news_list'], 0, 3),
            'more_news'     => "",
            'BREADCRUMB'    => $this->breadcrumb($news_detail['category_url']),
            'popular'       => $this->_trending(6, $TE_2),
            'today_tags'        => $this->view('mobile_en/box/_tags_bottom', ['list_tags_bottom' => $this->_today_tags(0, 10, 0, $TE_2, 'EN') ], true),
            'video'             => $this->view('mobile_en/box/promote_video', ['promote_video' => $promote_video ], TRUE),
        );

        if (empty($mongo_data) && ($this->config['json_news_detail'] === TRUE))
        {
            $news_detail['news_content'] = $tmp_news_content;
            $news_detail['news_id'] = (int)$news_detail['news_id'];
            $news_detail['news_date_publish'] = strtotime($news_detail['news_date_publish']);
            writeDataMongo($file, $news_detail, $this->config['mongo_prefix'] . "photo");
        }

        $this->_mobile_render('mobile_en/photo/detail', $data);
    }

    function breadcrumb($category)
    {
        $data_breadcumb['CATEGORY_TITLE'] = $this->_categories['url_to_name'][strtolower($category)];
        $data_breadcumb['CATEGORY_URL'] = strtolower($category);
        return $data_breadcumb;
    }

    function more_stories($news_detail, $TE){

        $total_related = 24;

        if (is_numeric($news_detail['news_date_publish']) )
        {
            $date_publish = date('Y-m-d H:i:s', $news_detail['news_date_publish']);
        }
        else
        {
            $date_publish = $news_detail['news_date_publish'];
        }

        $_v = cache("query_mobile_en_related_per_tag_", function () use($date_publish, $total_related) {
            $news = News::where('news_domain_id', $this->config['domain_id'])
                            ->where('news_level','1')
                            ->where('news.news_date_publish', '<' , $date_publish)
                            ->groupBy('news.news_id')
                            ->orderBy('news_date_publish','DESC')
                            ->take($total_related)
                            ->get()->toArray();
            return $news;
        }, 300);

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

        return $more_stories_list;

    }

}
