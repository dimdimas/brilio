<?php

class NewsDetailController extends CController {

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

        if (isset($this->_categories['url_to_id'][$category]))
        {
            $this->_id_cat = $this->_categories['url_to_id'][$category];
        }
        else
        {
            Output::App()->show_404();
        }

        if (empty($url_paging_news) || $url_paging_news == 'index')
        {
            $cacheKey = 'mobile_read_news_content_' . $slug.'_'.$key_preview;
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
            $cacheKey = 'mobile_read_news_content_' . $slug. '-' .$url_paging_news.'_'.$key_preview;
            $keyword    = $keyword_news; //news keyword
            $file       = $keyword_news . '/' . $url_paging_news;
            $mongo_file = $keyword_news . '/' . $url_paging_news ;
        }

        if ($ret = checkCache($cacheKey))
            return $ret;

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

          	$news_detail = $mongo_data;

            //cek for preview page
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
                $_news[0] = News::previewpublished()
                          ->where('news_url', $keyword)
                          ->with('sponsor_tag')
                          ->first();

                if ($_news[0])
                    $_news[0] = $_news[0]->toArray();
                else
                    Output::App()->show_404();

                //Generate News URL
                $_news = $this->generate_news_url($_news)[0];
                $news_detail = $_news;

                //cek for preview page
                if($news_detail['news_date_publish'] >= date('Y-m-d H:i:s') && $preview_flag == FALSE)
                    Output::App()->show_404();

