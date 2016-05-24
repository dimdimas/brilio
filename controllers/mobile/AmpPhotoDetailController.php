<?php

class AmpPhotoDetailController extends CController {

    function __construct() {
        parent::__construct();
        $this->model(['News', 'PhotonewsDetail', 'NewsRelated', 'TagNews'], null, true);
        $this->library(array('table', 'lib_date', 'widget'));
        $this->helper('mongodb');
    }

    function index($url_keyword_news = '', $url_paging_news = '')
    {
        $interval = WebCache::App()->get_config('cachetime_default');
        $url_filter = $_SERVER["REQUEST_URI"];
        $cacheKey   = 'amp_photo_mobile_read_photo_content-' . $url_filter;
        $TE_1 = 'Menu';
        $TE_2 = 'Detail Foto';

        $hide_keyword_html = explode('.html', $url_keyword_news);
        $keyword_news = $hide_keyword_html[0];

        $hide_paging_html = explode('.html', $url_paging_news);
        $keyword_paging = $hide_paging_html[0];

        if (empty($url_paging_news))
        {
            $file = $keyword_news;
        }
        else
        {
            $file = $keyword_news . '/' . $url_paging_news;
            $file = str_replace(".html", "", $file);
        }

        //CONFIG MONGO DB - development - production
        $file = "amp-". $file;

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
        }
        else
        {
//            $news_detail = $this->news_model->get_read_photo($keyword_news);
            $news_detail = cache("amp_photo_mobile_query-".$keyword_news, function () use ($keyword_news){
                $rows = News::select('news_id',
                                'news_url',
                                'news_title',
                                'news_content',
                                'news_date_publish',
                                'news_entry',
                                'news_synopsis',
                                'news_reporter',
                                'news_editor',
                                'news_image',
                                'news_image_thumbnail',
                                'news_image_potrait',
                                'news_imageinfo',
                                'news_sensitive',
                                'news_category')
                            ->where('news_domain_id', '=', $this->config['domain_id'])
                            ->where('news_type', '=', '1')
                            ->where('news_level', '=', '1')
                            ->where('news_url', '=', $keyword_news)
                            ->where('news_date_publish', '<', date('Y-m-d H:i:s'))
                            ->get()
                            ->toArray();
                return $rows[0];
            }, $interval);
        }

        if (count($news_detail) == 0)
        {
            Output::App()->show_404();
        }

