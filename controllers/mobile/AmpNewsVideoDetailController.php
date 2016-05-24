<?php

class AmpNewsVideoDetailController extends CController {

    private $_exclude   = [];
    private $_id_cat;
    private $share_url  = '';
    private $_type_news = [0 => 'news',1 => 'photo', 2 => 'video'];
    private $exclude_id = [];

    function __construct() {
        parent::__construct();
        $this->model(['News','Tag', 'TagNews', 'NewsRelated', 'NewsPaging', 'Video'],null, true);
        $this->library(array('table', 'lib_date', 'widget'));
        $this->helper('mongodb');
    }

    function index($category = '', $slug = '', $paging = '') {

        $interval   = WebCache::App()->get_config('cachetime_default');
        $cacheKey = 'amp_mobile_video_news_detail_' . $slug;

        if ($ret = checkCache($cacheKey))
            return $ret;

        if (isset($this->_categories['url_to_id'][$category]))
        {
           $this->_id_cat = $this->_categories['url_to_id'][$category];
        }
        else
        {
          Output::App()->show_404();
        }

        $TE = 'Detail video';
        $TE_2 = 'Detail video';
        $hide_news_html = explode('.html', $slug);
        $keyword_news = $hide_news_html[0];
        $hide_paging_html = explode('.html', $paging);
        $keyword_paging = $hide_paging_html[0];
        $file = $keyword_news . '.html';
        $pos = false;
        $is_paging = false;

        if (empty($paging) || $paging == 'index')
        {

           $file       = $keyword_news; //news keyword
           $keyword    = $keyword_news;
           $mongo_file = $file . '.html';
           // $mongo_file = $_SERVER['REQUEST_URI'];
        }
        else
        {
            $keyword    = $keyword_news; //news keyword
            $file       = $keyword_news . '/' . $paging;
            $mongo_file = $keyword_news . '/' . $paging ;
            // $mongo_file = $_SERVER['REQUEST_URI'];
        }

        $mongo_file = "amp-". $mongo_file;

        if ($this->config['json_news_detail'] === TRUE)
        {
            $mongo_data = readDataMongo($mongo_file, $this->config['mongo_prefix'] . "video");
        }
        else
        {
            $mongo_data = '';
        }
        //CONFIG MONGO DB - development - production
        //GET DATA READ VIDEO
        if (!empty($mongo_data))
        {
            $news_detail = $mongo_data;

            //cek for preview page
            if((date('Y-m-d H:i:s', $news_detail['news_date_publish']) >= date('Y-m-d H:i:s')) && $preview_flag == FALSE)
            {
                Output::App()->show_404();
            }

            if (!isset($news_detail['related_news_list']) ) {
               # code...
               $news_detail['related_news_list'] = $this->get_news_related($news_detail, $TE_2);
            }
            else
            {
              $news_detail['related_news_list'] = array_slice($news_detail['related_news_list'], 0,3);
            }

            $search  = array('!', ' ');
            $replace = array('', '-');
            if ( (str_replace($search, $replace,  strtolower($news_detail['news_category_name'])) ) !== $category )
            {
                echo '<meta http-equiv="Refresh" content="0;URL=' .$news_detail['news_url_full']. '">';
                exit;
            }
        }
        else
        {
            //Try get from DB
            $_video[0] = News::where('news_domain_id', $this->config['domain_id'])
                         ->where('news_type', 2)
                         ->where('news_level', 1)
                         ->where('news_url', $keyword_news)
                         ->where('news_date_publish', '<', date('Y-m-d H:i:s') )
                         ->with('sponsor_tag')
                         ->first();

            if ($_video)
            {
                $_video[0] = $_video[0]->toArray();
                $_video = $this->generate_news_url($_video)[0];
                $news_detail = $_video ;

                //GET TAG
                $tags = cache('amp_query_news_tags_'.$news_detail['news_id'], function () use($news_detail) {
                      return TagNews::leftJoin('tags','tags.id','=','tag_news.tag_news_tag_id')
                       ->where('tag_news_news_id',$news_detail['news_id'])
                       ->get()
                       ->toArray();
                  }, $interval );

               if ($tags)
               {
                    foreach ($tags as $k=>$v)
                   {
                    $tags[$k]['tag_url_full'] = $this->config['rel_url']. 'tag/'. $v['tag_url'][0] .'/'. $v['tag_url'] .'/';
                   }
                    $news_detail['news_tag_list'] = $tags;
               }
               else
               {
                    $news_detail['news_tag_list'] = null;
               }

                //GET VIDEO EMBED
                if (!empty($mongo_data['news_embed_video']))
                {
                    $news_detail['news_embed_video'] = $mongo_data['news_embed_video'];
                }
                else
                {
                    // $news_detail['video_news'] = $this->video_model->get_video_embed($news_detail['news_id']);
                    $_embed_video = Video::where('video_news_id', $news_detail['news_id'])->get()->toArray();

                    if ($_embed_video)
                    {

                        $news_detail['news_embed_video'] = reset($_embed_video);
                    }
                    else
                    {
                        $news_detail['news_embed_video'] = '';
                    }
                }

               //function get crosslink data
               $cross = cache('amp_video_query_news_crosslink_'.$news_detail['news_id'], function () use($news_detail) {
               $cross_ = news_related::where('news_related_news_id',$news_detail['news_id'])->get();
                  if ($cross_)
                  {
                     return $cross_->toArray();
                  }
                  else
                  {
                     return FALSE;
                  }
               } ,  $interval );

               if ($cross)
               {
                  $news_detail['news_crosslink'] = $cross;
               }
               else
               {
                  $news_detail['news_crosslink'] = '';
               }

               $news_detail['related_news_list'] = $this->get_news_related($news_detail, $TE_2);

               //START FUNCTION PAGING
               //cek if the news has paging
               if ($news_detail['has_paging'] == 1){
                  $paging_ = cache('amp_video_query_berita_paging_'. $news_detail['news_id'] , function() use($news_detail) {
                      $paging = news_paging::where('news_paging_status','=',1)->where('news_paging_news_id',$news_detail['news_id'])
                          ->orderBy('news_paging_no','asc')
                          ->get()->toArray();
                      //merubah urutan jika dipilih order berdasarkan yang terbesar
                      if ($paging[0]['news_paging_order']==1){
                          krsort($paging);
                          $_temp=[];
                          foreach ($paging as $key=>$val){
                              $_temp[]=$val;
                          }
                          $paging = $_temp;
                      }
                      return $paging;
                  },  $interval );

                  $news_detail['paging_'] = $paging_;

               }

              if (isset($news_detail['news_image_potrait']) && !empty($news_detail['news_image_potrait']))
              {
                $news_detail['news_image_intro_location_full'] = $news_detail['news_image_location'].'200x100-'. $news_detail['news_image_potrait'];; //set for paging bottom image for intro
              }else{
                $news_detail['news_image_intro_location_full'] = $news_detail['news_image_location_full']; //set for paging bottom image for intro
              }

              // get news content paging base on $paging
              if ($keyword_paging)
              {
                      $is_paging = news_paging::where('news_paging_status','1')
                              ->where('news_paging_url',$keyword_paging)
                              ->where('news_paging_news_id',$news_detail['news_id'])->first();

                  if (!$is_paging)
                    abort(404);
              }

              // set paging content
              if ($is_paging)
              {
                  $news_detail['news_imageinfo'] = $is_paging->news_paging_title;
                  $news_detail['news_content'] = $is_paging->news_paging_content;
                  $news_detail['news_image'] = ($is_paging->news_paging_media == 0? $is_paging->news_paging_path:'');
                  $set_paging_info[0] = $news_detail;
                  $news_detail = $this->generate_news_url($set_paging_info)[0];
              }

              if ($news_detail['has_paging'] == 1)
              {
                  $paging = $news_detail['paging_'];
                  $status_order = $news_detail['paging_'][0]['news_paging_order'] ;
                  $pos = 0;
                  $i = 1;
                  //get current position of news paging
                  if ($is_paging)
                  {
                      foreach($paging as $v)
                      {
                          if ($v['news_paging_id'] == $is_paging->news_paging_id)
                              $pos=$i;
                          $i++;
                      }

                  }

                  //generate nav paging top
                  if ($status_order== 0)
                  {
                      $limit          = 4;
                      $page           = ceil(count($news_detail['paging_']) / $limit);
                      $visit_page_no  = $pos;
                      $total          = count($news_detail['paging_']);
                      $last           = $total - $limit;

                      if ($visit_page_no <= 3)
                      {
                          // for firs 4 item from total page
                          $offset = 0;
                      }
                      elseif ($visit_page_no >= ($last+ 2))
                      {
                          //for the last 4 item from total page
                          $offset = $total - $limit ;
                      }
                      else {
                          $offset = $visit_page_no - 2 ;
                      }

                      $paging_list = cache('amp_video_query_nav_top_paging_'.$keyword_paging. '_' .$news_detail['news_id'], function() use($keyword_paging, $news_detail, $limit, $offset)
                      {
                        return news_paging::where('news_paging_status','1')
                              ->where('news_paging_news_id',$news_detail['news_id'])->skip($offset)->take($limit)->get()->toArray();
                      } ,  $interval  );

                      if ( is_object($paging_list) ) {
                           $news_detail['news_paging_list'] = $paging_list->toArray();
                      }
                      else
                      {
                           $news_detail['news_paging_list'] = $paging_list;
                      }

                  }//eof if status_oreder == 0
                  else
                  {
                      //condition equal status_order == 1 || paging is 'DESC'
                      $limit          = 4;
                      $page           = ceil(count($news_detail['paging_']) / $limit);
                      $visit_page_no  = $pos;//$news_detail['news_paging_detail']['news_paging_no'];
                      $total          = count($news_detail['paging_']);
                      $last           = $total - $limit;

                      if ($visit_page_no >= ($last+2) )
                      {
                          //for the first 4 item from total
                          $offset = 0;
                      }
                      elseif ($visit_page_no < $limit)
                      {
                          //for last 4 item from total
                          $offset = $total - $limit ;
                      }
                      else
                      {
                          $offset = $total - ($visit_page_no + 1);
                          // $offset = $visit_page_no - ($limit+2) ;
                      }

                      $paging_list = cache('amp_video_query_nav_top_paging_'.$keyword_paging. '_' .$news_detail['news_id'], function() use($keyword_paging, $news_detail, $limit, $offset)
                      {
                           return news_paging::where('news_paging_status','1')
                              ->where('news_paging_news_id',$news_detail['news_id'])->skip($offset)->take($limit)->get()->toArray();
                      } ,  $interval );

                      if ( is_object($paging_list) ) {
                    $news_detail['news_paging_list'] = array_reverse($paging_list->toArray());
                 }
                 else
                 {
                    $news_detail['news_paging_list'] = array_reverse($paging_list);
                 }
                  }

                  //generate url nav paging top
                  foreach ($news_detail['news_paging_list'] as $key => $val)
                  {
                      $news_paging_url = $this->config['rel_url'] . 'amp/' . $category . '/' . $news_detail['news_url'] . '/' . $val['news_paging_url']. '.html';
                      if ($keyword_paging)
                      {

                          if ( $val['news_paging_id'] == $is_paging->news_paging_id)
                          {
                              if ($keyword_paging == '')
                              {
                                  $paging_selected = '';
                              }
                              else
                              {
                                  $paging_selected = 'active';
                              }
                              $news_active[$key] = $val;
                          }
                          else
                          {
                              $paging_selected = '';
                          }
                      }
                      else{
                          $paging_selected = '';
                          $news_active = [];
                      }
                      //}
                      $tmp = '<li class="'.$paging_selected.'" ><a href="'.$news_paging_url.'" >'.$val["news_paging_no"].' </a></li> ';
                      $news_detail['news_paging_nav_list_top'][]   = $tmp;
                  }


                  //set next && prev news paging
                  $current_key = key($news_active);
                  $news_detail['news_paging_now_active'] = reset($news_active);
                  $_paging_list = $news_detail['news_paging_list'] ;
                  $news_detail['news_paging_next'] = [];
                  $news_detail['news_paging_prev'] = [];

                  if( empty($keyword_paging) ){
                      //set next news paging in INTRO
                     $_paging_list = reset($_paging_list);
                     $temp['news_paging_url'] = $news_detail['news_url_amp_without_html'] . $_paging_list['news_paging_url']. '.html';
                     $temp['news_paging_title'] = ucwords($_paging_list['news_paging_title']);

                     $split_img_path= explode('/', $_paging_list['news_paging_path']);
                     $filename= end($split_img_path);
                     unset($split_img_path[count($split_img_path) - 1]);
                     $temp['news_paging_image'] = $this->config['klimg_url'] . ($_paging_list['news_paging_media'] == 0? implode('/', $split_img_path).'/150x75-'.$filename : '');
                     $news_detail['news_paging_next'] = $temp;
                  }
                  elseif (isset($_paging_list[$current_key+1]) && !empty($_paging_list[$current_key+1]) &&  !empty($keyword_paging))
                  {
                     //set next news paging
                     $temp['news_paging_url'] = $news_detail['news_url_amp_without_html'] . $_paging_list[$current_key+1]['news_paging_url']. '.html';
                     $temp['news_paging_title'] = ucwords($_paging_list[$current_key+1]['news_paging_title']);

                     $split_img_path= explode('/', $_paging_list[$current_key+1]['news_paging_path']);
                     $filename= end($split_img_path);
                     unset($split_img_path[count($split_img_path) - 1]);
                     $temp['news_paging_image'] = $this->config['klimg_url'] . ($_paging_list[$current_key+1]['news_paging_media'] == 0? implode('/', $split_img_path).'/150x75-'.$filename : '');
                     $news_detail['news_paging_next'] = $temp;
                  }
                  else
                  {
                     $temp['news_paging_url'] = $news_detail['news_url_amp_full'] ;
                     $temp['news_paging_title'] = 'INTRO';
                     $temp['news_paging_image'] = $news_detail['news_image_intro_location_full'];

                     $news_detail['news_paging_next'] = $temp;
                  }

                  if (isset($_paging_list[$current_key-1]) && !empty($_paging_list[$current_key-1]))
                  {
                      //set prev news paging
                     $temp['news_paging_url'] = $news_detail['news_url_amp_without_html'] . $_paging_list[$current_key-1]['news_paging_url']. '.html';
                     $temp['news_paging_title'] = ucwords($_paging_list[$current_key-1]['news_paging_title']);

                     $split_img_path= explode('/', $_paging_list[$current_key-1]['news_paging_path']);
                     $filename= end($split_img_path);
                     unset($split_img_path[count($split_img_path) - 1]);
                     $temp['news_paging_image'] = $this->config['klimg_url'] . ($_paging_list[$current_key-1]['news_paging_media'] == 0? implode('/', $split_img_path).'/150x75-'.$filename : '');
                     $news_detail['news_paging_prev'] = $temp;
                  }
                  else
                  {
                     $temp['news_paging_url'] = $news_detail['news_url_amp_full'] ;
                     $temp['news_paging_title'] = 'INTRO';
                     $temp['news_paging_image'] = $news_detail['news_image_intro_location_full'];

                     $news_detail['news_paging_prev'] = $temp;
                  }
              }//EOF PAGING
            }
            else
            {
                abort(404);
            }

        }//eof when mongo is empty

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


        $news_detail['news_content'] = html_entity_decode($news_detail['news_content']);
        $news_detail['news_content'] = preg_replace('/http:\/\/d25no82dcg1wif.cloudfront.net\/+/', $this->config['klimg_url'].'/', $news_detail['news_content']);

        if (isset($news_detail['news_crosslink']) && !empty($news_detail['news_crosslink'])) {
              $cross = $news_detail['news_crosslink'];
              foreach ($cross as $key=>$value)
              {
                  if (strpos(html_entity_decode($news_detail['news_content']),'['.$value['news_related_code'].']') !== false)
                  {
                      $cross = $this->view('mobile/detail/amp_crosslink',['cross' => $value],TRUE);
                      $news_detail['news_content'] = str_replace('['.$value['news_related_code'].']', $cross , $news_detail['news_content']);
                      // $recommended .= $cross;
                  }
              }
        }

      $data_breadcumb['category']         = $news_detail['news_category_name'];
      $data_breadcumb['category_url']     = strtolower($news_detail['news_category_url']);

        $meta =
        [
            'meta_title'        => $news_detail['news_title'],
            'meta_description'  => $news_detail['news_synopsis'],
            'meta_keywords'     => str_replace(' ', ',', $news_detail['news_synopsis']),
            'og_url'            => $news_detail['news_url_with_base'],
            'og_image'          => 'http://cdn.klimg.com/newshub.id/' . substr($news_detail['news_image_location_full'], strlen($this->config['klimg_url'])),
            'og_image_secure'   => $news_detail['news_image_location_full'],
            'img_url'           => $this->config['assets_image_url'],
            'expires'           => date("D,j M Y G:i:s T", strtotime($news_detail["news_date_publish"])),
            'meta_keywords'     => str_replace(' ', ', ', $news_detail['news_synopsis']),
            'last_modifed'      => gmdate("D,j M Y G:i:s e", strtotime($news_detail["news_date_publish"])),
            'chartbeat_sections'=> ucfirst(strtolower($news_detail['news_category_name'])),
            'chartbeat_authors' => json_decode($news_detail['news_reporter'])[0]->name,//$editor_json_decode[0]->user_fullname,
            'meta_alternate'    => $this->config['m_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        ];

        $data =
        [
            'full_url'        => '',
            'TE_2'              => $TE_2,
            'meta'            => $meta,
            'box_announcer' => $this->view('mobile/box/amp_announcer_banner', [], TRUE),
            'breadcrumb'      => $data_breadcumb,
            'news_data'       => $this->_amp_validation($news_detail),
            'news_related'  => $this->view('mobile/detail/amp_news_related', ['related_news_list' => $news_detail['related_news_list']], TRUE),
            'popular'       => $this->_trending(6, $TE_2),
            'today_tags'    => $this->view('mobile/box/amp_tags_bottom', ['list_tags_bottom' => $this->_today_tags(0, 10, 0, $TE_2) ], true),
            'collect_email' => $this->view('mobile/box/_collect_email', [], true),
            'video'         => $this->view('mobile/box/amp_promote_video', ['promote_video' => $promote_video ], TRUE),
        ];

        if (empty($mongo_data) && ($this->config['json_news_detail'] === TRUE))
        {
            $news_detail['news_content'] = $tmp_content;
            $news_detail['news_id'] = (int)$news_detail['news_id'];
            $news_detail['news_date_publish'] = strtotime($news_detail['news_date_publish']);
            writeDataMongo($mongo_file, $news_detail, $this->config['mongo_prefix'] . "news");
        }

        $ret = $this->_amp_render('mobile/video/amp_newsvideodetail', $data);
        $interval = WebCache::App()->get_config('cachetime_default');
        setCache($cacheKey, $ret, $interval);
        return $ret ;

    }

   function get_news_related($news_detail, $TE){

   $interval   = WebCache::App()->get_config('cachetime_default');
   $cacheKey = 'amp_video_mobile_related_' . MD5($news_detail['news_id']);

   if ($ret = checkCache($cacheKey))
   return unserialize($ret) ;


   $total_related = 24;

   $_v = cache('query_mobile_more_stories_desktop_'.$news_detail['news_id'], function() use( $news_detail, $total_related) {
           return News::join('tag_news','tag_news.tag_news_news_id','=','news.news_id')
                   ->join('tags','tags.id','=','tag_news.tag_news_tag_id')
                   // ->where('tag_news.tag_news_tag_id',$tags[$num_tag]['tag_news_tag_id'])
                   ->where('news_domain_id', $this->config['domain_id'])
                   ->where('tag_news.tag_news_news_id','!=',$news_detail['news_id'])
                   ->where('news_level','1')
                   ->where('news.news_date_publish', '<' ,$news_detail['news_date_publish'])
                   // ->where('news.news_date_publish', '<', date('Y-m-d H:i:s'))
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
      $more_stories_list[$no]['news_url_amp_full'] = $v['news_url_with_base_amp'];
      $more_stories_list[$no]['news_image'] = $v['news_image'];
      $more_stories_list[$no]['news_image_potrait'] = $v['news_image_potrait'];
      $more_stories_list[$no]['news_image_location'] = $v['news_image_location'];
      $no++;
   }
   setCache($cacheKey, serialize($more_stories_list) , $interval);
   return $more_stories_list;

   }

}
