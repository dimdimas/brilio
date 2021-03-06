<?php

class AmpNewsDetailController extends CController {

     private $_exclude = [];
     private $_id_cat;
     private $share_url = '';
     private $_type_news = [0 => 'news',1 => 'photo', 2 => 'video'];
     private $exclude_id = [];

     function __construct()
     {
          parent::__construct();
          $this->library(array('table', 'lib_date', 'widget'));
          $this->model(['News','Tag', 'TagNews', 'NewsRelated', 'NewsPaging', 'SponsorTag'],null, true);
          $this->helper('mongodb');
     }

     function index($category = '', $slug = '', $url_paging_news = '')
     {
        $interval   = WebCache::App()->get_config('cachetime_default');

        if (empty($url_paging_news) ) {
        $cacheKey = 'amp_mobile_read_news_content_' . $slug;
        }
        else{
            $cacheKey = 'amp_mobile_read_news_content_' . $slug. '-' .$url_paging_news;
        }

        if ($ret = checkCache($cacheKey))
        {
              return $ret;
        }

        if (isset($this->_categories['url_to_id'][$category]))
        {
              $this->_id_cat = $this->_categories['url_to_id'][$category];
        }
        else
        {
              Output::App()->show_404();
        }

        $TE               = 'Detail pages';
        $TE_2             = 'Detail pages';
        $hide_news_html   = explode('.html', $slug);
        $keyword_news     = $hide_news_html[0];
        $hide_paging_html = explode('.html', $url_paging_news);
        $keyword_paging   = $hide_paging_html[0];
        $url_news_cek     = explode("-split", $slug);
        $pos              = false;
        $is_paging        = false;
        $split_cat        = explode('preview-', $category);

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

        if (empty($url_paging_news) || $url_paging_news == 'index')
        {
          if (count($url_news_cek) > 1)
          {
             $file       = $url_news_cek[0]; //news keyword
             $keyword    = $url_news_cek[0];
             $mongo_file = $keyword_news ;
          }
          else
          {
             $file       = $keyword_news; //news keyword
             $keyword    = $keyword_news;
             $mongo_file = $file;
          }
        }
        else
        {
          $keyword    = $keyword_news; //news keyword
          $file       = $keyword_news . '/' . $url_paging_news;
          $mongo_file = $keyword_news . '/' . $url_paging_news ;
        }

        // for amp
        $mongo_file = "amp-". $mongo_file;

        if ($this->config['json_news_detail'] === TRUE)
        {
          $mongo_data = readDataMongo($mongo_file, $this->config['mongo_prefix'] . "news");
        }
        else
        {
          $mongo_data = '';
        }

        if (!empty($mongo_data))
        {
          //cek for preview page
          $news_detail = $mongo_data;
          if((date('Y-m-d H:i:s', $news_detail['news_date_publish']) >= date('Y-m-d H:i:s')) && $preview_flag == FALSE)
          {
              Output::App()->show_404();
          }
          //del '!' and replace ' ' to '-'
          $search  = array('!', ' ');
          $replace = array('', '-');
          if ( (str_replace($search, $replace,  strtolower($news_detail['news_category_name'])) ) !== $category )
          {
            echo '<meta http-equiv="Refresh" content="0;URL=' .$news_detail['news_url_full']. '">';
              exit;
          }

          if (empty($news_detail['related_news_list']))
          {
            $news_detail['related_news_list'] = $this->more_stories($news_detail, $news_detail['news_TE']);
          }

          $news_detail['news_content'] = html_entity_decode($news_detail['news_content']);
          $news_detail['news_content'] = preg_replace('/http:\/\/d25no82dcg1wif.cloudfront.net\/+/', $this->config['klimg_url'].'/', $news_detail['news_content']);
        }
        else
        {
          //Try get news from DB
          $_news[0] = News::published()
                    ->where('news_url', $keyword)
                    ->with('sponsor_tag')
                    ->first();

          // cek apakah url yg digunakan merupakan url dari halaman paging
          if ($_news[0])
              $_news[0] = $_news[0]->toArray();
          else
              abort(404);
          //Generate News URL
          $_news = $this->generate_news_url($_news)[0];
          $news_detail = $_news;

          //cek for preview page
          if($news_detail['news_date_publish'] >= date('Y-m-d H:i:s') && $preview_flag == FALSE)
              Output::App()->show_404();

          $tags = cache('amo_query_news_tags_'.$news_detail['news_id'], function () use($news_detail) {
            return TagNews::leftJoin('tags','tags.id','=','tag_news.tag_news_tag_id')
                   ->where('tag_news_news_id',$news_detail['news_id'])
                   ->get()
                   ->toArray();
          },  $interval );

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

          //function crosslink
           $cross = cache('amp_query_news_crosslink_'.$news_detail['news_id'], function () use($news_detail) {
              $cross_ = news_related::where('news_related_news_id',$news_detail['news_id'])->get();
              if ($cross_) {
                 return $cross_->toArray();
              } else {
                 return FALSE;
              }
            } ,  $interval );
           // echoPre($cross);
           if ($cross)
           {
              $news_detail['news_crosslink'] = $cross;
           }
           else
           {
              $news_detail['news_crosslink'] = '';
           }
          // end of cross link

          //START FUNCTION PAGING
          //cek if the news has paging
          if ($news_detail['has_paging'] == 1)
          {
            $TE = 'Detail paging';
            $TE_2 = 'Detail paging';
            $paging_ = cache('amp_query_berita_paging_'. $news_detail['news_id'] , function() use($news_detail) {
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

          // get news paging base on $paging
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

               $paging_list = cache('amp_query_nav_top_paging_'.$keyword_paging. '_' .$news_detail['news_id'], function() use($keyword_paging, $news_detail, $limit, $offset)
               {
                   return news_paging::where('news_paging_status','1')
                       ->where('news_paging_news_id',$news_detail['news_id'])->skip($offset)->take($limit)->get()->toArray();
               }  );

               if (   is_object($paging_list) ) {
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

               $paging_list = cache('amp_query_nav_top_paging_'.$keyword_paging. '_' .$news_detail['news_id'], function() use($keyword_paging, $news_detail, $limit, $offset)
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
               $tmp = '<li class="'.$paging_selected.'" ><a href="'.$news_paging_url.'">'.$val["news_paging_no"].' </a></li> ';
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
              $news_detail['news_paging_next']['intro_active_state'] = 'active';

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
              $temp['news_paging_url'] = $news_detail['news_url_amp_full'];
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
        } //EOF PAGING

          // CHECK FOR SPLIT CONTENT
          $news_detail['news_content'] = explode('<!-- splitter content -->',html_entity_decode($news_detail['news_content']));
          $_split = [];
          if( count($news_detail['news_content']) > 1)
          {
                $url_split = preg_split('/\-splitnews\-|\.html+/', htmlspecialchars_decode($slug));

                if ( count($url_split) == 1 || empty($url_split[1])) {
                     $page = 1;
                }
                else
                {
                     if ($url_split[1] == 1)
                          $page = 1;
                     else
                          $page = $url_split[1];
                }

                if (empty($page) || $page == 1)
                {
                      $news_detail['news_content']         = html_entity_decode($news_detail['news_content'][0]);
                      $selanjutnya                         = $page + 1;
                      $_split['split_url_prev']            = null;
                      $_split['split_url_next']            = $this->config['rel_url'] . 'amp/' . str_replace('!', '', $category) . '/' . $url_split[0] . '-splitnews-' . $selanjutnya . '.html';
                }
                elseif ($page == count($news_detail['news_content']) && $page != 1 ) {

                      $last_index                  = count($news_detail['news_content']) - 1;
                      $news_detail['news_content'] = html_entity_decode($news_detail['news_content'][$last_index]);
                      $sebelum                     = $page - 1;
                      $_split['split_url_prev']    = $this->config['rel_url'] . 'amp/' . str_replace('!', '', $category) . '/' . $url_split[0] . '-splitnews-' . $sebelum . '.html';
                      $_split['split_url_next']    = null;
                }
                else
                {
                      $index                       = $page - 1  ;
                      $news_detail['news_content'] = html_entity_decode($news_detail['news_content'][$index]);
                      $selanjutnya                 = $page + 1;
                      $sebelum                     = $page - 1;
                      $_split['split_url_prev']    = $this->config['rel_url'] . 'amp/' . str_replace('!', '', $category) . '/' . $url_split[0] . '-splitnews-' . $sebelum . '.html';
                      $_split['split_url_next']    = $this->config['rel_url'] . 'amp/' . str_replace('!', '', $category) . '/' . $url_split[0]  . '-splitnews-' . $selanjutnya . '.html';
                }

                $news_detail['news_split_data'] = $_split;
          }
          else
          {
                $news_detail['news_content'] = $news_detail['news_content']['0'];
                $news_detail['news_split_data'] = '';
          }
          // eof SPLIT CONTENT

          //what do you think FB
          if ($news_detail['news_sensitive'] == 1)
          {
                $news_detail['url_share'] = '' ;
          }
          else
          {
                $news_detail['url_share'] =  $this->config['base_url'] . 'amp/' . $category . '/' . $news_detail['news_url'] . '.html';
          }
          //eof what do you think

          //news_related
          $news_detail['related_news_list'] = $this->get_news_related($news_detail, $news_detail['news_tag_list']);

          $news_detail['news_content'] = html_entity_decode($news_detail['news_content']);
          $news_detail['news_content'] = preg_replace('/http:\/\/d25no82dcg1wif.cloudfront.net\/+/', $this->config['klimg_url'].'/', $news_detail['news_content']);

        } // eof else when mongo data is empty

        //save for raw news_content before processed.
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
                'meta_title'         => $news_detail['news_title'],
                'meta_description'   => $news_detail['news_synopsis'],
                'meta_keywords'      => str_replace(' ', ',', $news_detail['news_synopsis']),
                'og_url'             => $news_detail['news_url_with_base'],
                'og_image'           => 'http://cdn.klimg.com/newshub.id/' . substr($news_detail['news_image_location_full'], strlen($this->config['klimg_url'])),
                'og_image_secure'    => $news_detail['news_image_location_full'],
                'img_url'            => $this->config['assets_image_url'],
                'expires'            => date("D,j M Y G:i:s T", strtotime($news_detail["news_date_publish"])),
                'meta_keywords'      => str_replace(' ', ', ', $news_detail['news_synopsis']),
                'last_modifed'       => gmdate("D,j M Y G:i:s e", strtotime($news_detail["news_date_publish"])),
                'chartbeat_sections' => ucfirst(strtolower($news_detail['news_category_name'])),
                'chartbeat_authors'  => json_decode($news_detail['news_reporter'])[0]->name,
                'meta_alternate'     => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
                'iframe_kl'          => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
          ];

          $data =
          [
                'full_url'      => '',
                'TE_2'          => $TE_2,
                'meta'          => $meta,
                'box_announcer' => $this->view('mobile/box/amp_announcer_banner', [], TRUE),
                'breadcrumb'    => $data_breadcumb,
                'news_data'     => $this->_amp_validation($news_detail),
                'news_related'  => $this->view('mobile/detail/amp_news_related', ['related_news_list' => $news_detail['related_news_list']], TRUE),
                'popular'       => $this->_trending(6, $TE_2),
                'split_data'    => $news_detail['news_split_data'],
                'today_tags'    => $this->view('mobile/box/amp_tags_bottom', ['list_tags_bottom' => $this->_today_tags(0, 10, 0, $TE_2) ], true),
                'collect_email' => $this->view('mobile/box/_collect_email', [], true),
                'video'         => $this->view('mobile/box/amp_promote_video', ['promote_video' => $promote_video ], TRUE),
          ];

          if (empty($mongo_data) && ($this->config['json_news_detail'] === TRUE))
          {
            $news_detail['news_content']      = $tmp_content;
            $news_detail['news_id']           = (int)$news_detail['news_id'];
            $news_detail['news_date_publish'] = strtotime($news_detail['news_date_publish']);
            writeDataMongo($mongo_file, $news_detail, $this->config['mongo_prefix'] . "news");
          }

          $ret = $this->_amp_render('mobile/detail/amp_m_news_detail', $data);
          setCache($cacheKey, $ret, $interval);
          return $ret;
     }

     function get_news_related($news_detail, $tags){
      $interval   = WebCache::App()->get_config('cachetime_default');
      $cacheKey = 'amp_mobile_desktop_related_' . MD5($news_detail['news_id']);

      if ($ret = checkCache($cacheKey))
        return unserialize($ret) ;

          $relstory = [];
          $temp_news_related = [];
          $news_index = 0;
          $total_related = 3; // count of needed news

          // if (empty($tags)) {
          //     $related_news_list = '';
          //     return $this->view('mobile/news/_news_related', ['related_news_list' => $related_news_list], TRUE);
          // }

          $relstory = cache('amp_query_related_story_'.$news_detail['news_id'], function() use($tags, $news_detail, $temp_news_related, $news_index, $total_related) {

                if ( empty($tags) )
                {
                    //condition without tag, take news by active news category
                     $_v = TagNews::join('news', 'tag_news.tag_news_news_id', '=', 'news.news_id')
                                ->join('tags','tags.id','=','tag_news.tag_news_tag_id')
                                ->where('news.news_domain_id', $this->config['domain_id'])
                                ->where('tag_news.tag_news_news_id', '!=', $news_detail['news_id'])
                                ->where('news.news_level', 1)
                                ->where('news.news_date_publish', '<' ,$news_detail['news_date_publish'])
                                ->where('news.news_category', $news_detail['news_category'])
                                ->groupBy('news.news_id')
                                ->orderBy('news.news_date_publish', 'desc')
                                ->limit(3)
                                ->get()->toArray();
                                // ->toSql();
                    if (count($_v))
                          {
                                $id = 1;
                                foreach ($_v as $v)
                                {
                                     if($news_index > ($total_related-1)) break;
                                     $temp_news_related[$news_index] = $v;
                                     $reg_id[$id] = $temp_news_related[$news_index]['news_id'];      // register id each news $_v (related news)
                                     $id++;
                                     $news_index++;
                                }
                          }

                    $rel = $temp_news_related;
                }
                else
                {
                    //condition with tags from active news
                    // $tags = array_reverse($tags);
                    for ($num_tag=0 ; $num_tag<count($tags) ; $num_tag++)
                    {
                         $_v = News::join('tag_news','tag_news.tag_news_news_id','=','news.news_id')
                                      ->join('tags','tags.id','=','tag_news.tag_news_tag_id')
                                      ->where('tag_news.tag_news_tag_id',$tags[$num_tag]['tag_news_tag_id'])
                                      ->where('news_domain_id', $this->config['domain_id'])
                                      ->where('tag_news.tag_news_news_id','!=',$news_detail['news_id'])
                                      ->where('news_level','1')
                                      ->where('news.news_date_publish', '<', date('Y-m-d H:i:s'))
                                      ->groupBy('news.news_id')
                                      ->orderBy('news_date_publish','DESC')
                                      // ->take(10)
                                      ->get()->toArray();

                         if (count($_v))
                              {
                                    $id = 1;
                                    foreach ($_v as $v)
                                    {
                                         if($news_index > ($total_related-1)) break;
                                         $temp_news_related[$news_index] = $v;
                                         $reg_id[$id] = $temp_news_related[$news_index]['news_id']; // register id each news $_v (related news)
                                         $id++;
                                         $news_index++;
                                    }
                              }
                         $rel = $temp_news_related;
                         if (count($_v) >= 10 ) break;
                    }
                }

                if (count($_v) < $total_related)
                {
                     $curr_news_id = [$news_detail['news_id']];
                     $exclude = (isset($reg_id)) ? array_merge($curr_news_id, $reg_id) : $curr_news_id;
                     $tanggal = $news_detail['news_date_publish'];
                     $otherTag = $tags;
                     unset($otherTag[0]);
                     if (count($otherTag) > 0)
                     {
                          foreach ($otherTag as $v)
                          {
                                $includeTags[] = $v['tag_news_tag_id'];
                          }
                          $_otherV = News::Join('tag_news','tag_news.tag_news_news_id','=','news.news_id')
                                ->join('tags','tags.id','=','tag_news.tag_news_tag_id')
                                ->whereIn('tag_news.tag_news_tag_id',$includeTags)
                                ->where('news_domain_id', $this->config['domain_id'])
                                ->where('news_level','1')
                                ->whereNotIn('news.news_id',$exclude)
                                ->where('news.news_date_publish','<',$news_detail['news_date_publish'])
                                ->groupBy('news.news_id')
                                ->orderBy('news_date_publish','DESC')
                                ->take($total_related - (count($_v)))->get()->toArray();
                          $rel = array_merge($_v,$_otherV);
                     }

                     if (count($rel) < $total_related)
                     {
                          $exclude = (count($rel) ? [] : $exclude);

                          foreach ($rel as $vv)
                          {
                                $exclude[] = $vv['news_id'];
                          }

                          $_otherCategory = News::join('tag_news', 'tag_news.tag_news_news_id', '=', 'news.news_id')
                                            ->join('tags','tags.id','=','tag_news.tag_news_tag_id')
                                            ->where('news_level', '1')
                                            ->whereNotIn('news_id', $exclude)
                                            ->where('news_domain_id', $this->config['domain_id'])
                                            ->whereNews_category('["'. $news_detail['news_id_category'] .'"]')
                                            ->where('news_date_publish', '<', $news_detail['news_date_publish'])
                                            ->groupBy('news.news_id')
                                            ->orderBy('news_date_publish','DESC')
                                            ->take($total_related - (count($rel)))->get()->toArray();
                          $rel = array_merge($rel, $_otherCategory);
                     }
                }

                return $rel ;
          },  $interval );

          $no = 0 ;
          $relstory = $this->generate_news_url($relstory);
          foreach ($relstory as $v)
          {
//                $this->exclude_id[] = $v['news_id'];
                $related_news_list[$no]['news_title'] = $v['news_title'];
                $related_news_list[$no]['news_url_amp_full'] = $v['news_url_with_base_amp'];
                $no++;
          }

          $interval   = WebCache::App()->get_config('cachetime_default');
          setCache($cacheKey, serialize($related_news_list) , $interval);

          return $related_news_list;

     }

}
?>