        $reporter_json = $news_detail['news_reporter'];
        $reporter_json_decode = json_decode($reporter_json);
        $editor_json = $news_detail['news_editor'];
        $editor_json_decode = json_decode($editor_json);

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
        $news_detail['url_share'] = $this->config['base_url'] . 'photo/' . strtolower($category_url) . '/' . $news_detail['news_url'] . '.html';
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
//            $news_detail['data_photos_visit'] = $this->photonews_detail_model->data_visit_photos($news_detail['news_id'], $keyword_paging);
            $data_visit_photos = cache("amp_photo_mobile_query-".$cacheKey."data_visit_photos", function () use($all_photos, $keyword_paging) {
                $photos = clone $all_photos;

                if($keyword_paging == '')
                {
                    $photos = $photos->get()
                                  ->toArray();
//                    echopre($photos);
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
            $prev_photos = cache("amp_photo_mobile_query-".$cacheKey."prev_photos_visit", function () use ($visit_page_no, $get_photo_list) {
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
//            $news_detail['next_photos_visit'] = $this->photonews_detail_model->next_visit_photos($news_detail['news_id'], $news_detail['data_photos_visit']['photonews_id']);
            $next_photos = cache("amp_photo_mobile_query-".$cacheKey."next_photos_visit", function () use ($visit_page_no, $get_photo_list) {

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
            $news_detail['nav_paging_photos']['next_photonews_url'] = $this->config['rel_url'] . 'amp/photo/' . strtolower($news_detail['category_url']) . '/' . $news_detail['news_url'] . '/' . $news_detail['next_photos_visit']['photonews_url'] . '.html';
        }
        else
        {
            $news_detail['nav_paging_photos']['next_photonews_url'] = '#';
        }

        if(!empty($news_detail['prev_photos_visit']))
        {
            $news_detail['nav_paging_photos']['prev_photonews_url'] = $this->config['rel_url'] . 'amp/photo/' . strtolower($news_detail['category_url']) . '/' . $news_detail['news_url'] . '/' . $news_detail['prev_photos_visit']['photonews_url'] . '.html';
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
//            $news_detail['tag_news'] = $this->tag_news_model->get_tags_news($news_detail['news_id']);
            $tag_news = cache("amp_photo_mobile_query-". $cache_news_id ."-tag_news", function () use ($news_detail){
                $tags = TagNews::leftJoin('tags','tags.id','=','tag_news.tag_news_tag_id')
                                ->where('tag_news_news_id',$news_detail['news_id'])
                                ->get()
                                ->toArray();
                return $tags;
            }, $interval);

            $news_detail['tag_news'] = $tag_news;
        }

        $tmp_content = $news_detail['news_content'];

        //FUNCTION SPONSOR TAG
        if( !empty($news_detail['sponsor_tag']) )
        {
            $news_detail['reporter_status'] = 'brand';
            $news_detail['reporter']['name']   = $this->view('mobile/box/amp_box_brand', [ 'brand' => $news_detail['sponsor_tag'], 'TE' => $TE_2], true);
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
                $news_detail['reporter']['name']    = $this->view('mobile/box/amp_box_brand', ['brand' => $brand, 'TE' => $TE_2 ], true);
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


        // function crosslink
        $cross_view = '';
        $cross = cache('amp_photo_mobile_news_crosslink_'. $cache_news_id , function () use($news_detail) {
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
                    $cross_view = $this->view('mobile/detail/amp_crosslink',['cross' => $value],TRUE);
                    $news_detail['news_content'] = str_replace('['.$value['news_related_code'].']',$cross_view, $news_detail['news_content']);
                }
            }
        }
        else
        {
            $news_detail['news_content'] = html_entity_decode($news_detail['news_content']);
        }

        if (!empty($mongo_data['related_news_list']))
        {
            $news_detail['related_news_list'] = $mongo_data['related_news_list'];
        }
        else
        {
            $news_detail['related_news_list'] = $this->get_related($news_detail, $TE_2);
        }


        // $this->render('mobile/templates/detail', $dt);
        $meta = array(
            'meta_title'         => $news_detail['news_title']. ' - Brilio.net',
            'meta_description'   => $news_detail['news_synopsis'],
            'meta_keywords'      => str_replace(' ', ', ', $news_detail['news_synopsis']),
            'og_url'             => $this->config['base_url'] . 'photo/' . strtolower($category_url) . '/' . $news_detail["news_url"] . '.html',
            'og_image'           => 'http://cdn.klimg.com/newshub.id/photonews/' . $year . '/' . $month . '/' . $date . '/' . $news_detail['news_id'] . '/657xauto-' . $news_detail['news_image'],
            'og_image_secure'    => $this->config['klimg_url'] . 'news/' . $year . '/' . $month . '/' . $date . '/' . $news_detail['news_id'] . '/657xauto-' . $news_detail['news_image'],
            'img_url'            => $this->config['assets_image_url'],
            'expires'            => date("D,j M Y G:i:s T", strtotime($news_detail["news_date_publish"])),
            'last_modifed'       => date("D,j M Y G:i:s", strtotime($news_detail["news_date_publish"])),
            'chartbeat_sections' => ucfirst(strtolower($category_url)),
            'chartbeat_authors'  => $reporter_json_decode[0]->name,
            'meta_alternate'    => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );
        $data = array(
            'full_url'      => $url,
            'meta'          => $meta,
            'TE_2'          => $TE_1,
            'news_data'     => $this->_amp_validation($news_detail),
            'box_announcer' => $this->view('mobile/box/amp_announcer_banner', [], TRUE),
            // 'related_news'  => $news_detail['related_news_list'],
            'news_related'  => $this->view('mobile/detail/amp_news_related', ['related_news_list' => $news_detail['related_news_list']], TRUE),
            'BREADCRUMB'    => $this->breadcrumb($news_detail['category_url']),
            'popular'       => $this->_trending(6, $TE_2),
            'today_tags'    => $this->view('mobile/box/amp_tags_bottom', ['list_tags_bottom' => $this->_today_tags(0, 10, 0, $TE_2) ], true),
            'video'         => $this->view('mobile/box/amp_promote_video', ['promote_video' => $promote_video ], TRUE),
        );

        if (empty($mongo_data) && ($this->config['json_news_detail'] === TRUE))
        {
            $news_detail['news_id'] = (int)$news_detail['news_id'];
            $news_detail['news_date_publish'] = strtotime($news_detail['news_date_publish']);
            writeDataMongo($file, $news_detail, $this->config['mongo_prefix'] . "photo");
        }
        $this->_amp_render('mobile/photo/amp_detail', $data);
    }

    function breadcrumb($category)
    {
        $data_breadcumb['CATEGORY_TITLE'] = $this->_categories['url_to_name'][strtolower($category)];
        $data_breadcumb['CATEGORY_URL'] = strtolower($category);
        return $data_breadcumb;
    }

    function get_related($news_detail, $TE){

      $total_related = 24;

      if (is_numeric($news_detail['news_date_publish']) )
      {
          $date_publish = date('Y-m-d H:i:s', $news_detail['news_date_publish']);
      }
      else
      {
          $date_publish = $news_detail['news_date_publish'];
      }

      $_v = cache("mobile-query-related-per-tag", function () use($date_publish, $total_related) {
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
         $more_stories_list[$no]['news_url_amp_full'] = $v['news_url_with_base_amp'];
         $more_stories_list[$no]['news_image'] = $v['news_image'];
         $more_stories_list[$no]['news_image_potrait'] = $v['news_image_potrait'];
         $more_stories_list[$no]['news_image_location'] = $v['news_image_location'];
         $no++;
      }

      return $more_stories_list;


    }

}
