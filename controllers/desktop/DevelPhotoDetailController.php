<?php

class DevelPhotoDetailController extends CController {

    private $_exclude = [];
    private $_id_cat;

    function __construct()
    {
        parent::__construct();
        $this->model(array('news_model', 'tags_model', 'jsview_model', 'what_happen_model', 'tag_news_model', 'today_tags_model', 'news_paging_model', 'news_related_model', 'photonews_detail_model', 'video_model'));
        $this->model(['News', 'PhotonewsDetail', 'TagNews', 'Tag', 'NewsRelated'], null, true);
        $this->library(array('table', 'lib_date', 'widget', 'SponsorLinkContent'));
        $this->helper('mongodb');
    }

    function index($url_keyword_news = '', $url_paging_news = '')
    {
        $this->SponsorLinkContent->scanSponsorlinkKeyword();
      // $news_detail = $this->SponsorLinkContent->scanSingleNews($news_detail, $TE);
        $interval = WebCache::App()->get_config('cachetime_default');
        $url_filter = $_SERVER["REQUEST_URI"];
        $cacheKey   = 'desktop_read_photo_content-' . $url_filter;
        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        $hide_keyword_html = explode('.html', $url_keyword_news);
        $keyword_news      = $hide_keyword_html[0];
        $hide_paging_html  = explode('.html', $url_paging_news);
        $keyword_paging    = $hide_paging_html[0];
        $file              = $keyword_news;
        $TE  = 'Detail foto';

        if (empty($url_paging_news))
        {
            $mongo_file = $url_keyword_news;
        }
        else
        {
            $mongo_file = $keyword_news . '/' . $url_paging_news;
        }

        if ($this->config['json_news_detail'] === TRUE) {
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
        }
        else
        {
            $_news_detail = cache("dekstop_photo_query_more_stories_".$keyword_news, function () use ($keyword_news){
                $rows = News::where('news_domain_id', '=', $this->config['domain_id'])
                            ->where('news_type', '=', '1')
                            ->where('news_level', '=', '1')
                            ->where('news_url', '=', $keyword_news)
                            ->with('news_tag_list')
                            ->get()
                            ->toArray();
                return $rows;
            }, $interval);

            if ($_news_detail) {
                $news_detail = $this->generate_news_url($_news_detail)[0];
                // $news_detail['related_news_list'] = $this->more_stories($news_detail, $TE);

                //get all photos
                $news_detail['all_photos'] = PhotonewsDetail::where('photonews_newsid', '=', $news_detail['news_id'])
                                ->orderby('photonews_id', 'ASC')
                                ->get()
                                ->toArray();

                $total_list_photos = count($news_detail['all_photos']);
                $photos_no = 0;

                //get possition current photos from allphotos
                if ($keyword_paging == '' || empty($keyword_paging) )
                {
                    $visit_page_no = 0 ; // 0 get from array,
                    $news_detail['news_active_photos'] = $news_detail['all_photos'][0];
                    $news_detail['news_active_photos']['photonews_image_location'] = $this->config['klimg_url'].$news_detail['news_image_location_raw'].'657xauto-'.$news_detail['all_photos'][0]['photonews_src'];;
                    $news_detail['news_active_photos']['photonews_news_photo_no'] = 1;
                    $news_detail['news_data_next_photo']['news_photo_url'] = $news_detail['news_url_without_html']. $news_detail['all_photos'][0]['photonews_url'].'.html';
                }
                if( !empty($keyword_paging) )
                {
                    foreach ($news_detail['all_photos'] as $key => $value)
                    {
                        //get current position photos in array
                        if ($news_detail['all_photos'][$key]['photonews_url'] == $keyword_paging )
                        {
                            $photos_no = $key;
                            $news_detail['news_active_photos'] = $news_detail['all_photos'][$photos_no] ;
                            $news_detail['news_active_photos']['photonews_news_photo_no'] = $key+1;
                            $news_detail['news_active_photos']['photonews_image_location'] = $this->config['klimg_url'].$news_detail['news_image_location_raw'].'657xauto-'.$news_detail['all_photos'][$photos_no]['photonews_src'];;
                        }
                    }
                }

                //set next photo data
                if ( ($photos_no + 2) <= $total_list_photos || empty($keyword_paging) )
                {
                    $news_detail['news_data_next_photo'] = $news_detail['all_photos'][$photos_no+1];
                    $news_detail['news_data_next_photo']['news_photo_url'] = $news_detail['news_url_without_html']. $news_detail['all_photos'][$photos_no+1]['photonews_url'].'.html';
                }
                if ( ($photos_no - 1) >= 0 && !empty($keyword_paging) )
                {
                    $news_detail['news_data_prev_photo'] = $news_detail['all_photos'][$photos_no-1];
                    $news_detail['news_data_prev_photo']['news_photo_url'] = $news_detail['news_url_without_html']. $news_detail['all_photos'][$photos_no-1]['photonews_url'].'.html';
                }

                $limit  = 9;
                $last   = $total_list_photos - $limit;

                if ($photos_no+3 < $limit)
                {
                    $offset = 9;
                    $start  = 0;
                }
                elseif($photos_no-3 >= $last)
                {
                    $start = $last;
                    $offset = $total_list_photos;
                }
                else
                {
                    $start = $photos_no-3;
                    $offset = $start+$limit;
                }

                foreach ($news_detail['all_photos'] as $key => $value)
                {
                    if ($start < $offset)
                    {

                        $tmp['news_thumbnail_url'] = $news_detail['news_url_without_html'].$news_detail['all_photos'][$start]['photonews_url'].'.html';
                        $tmp['thumbnail_url'] = $this->config['klimg_url'].$news_detail['news_image_location_raw'].'105x105-'.$news_detail['all_photos'][$start]['photonews_src'];

                        //set active class thumbnail
                        if ($news_detail['all_photos'][$start]['photonews_id'] == $news_detail['news_active_photos']['photonews_id'])
                            $tmp['thumbnail_state'] = 'active';
                        else
                            $tmp['thumbnail_state'] = '';

                        $news_detail['news_photos_thumbnail_list'][] = $tmp;
                    }
                    $start++;
                }

                //function crosslink
                $cross = cache('desktop_news_crosslink_'. $news_detail['news_id'] , function () use($news_detail) {
                    $cross_ = news_related::where('news_related_news_id',$news_detail['news_id'])->get();

                    if ($cross_)
                        return $cross_->toArray();
                    else
                        return FALSE;
                });

                if ($cross)
                    $news_detail['news_crosslink'] = $cross;
                else
                    $news_detail['news_crosslink'] = '';

                // FUNCTION MORE STORIES
                $news_detail['related_news_list'] = $this->more_stories($news_detail, $TE);
            }
            else
            {
                Output::App()->show_404();
            }
            // echoPre($news_detail);
        }//end get from DB

        // @var tag_id & hutuf_awal_tags for related
        $tag_id  = '';
        $init_brand = FALSE;

        if ($news_detail['news_tag_list'])
        {
            foreach ($news_detail['news_tag_list'] as $a => $b)
            {
                foreach ($this->config['tag_id_brand'] as $k => $v )
                {
                        if ($k == $b['tag_news_tag_id'])
                        {
                            $brand                     = array();
                            $brand['BRAND_IMG']        = $this->config['assets_image_url_v2'] . $this->config['tag_id_brand'][$k][0];
                            $brand['BRAND_STYLE']      = $this->config['tag_id_brand'][$k][1];
                            $news_detail['reporter_status'] = 'brand';
                            $news_detail['reporter']   = $this->view('desktop/box/_box_brand', $brand, true);
                            $news_detail['reporter_style_date'] = 'style="margin-top: 28px;"';
                            $init_brand = TRUE;
                        }
                }
            }
        }

        if($init_brand == FALSE)
        {
            foreach ($this->config['keyword_brand'] as $key => $value)
            {
                if ($key == $keyword_news )
                {
                    $brand                     = array();
                    $brand['BRAND_IMG']        = $this->config['assets_image_url_v2'] . $this->config['keyword_brand'][$key][0];
                    $brand['BRAND_STYLE']      = isset($this->config['tag_id_brand'][1]) ? $this->config['tag_id_brand'][$k][1] : '';
                    $news_detail['reporter']   = $this->view('desktop/box/_box_brand', $brand, true);
                    $news_detail['reporter_style_date'] = 'style="margin-top: 28px;"';
                }
            }
        }
        //eof brand image reporter

        $tmp_content = $news_detail['news_content'];

        if ($news_detail['news_crosslink'])
        {
            foreach ($news_detail['news_crosslink'] as $key=>$value)
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
        $data_breadcrumb['CATEGORY_TITLE'] = $news_detail['news_category_name'];
        $data_breadcrumb['CATEGORY_URL']   = strtolower($news_detail['news_category_url']);
        // echoPre($news_detail);
        //FUNCTION REDIRECT 404 IF CAN'T READ NEWS PHOTO
        if (count($news_detail) == 0)
        {
            Output::App()->show_404();
        }

        $url = $this->config['base_url']. 'photo' . '/' . strtolower($news_detail['news_category_name']) . '/' . $news_detail['news_url'] . '.html';

        $meta =
        [
            'meta_title'        => 'Brilio - '.$news_detail['news_title'],
            'meta_description'  => $news_detail['news_synopsis'],
            'meta_keywords'     => str_replace(' ', ', ', $news_detail['news_synopsis']),
            'og_url'            => $news_detail['news_url_with_base'],
            'og_image'          => 'http://cdn.klimg.com/newshub.id/' . substr($news_detail['news_image_location_full'], strlen($this->config['klimg_url'])),
            'og_image_secure'   => $news_detail['news_image_location_full'],
            'img_url'           => $this->config['assets_image_url_v2'],
            'expires'           => date("D,j M Y G:i:s T", strtotime($news_detail["news_date_publish"])),
            'meta_keywords'     => str_replace(' ', ', ', $news_detail['news_synopsis']),
            'last_modifed'      => gmdate("D,j M Y G:i:s e", strtotime($news_detail["news_date_publish"])),
            'chartbeat_sections'=> ucfirst(strtolower($news_detail['news_category_name'])),
            'chartbeat_authors' => @(isset($news_detail['news_reporter']) ? json_decode($news_detail['news_reporter'])[0]->name : '' ),
            'meta_alternate'    => $this->config['m_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
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
        ];
        // echoPre($data);
        if (empty($mongo_data) && ($this->config['json_news_detail'] === TRUE))
        {
            $news_detail['news_id'] = (int) $news_detail['news_id'];
            $news_detail['news_content'] = $tmp_content;
            $news_detail['news_date_publish'] = strtotime($news_detail['news_date_publish']);
            $news_detail = array_merge($news_detail, $json);
            writeDataMongo($mongo_file, $news_detail, $this->config['mongo_prefix'] . "photo");
        }

        $ret      = $this->_render('desktop/photo/develphotodetail', $data);
        setCache($cacheKey, $ret, $interval);
        return $ret;
    }


    function more_stories($news_detail, $TE){
        $interval   = WebCache::App()->get_config('cachetime_default');
        $cacheKey = 'desktop_preview_photo_more_stories_' . MD5($news_detail['news_id']);

        if ($ret = checkCache($cacheKey))
        return unserialize($ret) ;

        $total_related = 24;

        $_v = cache('query_preview_photo_more_stories_'.$news_detail['news_id'], function() use( $news_detail, $total_related) {
                return News::where('news_domain_id', $this->config['domain_id'])
                        ->where('news_level','1')
                        ->where('news.news_date_publish', '<' ,$news_detail['news_date_publish'])
                        ->groupBy('news.news_id')
                        ->orderBy('news_date_publish','DESC')
                        ->take($total_related)
                        ->get()->toArray();
            }, $interval );

        foreach ($_v as $k => $v)
        {
            $_v[$k]['TE'] = $TE;
        }

        $more_stories_list = $this->generate_news_url($_v);
        setCache($cacheKey, serialize($more_stories_list) , $interval);

        return $more_stories_list;

    }

}
?>