                $tags = cache('query_news_tags_'.$news_detail['news_id'], function () use($news_detail) {
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
           $cross = cache('query_news_crosslink_'.$news_detail['news_id'], function () use($news_detail) {
              $cross_ = news_related::where('news_related_news_id',$news_detail['news_id'])->get();
              if ($cross_) {
                 return $cross_->toArray();
              } else {
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
          // end of cross link

          //START FUNCTION PAGING
          //cek if the news has paging

          if ($news_detail['has_paging'] == 1)
          {
            $TE = 'Detail paging';
            $TE_2 = 'Detail paging';
            $paging_ = cache('query_berita_paging_'. $news_detail['news_id'] , function() use($news_detail) {
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
            $is_paging = cache('query_cek_content_paging_'.$keyword_paging. '_' . $news_detail['news_id'], function() use($keyword_paging,$news_detail)
            {
              return news_paging::where('news_paging_status','1')
                         ->where('news_paging_url',$keyword_paging)
                         ->where('news_paging_news_id',$news_detail['news_id'])->first();
            },  $interval );

            if (!$is_paging) abort(404);
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

               $paging_list = cache('query_nav_top_paging_'.$keyword_paging. '_' .$news_detail['news_id'], function() use($keyword_paging, $news_detail, $limit, $offset)
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

               $paging_list = cache('query_nav_top_paging_'.$keyword_paging. '_' .$news_detail['news_id'], function() use($keyword_paging, $news_detail, $limit, $offset)
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
               $news_paging_url = $this->config['rel_url'] . $category . '/' . $news_detail['news_url'] . '/' . $val['news_paging_url']. '.html';
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
               $tmp = '<li class="'.$paging_selected.'" ><a href="'.$news_paging_url.'" onclick = "ga(\'send\', \'event\', \'Detail pages - Paging number\', \'Click\', \''.$val["news_paging_no"].'\');">'.$val["news_paging_no"].' </a></li> ';
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
              $temp['news_paging_url'] = $news_detail['news_url_without_html'] . $_paging_list['news_paging_url']. '.html';
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
              $temp['news_paging_url'] = $news_detail['news_url_without_html'] . $_paging_list[$current_key+1]['news_paging_url']. '.html';
              $temp['news_paging_title'] = ucwords($_paging_list[$current_key+1]['news_paging_title']);

              $split_img_path= explode('/', $_paging_list[$current_key+1]['news_paging_path']);
              $filename= end($split_img_path);
              unset($split_img_path[count($split_img_path) - 1]);
              $temp['news_paging_image'] = $this->config['klimg_url'] . ($_paging_list[$current_key+1]['news_paging_media'] == 0? implode('/', $split_img_path).'/150x75-'.$filename : '');
              $news_detail['news_paging_next'] = $temp;
          }
          else
          {
              $temp['news_paging_url'] = $news_detail['news_url_full'] ;
              $temp['news_paging_title'] = 'INTRO';
              $temp['news_paging_image'] = $news_detail['news_image_intro_location_full'];

              $news_detail['news_paging_next'] = $temp;
          }

          if (isset($_paging_list[$current_key-1]) && !empty($_paging_list[$current_key-1]))
          {
               //set prev news paging
              $temp['news_paging_url'] = $news_detail['news_url_without_html'] . $_paging_list[$current_key-1]['news_paging_url']. '.html';
              $temp['news_paging_title'] = ucwords($_paging_list[$current_key-1]['news_paging_title']);

              $split_img_path= explode('/', $_paging_list[$current_key-1]['news_paging_path']);
              $filename= end($split_img_path);
              unset($split_img_path[count($split_img_path) - 1]);
              $temp['news_paging_image'] = $this->config['klimg_url'] . ($_paging_list[$current_key-1]['news_paging_media'] == 0? implode('/', $split_img_path).'/150x75-'.$filename : '');
              $news_detail['news_paging_prev'] = $temp;
          }
          else
          {
            $temp['news_paging_url'] = $news_detail['news_url_full'] ;
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
                $TE = 'Detail split';
                $TE_2 = 'Detail split';
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
                     // $news_detail['news_content'] = $news_split[$page_info['HALAMAN'] - 1];
                        $news_detail['news_content']         = html_entity_decode($news_detail['news_content'][0]);
                        $selanjutnya                         = $page + 1;
                        $_split['split_url_prev']            = null;
                        $_split['split_url_next']            = $this->config['rel_url'] . str_replace('!', '', $category) . '/' . $url_split[0] . '-splitnews-' . $selanjutnya . '.html';
                }
                elseif ($page == count($news_detail['news_content']) && $page != 1 ) {
                     # code...
                        $last_index                  = count($news_detail['news_content']) - 1;
                        $news_detail['news_content'] = html_entity_decode($news_detail['news_content'][$last_index]);
                        $sebelum                     = $page - 1;
                        $_split['split_url_prev']    = $this->config['rel_url'] . str_replace('!', '', $category) . '/' . $url_split[0] . '-splitnews-' . $sebelum . '.html';
                        $_split['split_url_next']    = null;
                }
                else
                {
                        $index                       = $page - 1  ;
                        $news_detail['news_content'] = html_entity_decode($news_detail['news_content'][$index]);
                        $selanjutnya                 = $page + 1;
                        $sebelum                     = $page - 1;
                        $_split['split_url_prev']    = $this->config['rel_url'] . str_replace('!', '', $category) . '/' . $url_split[0] . '-splitnews-' . $sebelum . '.html';
                        $_split['split_url_next']    = $this->config['rel_url'] . str_replace('!', '', $category) . '/' . $url_split[0]  . '-splitnews-' . $selanjutnya . '.html';
                }

                $news_detail['news_split_data'] = $_split;
          }
          else
          {
                $news_detail['news_content'] = $news_detail['news_content']['0'];
                $news_detail['news_split_data'] = '';
          }
          // eof SPLIT CONTENT
          //
          //what do you think FB
          if ($news_detail['news_sensitive'] == 1)
          {
                $news_detail['url_share'] = '' ;
          }
          else
          {
                $news_detail['url_share'] =  $this->config['base_url'] . $category . '/' . $news_detail['news_url'] . '.html';
          }
          //eof what do you think

          //news_related
          $news_detail['related_news_list'] = $this->more_stories($news_detail, $TE_2);

          $news_detail['news_content'] = html_entity_decode($news_detail['news_content']);
          $news_detail['news_content'] = preg_replace('/http:\/\/d25no82dcg1wif.cloudfront.net\/+/', $this->config['klimg_url'].'/', $news_detail['news_content']);

        } // eof else when mongo data is empty

        $tmp_content = $news_detail['news_content'];

        //FUNCTION SPONSOR TAG
        if( !empty($news_detail['sponsor_tag']) )
        {
            $news_detail['reporter_status'] = 'brand';
            $news_detail['reporter']['name']   = $this->view('mobile/box/_box_brand', [ 'brand' => $news_detail['sponsor_tag'], 'TE' => $TE_2], true);
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
                $news_detail['reporter']['name']    = $this->view('mobile/box/_box_brand', ['brand' => $brand, 'TE' => $TE_2 ], true);
                $news_detail['reporter_style_date'] = 'style="margin-top: 28px;"';
                $news_detail['reporter_status']     = 'brand';
                $init_brand = TRUE;
            }
        }

        //PROMOTE VIDEO
    		// if ( !empty($news_detail['news_sponsorship']) || $init_brand == TRUE )
    		// {
        //     if (in_array($news_detail['news_sponsorship'], $this->config['video_sponsor']['allowed_sponsorship_tag'])) {
        //         $promote_video = $this->config['video_sponsor'];
        //     }
        //     else{
        //       $promote_video = '';
        //     }
    		// }
        // else
    		// {
    		// 		$promote_video = $this->config['video_sponsor'];
    		// }
        //video sponsor exception


        //eof brand image reporter

        //FUNCTION ADDED KODE TRACK EVENT - LINK INSIDE CONTENT
        //$news_detail['news_content'] = $this->add_te_link_in_content($news_detail['news_content'], $TE);
        //end kode track event

        if (isset($news_detail['news_crosslink']) && !empty($news_detail['news_crosslink'])) {
          	$cross = $news_detail['news_crosslink'];
          	foreach ($cross as $key=>$value)
          	{
             	if (strpos(html_entity_decode($news_detail['news_content']),'['.$value['news_related_code'].']') !== false)
             	{
                	$cross = $this->view('mobile/detail/_crosslink',['cross' => $value],TRUE);
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
                'chartbeat_authors'  => (!empty(json_decode($news_detail['news_reporter'])[0]) ? json_decode($news_detail['news_reporter'])[0]->name : NULL),
                'meta_alternate'     => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
                'iframe_kl'          => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
          ];

          $data =
          [
                'full_url'      => '',
                'TE_2'          => $TE_2,
                'meta'          => $meta,
                'box_announcer' => $this->view('mobile/box/_announcer_banner', [], TRUE),
                'breadcrumb'    => $data_breadcumb,
                'news_data'     => $news_detail,
                'news_related'  => $this->view('mobile/detail/_news_related', ['TE_2' => $TE_2, 'related_news_list' => array_slice($news_detail['related_news_list'],0,3)], TRUE),
                'popular'       => $this->_trending(6, $TE_2),
                'split_data'    => $news_detail['news_split_data'],
                '_list_categories_bottom' => $this->view('mobile/box/_menu_bottom', ['_list' => $this->mobile_menu_bottom($TE_2) ], true),
                'collect_email' => $this->view('mobile/box/_collect_email', [], true),
                'video'         => $this->view('mobile/box/promote_video', ['promote_video' => $this->get_video_sponsor($news_detail['news_sponsorship'], $init_brand) ], TRUE),
          ];

          if (empty($mongo_data) && ($this->config['json_news_detail'] === TRUE))
          {
            $news_detail['news_content']      = $tmp_content;
            $news_detail['news_id']           = (int)$news_detail['news_id'];
            $news_detail['news_date_publish'] = strtotime($news_detail['news_date_publish']);
          	$news_detail['news_TE'] 		  = $TE_2;
            writeDataMongo($mongo_file, $news_detail, $this->config['mongo_prefix'] . "news");
          }

          $ret = $this->_mobile_render('mobile/detail/m_news_detail', $data);
          setCache($cacheKey, $ret, $interval);
          return $ret;
     }

    function more_stories($news_detail, $TE){
    	$interval   = WebCache::App()->get_config('cachetime_default');
      	$cacheKey = 'mobile_more_stories_' . MD5($news_detail['news_id']);

      	if ($ret = checkCache($cacheKey))
        	return unserialize($ret) ;

    	$total_related = 24;

		$_v = cache('query_more_stories_mobile_'.$news_detail['news_id'], function() use( $news_detail, $total_related) {
				return News::where('news_domain_id', $this->config['domain_id'])
						->where('news_level','1')
						->where('news.news_date_publish', '<' ,$news_detail['news_date_publish'])
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
      	$interval   = WebCache::App()->get_config('cachetime_default');
      	setCache($cacheKey, serialize($more_stories_list) , $interval);

      	return $more_stories_list;

     }

}
?>
