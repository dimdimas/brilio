<?php

class DetailController extends CController {
    
    private $_exclude = [];
    private $_id_cat;
    
    function __construct() 
    {
        parent::__construct();
        $this->model(array('news_model', 'tags_model', 'jsview_model', 'what_happen_model', 'tag_news_model', 'today_tags_model', 'news_paging_model', 'news_related_model', 'photonews_detail_model', 'video_model'));
        $this->library(array('table', 'lib_date', 'widget'));
        $this->helper('mongodb');
    }

    function index($category = '', $url_news = '', $url_paging_news = '') 
    { 
        $url_filter = $_SERVER["REQUEST_URI"];
        $cacheKey   = 'read_content_news_mobile' . $url_filter;
        if ($ret = checkCache($cacheKey))
            return $ret;

        if (isset($this->_categories['url_to_id'][$category])) 
        {
            $this->_id_cat = $this->_categories['url_to_id'][$category];
        }
        else
            Output::App()->show_404();
        
        $hide_news_html = explode('.html', $url_news);
        $keyword_news   = $hide_news_html[0];

        $page_full = explode("index", $keyword_news);

        if (empty($page_full[1])) 
        {
            $type = '';
        } 
        else 
        {
            $type = $page_full[1];
        }

        $TE_1 = 'Menu';
        $TE_2 = 'Detail pages';
        $hide_news_html     = explode('.html', $url_news);
        $keyword_news       = $hide_news_html[0];
        $hide_paging_html   = explode('.html', $url_paging_news);
        $keyword_paging     = $hide_paging_html[0];

        $url_news_cek       = explode("-split", $url_news);

        if (empty($url_paging_news) || $url_paging_news == 'index') 
        {
            if (count($url_news_cek) > 1) 
            {
                $file       = $url_news_cek[0];
                $keyword    = $url_news_cek[0];
                $mongo_file = $file . '.html';
                // $mongo_file = $file;
            } 
            else 
            {
                $file       = $keyword_news;
                $keyword    = $keyword_news;
                $mongo_file = $file . '.html';
                // $mongo_file = $file;
            }
        } 
        else 
        {
            $keyword        = $keyword_news;
            $file           = $keyword_news . '/' . $url_paging_news;
            $mongo_file     = $keyword_news . '/' . $url_paging_news . '.html';
            // $mongo_file     = $keyword_news . '/' . $url_paging_news ;
        }

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
            if ($mongo_data['total_paging'] == 0) 
            {
                $old_mongo_data = 1 ; //if 
                $news_detail['has_paging'] = 0;
            }
            else
            {
                $old_mongo_data = 0 ;
                $news_detail['has_paging'] = 1;
            }
        } 
        else 
        {
            $news_detail = $this->news_model->get_read_news($keyword);
            // $news_detail = $this->news_model->get_all_news_detail($keyword);
            $news_detail['tag_news'] = $this->tag_news_model->get_tags_news($news_detail['news_id']);
        }

        if (empty($news_detail['news_category'])) 
        {
            $category_url = str_replace(' ', '-', strtolower($news_detail['news_rubrics_rubrics_common']));
            $news_detail['news_rubrics_rubrics_common'] = $news_detail['news_rubrics_rubrics_common'];
            $news_detail['category_url'] = $news_detail['news_rubrics_rubrics_common'];
        } 
        else 
        {
            $category_meta = $this->_category($news_detail['news_category']);
            $category_url = $category_meta['CATEGORY_URL'];
            $news_detail['news_rubrics_rubrics_common'] = strtolower($category_meta['CATEGORY_URL']);
            $news_detail['category_url'] = $category_meta['CATEGORY_URL'];
        }

        if (empty($news_detail['news_id'])) 
        {
            Output::App()->show_404();
        }
        
        
        $news_detail['news_date']    = $this->lib_date->mobile_waktu($news_detail['news_date_publish']);
        $news_detail['news_url_fix'] = $this->config['rel_url']. $category .'/'.  $news_detail['news_url'].'.html';
        
        $reporter_json        = $news_detail['news_reporter'];
        $reporter_json_decode = json_decode($reporter_json);
        $editor_json          = $news_detail['news_editor'];
        $editor_json_decode   = json_decode($editor_json);            
        $datetime             = explode(" ", $news_detail['news_entry']);
        $datetime_clear       = explode("-", $datetime[0]);
        $year                 = $datetime_clear[0];
        $month                = $datetime_clear[1];
        $date                 = $datetime_clear[2];

        $news_detail['img_cover_url'] = $this->config['klimg_url']."news/".$year."/".$month."/".$date."/".$news_detail['news_id']."/657x438-".$news_detail['news_image'];

        //FUNCTION EDITOR
        if (!empty($editor_json_decode[0]->user_fullname)) 
        {
            $news_detail['EDITOR'] = 'Editor : ' . $editor_json_decode[0]->user_fullname . '';
            $news_detail['inisial_editor'] = '(brl/' . $this->widget->inisial_editor($editor_json_decode[0]->id) . ')';
        } 
        else 
        {
            $news_detail['EDITOR'] = '';
            $news_detail['inisial_editor'] = '';
        }

        if (isset($mongo_data['tag_news']) || !empty($mongo_data['tag_news'])) 
        {
            $news_detail['tag_news'] = $mongo_data['tag_news'];
        } 
        else 
        {
            $news_detail['tag_news'] = $this->tag_news_model->get_tags_news($news_detail['news_id']);
        }

        $tag_id = '';
        $similar = '';
        $news_detail['list_tag'] = [];
        if ($news_detail['tag_news']) 
        {
            $no = 1;
            foreach ($news_detail['tag_news'] as $a => $b) 
            {
                $tmp['tag_news_id'] = $b['tag_news_news_id'];
                $tmp['tag_news_title'] = $b['tag_news_tags'];
                $huruf_awal_tags = substr(strtolower($b["tag_url"]), 0, 1);
                $tmp['tag_url'] = $this->config['rel_url'] . 'tag/' . $huruf_awal_tags . '/' . $b["tag_url"] . '/';
                
                if ($no == 1) 
                {
                    $koma = '';
                } 
                else 
                {
                    $koma = ',';
                }
                $tag_id .= "$koma" . $b['tag_news_tag_id'];
                $no++;
                if ($b['tag_news_tag_id'] == '8424') 
                {
                    $similar .= 'true';
                } 
                else 
                {
                    $similar .= '';
                }
                
                $news_detail['list_tag'][] = $tmp ;
            }
        } 
        else 
        {
            $news_detail['list_tag'] = '';
        }
       
       // function for brand image in reporter section
        if ($similar == 'true') 
        {
            $brand = array();
            $brand['BRAND_IMG'] = $this->config['assets_image_url'] . 'logo-intel-mobile.png';
            $brand['STYLE_DATE'] = 'style="margin-top: 28px;"';
            $news_detail['reporter'] = $this->view('mobile/box/_box_brand', $brand, true);
            $news_detail['style_date'] = 'style="margin-top: 28px;"';
        } 
        else 
        {
            if ($keyword_news == '10-tanda-ketika-bos-di-kantormu-menjengkelkan-bikin-kamu-malas-kerja-150831f' ||
                    $keyword_news == 'pojok-beteng-keraton-yogyakarta-cuma-tinggal-3-yang-1-lagi-kemana-ya-151015f' ||
                    $keyword_news == '20-tempat-mengagumkan-ini-cukup-jadi-alasanmu-pergi-berlibur-ke-tokyo-1510154' ||
                    $keyword_news == 'bukti-wisata-jogja-geser-bali-bandung-bahkan-destinasi-luar-negeri-151015o' ||
                    $keyword_news == '7-pesona-wisata-di-krabi-kamu-juga-bisa-temukan-karimunjawa-di-sana-151015w' ||
                    $keyword_news == '15-alasan-kenapa-orang-malaysia-sebut-langkawi-persis-dengan-bali-151015q' ||
                    $keyword_news == 'dengan-rp-5-juta-kamu-bisa-kunjungi-16-tujuan-wisata-di-thailand-ini-1510150') 
            {
                $brand = array();
                $brand['BRAND_IMG'] = $this->config['assets_image_url'] . 'ssbd_75.png';
                $brand['BRAND_STYLE'] = 'margin-top: 14px;';
                $news_detail['reporter'] = $this->view('mobile/box/_box_brand', $brand, true);
                $news_detail['style_date'] = 'style="margin-top: 28px;"';
            }
            elseif ($keyword_news == '25-alasan-mengapa-cewek-lebih-suka-cowok-bersih-tak-berjanggut-151218h' )
            {
                $brand = array();
                $brand['BRAND_IMG'] = $this->config['assets_image_url'] . 'logo-philips.png';
                $brand['BRAND_STYLE'] = 'margin-top: 14px;';
                $news_detail['reporter'] = $this->view('mobile/box/_box_brand', $brand, true);
                $news_detail['style_date'] = 'style="margin-top: 28px;"';
            }
            elseif ($keyword_news == 'yuk-intip-prediksi-peruntungan-kamu-di-tahun-monyet-api-160206v')
            {
                $brand = array();
                $brand['BRAND_IMG'] = $this->config['assets_image_url'] . 'logo-bearbrand-small.png';
                $brand['BRAND_STYLE'] = 'margin-top: -15px;';
                $news_detail['reporter'] = $this->view('mobile/box/_box_brand', $brand, true);
                $news_detail['style_date'] = 'style="margin-top: 28px;"';
            }
            else 
            {
                $news_detail['reporter'] = 'Reporter : ' . $reporter_json_decode[0]->name;
                $news_detail['style_date'] = '';
            }
        }
        //eof brand image reporter
     
        //FUNCITON PAGING
        if ($news_detail['has_paging'] == 1) 
        {
           //start paging 
            $category = strtolower($news_detail['news_rubrics_rubrics_common']);

            // $news_detail['news_paging_detail'] = $this->news_paging_model->get_news_paging_img($news_detail['news_id'], $keyword_paging);
            $news_detail['news_paging_order_cek_db'] = $this->news_paging_model->get_news_paging_cek_order($news_detail['news_id']);
            $news_detail['news_paging_detail'] = $this->news_paging_model->_get_news_paging_no($news_detail['news_id'], $news_detail['news_paging_order_cek_db'][0]['news_paging_order'] , $keyword_paging);
            $news_detail['total_paging'] = $this->news_paging_model->get_news_paging_total($news_detail['news_id']);
            
            if (!empty($news_detail['news_paging_order_cek_db'][0]) && isset($news_detail['news_paging_order_cek_db'][0])) 
            {
                $get_order = $news_detail['news_paging_order_cek_db'][0];
                $status_order = $news_detail['news_paging_order_cek_db'][0]['news_paging_order'];
            } 
            else 
            {
                $status_order = "";
            }

            //make paging nav under image cover
            if (!empty($news_detail['news_paging_detail']['news_paging_no'])) 
            {
                //status_order == 0  paging is "ASC"
                if ($status_order == 0) 
                {
                    $limit = 4;
                    $page = ceil($news_detail['total_paging'] / $limit);
                    $visit_page_no = $news_detail['news_paging_detail']['news_paging_no'];
                    $total = $news_detail['total_paging'];
                    $last = $news_detail['total_paging'] - $limit;
                    
                    if ($visit_page_no <= 3) 
                    {
                        // for firs 4 item from total page
                        $offset = 0;
                    }
                    elseif($visit_page_no >= ($last+ 2))
                    {
                        //for the last 4 item from total page
                        $offset = $total - $limit ;
                    }
                    else
                    {
                        $offset = $visit_page_no - 2 ;
                    }
                    $news_detail['news_paging_list_top'] = $this->news_paging_model->get_news_paging_no($news_detail['news_id'], $status_order, $offset, $limit);
                }//eof if status_oreder == 0
                else
                {
                    //condition equal status_order == 1 || paging is 'DESC'
                    $limit = 4;
                    $page = ceil($news_detail['total_paging'] / $limit);
                    $visit_page_no = $news_detail['news_paging_detail']['news_paging_no'];
                    $total = $news_detail['total_paging'];
                    $last = $news_detail['total_paging'] - $limit;
                    
                    if ($visit_page_no >= ($last+2) ) 
                    {
                        //for the first 4 item from total
                        $offset = 0;
                    }
                    elseif($visit_page_no < $limit)
                    {
                        //for last 4 item from total
                        $offset = $total - $limit ;
                    }
                    // elseif (($visit_page_no <= $last) && ($visit_page_no >= $limit)) {
                    //     echo "string2";
                    //     $offset = $total - ($visit_page_no + 1);
                    // }
                    else
                    {
                        $offset = $total - ($visit_page_no + 1);
                        // $offset = $visit_page_no - ($limit+2) ;
                    }
                    $news_detail['news_paging_list_top'] = $this->news_paging_model->get_news_paging_no($news_detail['news_id'], $status_order, $offset, $limit);
                
                }
            }//eof make paging nav under image cover

            if ($keyword_paging == '') 
            {
                //content for intro
                $news_detail['news_content'] = html_entity_decode($news_detail['news_content']);
                $news_detail['news_paging_img_copy'] = '';
            } 
            else 
            {
                //content for paging
                $news_detail['news_paging_title'] = $news_detail['news_paging_detail']['news_paging_no'] . ". " . $news_detail['news_paging_detail']['news_paging_title'];
                $news_detail['news_paging_img_copy'] = $news_detail['news_paging_detail']['news_paging_title'];
                $news_detail['news_content'] = html_entity_decode($news_detail['news_paging_detail']['news_paging_content']);
                $split_img_path = explode('/', $news_detail['news_paging_detail']['news_paging_path']);
                $filename = end($split_img_path);
                unset($split_img_path[count($split_img_path) - 1]);
                $img_paging_cover = $this->config['klimg_url'].implode('/', $split_img_path).'/657xauto-'.$filename;
                $news_detail['img_cover_url'] = $img_paging_cover;
            }

            $news_detail['paging_nav_list'] = [];

            foreach ($news_detail['news_paging_list_top'] as $key) 
            {
                $news_paging_url = $this->config['rel_url'] . $category . '/' . $news_detail['news_url'] . '/' . $key['news_paging_url']. '.html';
                //if ($keyword_paging != '') {    
                    if ($key['news_paging_no'] == $news_detail['news_paging_detail']['news_paging_no']) 
                    {
                        if ($keyword_paging == '')
                        {
                            $paging_selected = '';
                            $news_detail['paging_intro_active_state'] = 'active';
                        }
                        else 
                        {
                            $paging_selected = 'active';
                            $news_detail['paging_intro_active_state'] = ''; 
                        }
                    }
                    else 
                    {
                        $paging_selected = '';
                        // $news_detail['paging_intro_active_state'] = 'active'; 
                    }
                //}
                //
                    $tmp = '<li class="'.$paging_selected.'" ><a href="'.$news_paging_url.'" onclick = "ga(\'send\', \'event\', \'Detail pages - Paging number\', \'Click\', \''.$key["news_paging_no"].'\');">'.$key["news_paging_no"].' </a></li> ';
                    //$tmp ="<li class='".$paging_selected."'><a href='".$news_paging_url."' onclick='ga('send', 'event', 'Detail page - Paging number', 'Click', '".$key['news_paging_no']."')'>".$key['news_paging_no']."</a></li>" ;
                    $news_detail['paging_nav_list'][] = $tmp;
            }
            
            //set next news
            if (isset($news_detail['news_paging_order_cek_db'][0]) && !empty($news_detail['news_paging_order_cek_db'][0])) 
            {
                $news_detail['news_paging_order_cek'] = $news_detail['news_paging_order_cek_db'][0];
            } 
            else 
            {
                $news_detail['news_paging_order_cek'] = "";
            }

            $news_detail['next_news_paging'] = '';
            $news_detail['prev_news_paging'] = '';
            if (isset($news_detail['news_paging_detail']['news_paging_no']) || !empty($news_detail['news_paging_detail']['news_paging_no'])) 
            {

                $news_detail['next_news_paging'] = $this->news_paging_model->get_next_news_paging($news_detail['news_id'], $keyword_paging, $news_detail['news_paging_detail']['news_paging_no'], $news_detail['news_paging_order_cek']['news_paging_no'], $news_detail['news_paging_order_cek']['news_paging_order']);
                $get_last_paging = end($news_detail['news_paging_list_top']);
                
                if (empty($news_detail['next_news_paging']) || !isset($news_detail['next_news_paging'])) 
                {
                    $news_detail['next_news_paging']['news_paging_url'] = $news_detail['news_url'] ;
                    $news_detail['next_news_paging']['news_paging_title'] = 'INTRO' ;
                    $news_detail['next_news_paging']['news_paging_detail_bottom'] = $this->config['klimg_url']."news/".$year."/".$month."/".$date."/".$news_detail['news_id']."/150x75-".$news_detail['news_image'];
                    $news_detail['next_news_paging_fix_url'] = $this->config['rel_url'] . $category . '/' . $news_detail['news_url'] . '.html' ;

                }
                else
                {
                    $split_img_path = explode('/', $news_detail['next_news_paging']['news_paging_path']);
                    $filename = end($split_img_path);
                    unset($split_img_path[count($split_img_path) - 1]);
                    $img_paging_cover = $this->config['klimg_url'].implode('/', $split_img_path).'/657xauto-'.$filename;
                    $img_paging_bottom = $this->config['klimg_url'].implode('/', $split_img_path).'/150x75-'.$filename;
                    $news_detail['next_news_paging']['news_paging_detail_bottom'] = $img_paging_bottom;
                    $news_detail['next_news_paging_fix_url'] = $this->config['rel_url'] . $category . '/' . $news_detail['news_url'] . '/' . $news_detail['next_news_paging']['news_paging_url'] . '.html' ;
                }
                    $news_detail['prev_news_paging'] = $this->news_paging_model->get_prev_news_paging($news_detail['news_id'], $keyword_paging, $news_detail['news_paging_detail']['news_paging_no'], $news_detail['news_paging_order_cek']['news_paging_order']);
            }
            
            //set prev paging url
            if (empty($news_detail['prev_news_paging'])) 
            {
//                $news_detail['prev_news_paging_fix_url'] = $this->config['rel_url'] . $category . '/' . $news_detail['news_url'] . '.html' ;
                $news_detail['prev_news_paging_fix_url'] = '#' ;

            } 
            else 
            {
                $news_detail['prev_news_paging_fix_url'] = $this->config['rel_url'] . $category . '/' . $news_detail['news_url'] . '/' . $news_detail['prev_news_paging']['news_paging_url'] . '.html' ;
            }
            //eof set prev paging url
        }
        //eof paging

        //function split content
        // CHECK FOR SPLIT CONTENT
        $news_split = explode('<!-- splitter content -->',html_entity_decode($news_detail['news_content']));
        if(count($news_split) > 1)
        {
            // $url_split = explode("-splitnews-", htmlspecialchars_decode($url_news));
            $url_split = preg_split('/\-splitnews\-|\.html+/', htmlspecialchars_decode($url_news));
            
            if (empty($url_split[1]) || !isset($url_split[1])) 
            {
                $page = '';
            } 
            else 
            {
                $page = $url_split[1];
            }
            if ($page == 0) 
            {
                $offset = 1;
            } 
            else 
            {
                $offset = $page;
            }
            
            //KETERANGAN FUNCTION SPLITTER ('DATA_CONTENT' - 'JML_DATA' - 'HALAMAN' - (DATA, OFFSET, LIMIT))
            $page_info = $this->_splitter($news_split, $offset, 1);

            
            //for first page split
            if ($page_info['HALAMAN'] == 1) 
            {
                // $news_detail['news_content'] = $news_split[$page_info['HALAMAN'] - 1];
                $news_detail['news_content'] = html_entity_decode($page_info['DATA_CONTENT'][0]);
                $selanjutnya = $page_info['HALAMAN'] + 1;
                $data_split['SPLIT_URL_PREV'] = null;
                $data_split['SPLIT_URL_NEXT'] = $this->config['base_url'] . str_replace('!', '', $category) . '/' . $url_split[0] . '-splitnews-' . $selanjutnya . '.html';
            }
            // for last page split
            if ($page_info['HALAMAN'] == $page_info['JML_DATA']) 
            {
                // $news_detail['news_content'] = $news_split[$page_info['HALAMAN'] - 1];
                $news_detail['news_content'] = html_entity_decode($page_info['DATA_CONTENT'][0]);
                $sebelum = $page_info['HALAMAN'] - 1;
                $data_split['SPLIT_URL_PREV'] = $this->config['base_url'] . str_replace('!', '', $category) . '/' . $url_split[0] . '-splitnews-' . $sebelum . '.html';
                $data_split['SPLIT_URL_NEXT'] = null;
            }
            //for page not in last or in first split
            if(($page_info['HALAMAN'] > 1) && ($page_info['HALAMAN'] < $page_info['JML_DATA']) )
            {
                // $news_detail['news_content'] = $news_split[$page_info['HALAMAN'] - 1];
                $news_detail['news_content'] = html_entity_decode($page_info['DATA_CONTENT'][0]);
                $selanjutnya = $page_info['HALAMAN'] + 1;
                $sebelum = $page_info['HALAMAN'] - 1;
                $data_split['SPLIT_URL_PREV'] = $this->config['base_url'] . str_replace('!', '', $category) . '/' . $url_split[0] . '-splitnews-' . $sebelum . '.html';
                $data_split['SPLIT_URL_NEXT'] = $this->config['base_url'] . str_replace('!', '', $category) . '/' . $url_split[0]  . '-splitnews-' . $selanjutnya . '.html';
            }
        }
        else
        {
            $data_split = '';
        }
        // eof SPLIT CONTENT

        $category_detail    = $category_url;
        $data_breadcumb['CATEGORY_TITLE']   = $category;
        $data_breadcumb['CATEGORY_URL']     = strtolower($category);

        //for social 
        $url_share          = $this->config['www_url'] . $category_detail . '/' . $news_detail['news_url'] . '.html';
        $news_detail['url_share'] = $url_share;        
        //eof social

        // function crosslink
        $cross_view = '';
        $cross = $this->news_related_model->_get_recomended_news($news_detail['news_id']);

        if ($cross)
        {
            foreach ($cross as $key=>$value)
            {
                if (strpos(html_entity_decode($news_detail['news_content']),'['.$value['news_related_code'].']') !== false) 
                {
                    $cross_view = $this->view('mobile/detail/_crosslink',['cross' => $value],TRUE);
                    $news_detail['news_content'] = str_replace('['.$value['news_related_code'].']',$cross_view, $news_detail['news_content']);
                }
            }
        }
        else
        {
            $news_detail['news_content'] = html_entity_decode($news_detail['news_content']);
        } 

        // for optimizaiton embeded code
        $news_detail['news_content'] = html_entity_decode($news_detail['news_content']);
        $news_detail['news_content'] = preg_replace('/http:\/\/d25no82dcg1wif.cloudfront.net\/+/', $this->config['klimg_url'].'/', $news_detail['news_content']);
        //eof optimzation
        
        $meta = array(
                'meta_title'         => $news_detail['news_title']. ' - Brilio.net',
                'meta_description'   => html_entity_decode($news_detail['news_synopsis']),
                'meta_keywords'      => html_entity_decode(str_replace(' ', ', ', $news_detail['news_synopsis'])),
                'og_url'             => $this->config['www_url'] . strtolower($news_detail['news_rubrics_rubrics_common']) . '/' . $news_detail["news_url"] . '.html',
                'og_image'           => 'http://cdn.klimg.com/newshub.id/news/' . $year . '/' . $month . '/' . $date . '/' . $news_detail['news_id'] . '/657xauto-' . $news_detail['news_image'],
                'og_image_secure'    => $this->config['klimg_url'] . 'news/' . $year . '/' . $month . '/' . $date . '/' . $news_detail['news_id'] . '/657xauto-' . $news_detail['news_image'],
                'expires'       => date("D,j M Y G:i:s T", strtotime($news_detail["news_date_publish"])),
                'last_modifed'  => date("D,j M Y G:i:s", strtotime($news_detail["news_date_publish"])),
                'chartbeat_sections' => ucfirst(strtolower($category_url)),
                'chartbeat_authors' => $editor_json_decode[0]->user_fullname,
                'meta_alternate'    => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
                'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );
        
        
        $data = array(
            'full_url'      => $news_detail['url_share'],
            'meta'          => $meta,
            'TE_2'          => $TE_2,
            'box_announcer' => $this->view('mobile/box/_announcer_banner', [], TRUE),
            'breadcrumb'    => $data_breadcumb,
            // 'collect_email' => $this->view('mobile/box/_view_collect_email',[], TRUE),
            'news_data'     => $news_detail,
            'split_data'    => $data_split,
            'related_news'  => $this->get_related($mongo_data, $news_detail, $tag_id),
            'popular'       => $this->_popular(6, $TE_2),
            'more_news'     => '',//$this->more_news($news_detail, 10),
            'tag_bottom'    => $this->_tags_bottom(0, 10, 0, $TE_2),
            'collect_email' => $this->_collect_email()
        );
        
        // FUNCTION WRITE MONGO
        if (empty($mongo_data) && ($this->config['json_news_detail'] === TRUE))
        {
            $news_detail['news_id'] = (int)$news_detail['news_id'];
            $file = str_replace('.html', '', $file);
            $news_detail['news_date_publish'] = strtotime($news_detail['news_date_publish']);
            writeDataMongo($file, $news_detail, $this->config['mongo_prefix'] . "news");
        }
        $ret        = $this->_render('mobile/detail/detail', $data);
        $interval   = WebCache::App()->get_config('cachetime_default');

        setCache($cacheKey, $ret, $interval);

        return $ret;
        // $this->_render('mobile/news/detail', $data);
    }

    function get_related($mongo_data, $news_detail, $tag_id){
        //FUNCTION RELATED NEWS
        if (isset($mongo_data['related_news']) || !empty($mongo_data['related_news'])) 
        {
            $news_detail['related_news'] = $mongo_data['related_news'];
        } 
        else 
        {
            $limit_related = 3;
            $query_related_per_tag = $this->tag_news_model->get_related_news($news_detail['news_id'], $tag_id, 0);

            $total_related_per_tag = $limit_related - count($query_related_per_tag);

            $total_related_tag = count($query_related_per_tag);

            if ($total_related_tag < 3) 
            {
                $query_related_per_category = $this->tag_news_model->get_related_news_category($news_detail['news_rubrics_rubrics_common'], $news_detail['news_date_publish'], $news_detail['news_id'], 0, $total_related_per_tag);
                $news_detail['related_news'] = array_merge($query_related_per_tag, $query_related_per_category);
            } 
            else 
            {
                $news_detail['related_news'] = array_merge($query_related_per_tag);
            }
        }

        if ($news_detail['related_news']) 
        {
            $news_detail['TITLE_RELATED_NEWS'] = '
                <div class="bottom-tags-title">
                    <div class="bottom-tags-title-line"></div>
                    <div class="bottom-tags-title-name">RELATED</div>
                </div>';

            $news_detail['list_related_news'] = [];
            foreach ($news_detail['related_news'] as $e) 
            {
                $category = str_replace(' ', '-', strtolower($e['news_rubrics_rubrics_common']));
                $datetime = explode(" ", $e['news_entry']);
                $datetime_clear = explode("-", $datetime[0]);
                $year = $datetime_clear[0];
                $month = $datetime_clear[1];
                $date = $datetime_clear[2];

                if ($e['news_type'] == '1') 
                {
                    $news_related_url = $this->config['rel_url'] . 'photo/' . str_replace('!', '', $category) . '/' . $e['news_url'] . '.html';
                    $url_img_news = 'photonews';
                } 
                elseif ($e['news_type'] == '2') 
                {
                    $news_related_url = $this->config['rel_url'] . 'video/' . str_replace('!', '', $category) . '/' . $e['news_url'] . '.html';
                    $url_img_news = 'video';
                } 
                else 
                {
                    $news_related_url = $this->config['rel_url'] . str_replace('!', '', $category). '/' . $e['news_url'] . '.html';
                    $url_img_news = 'news';
                }

                //TAGS LIST
                $tags_list = $this->tag_news_model->tags_news_list($e['tag_news_tags']);
                $huruf_awal_tags = substr(strtolower($e["tag_news_tags"]), 0, 1);
                $d['TAGS_LINK'] = $this->config['rel_url'] . 'tag/' . $huruf_awal_tags . '/' . str_replace(' ', '-', strtolower($e['tag_news_tags'])) . '/';
                $d['TAGS_NEWS_TAGS'] = $e['tag_news_tags'];
                $d['RELATED_NEWS_TITLE'] = $e['news_title'];
                $d['RELATED_NEWS_URL'] = $news_related_url;
                $d['RELATED_NEWS_DATE'] = $this->lib_date->mobile_waktu($e['news_entry']);
                $d['RELATED_NEWS_IMG'] = $this->config['klimg_url'] . $url_img_news . '/' . $year . '/' . $month . '/' . $date . '/' . $e['news_id'] . '/200x100-' . $e['news_image_potrait'];
                $news_detail['list_related_news'][]= $d;
            }
        } 
        else 
        {
            $news_detail['LIST_RELATED_NEWS'] = '';
            $news_detail['TITLE_RELATED_NEWS'] = '';
        }

        return $news_detail ;
    }

    //FUNCTION BERITA LAINNYA
    function more_news($news_detail, $limit) {

        $cacheKey = 'more_news_' . MD5($news_detail['url_share']);

        if ($ret = checkCache($cacheKey))
            return $ret;

        $dont_miss_it_get = $this->dont_miss_it($news_detail['url_share'], $news_detail['news_date_publish'], $news_detail['news_id'], $news_detail['news_rubrics_rubrics_common']);
        $dont_miss_it_id = $dont_miss_it_get['DONT_MISS_IT_ID'];

        $more_news = $this->news_model->get_more_news($dont_miss_it_id, $news_detail['news_id'], $news_detail['news_date_publish'], $limit);
        $view = [];
        $no_more = 1;
        foreach ($more_news as $e) 
        {
            $datetime = explode(" ", $e['news_entry']);
            $datetime_clear = explode("-", $datetime[0]);
            $year = $datetime_clear[0];
            $month = $datetime_clear[1];
            $date = $datetime_clear[2];

            $category_meta = $this->_category($e['news_category']);
            $tag_meta = $this->_tag($e['tag_news_tag_id']);

            $category = $category_meta['CATEGORY_URL'];

            if ($e['news_type'] == '1') 
            {
                $news_link = $this->config['rel_url'] . 'photo/' . $category . '/' . $e['news_url'] . '.html';
                $url_img_news = 'photonews';
            } 
            elseif ($e['news_type'] == '2') 
            {
                $news_link = $this->config['rel_url'] . 'video/' . $category . '/' . $e['news_url'] . '.html';
                $url_img_news = 'video';
            } 
            else 
            {
                $news_link = $this->config['rel_url'] . $category . '/' . $e['news_url'] . '.html';
                $url_img_news = 'news';
            }

            $data_more_news['no_more'] = $no_more;
            $data_more_news['NEWS_TITLE'] = $e['news_title'];
            $data_more_news['NEWS_ID'] = $e['news_id'];
            $data_more_news['NEWS_IMAGES'] = $this->config['klimg_url'] . $url_img_news . '/' . $year . '/' . $month . '/' . $date . '/' . $e['news_id'] . '/106x106-' . $e['news_image_thumbnail'];
            $data_more_news['NEWS_ENTRY'] = $this->lib_date->mobile_waktu($e['news_date_publish']); //format 22 April 2015 13.30
            $data_more_news['NEWS_URL'] = $news_link;
            $data_more_news['TAGS_TITLE'] = $tag_meta['TAG_TITLE'];
            $data_more_news['TAGS_LINK'] = $tag_meta['TAG_URL'];
            $no_more++;
            $view[]= $data_more_news;
        }

        $ret = $view;
        $interval = WebCache::App()->get_config('cachetime_default');
        setCache($cacheKey, $ret, $interval);

        return $ret;
    }


    function dont_miss_it($url, $news_date_publish, $news_id, $news_rubrics_rubrics_common) {

        $cacheKey = 'dont_miss_it_' . MD5($url);

        if ($ret = checkCache($cacheKey))
            return $ret;

        $dont_miss_it = $this->news_model->get_dont_miss_it($news_date_publish, $type = '0');

        if (!empty($dont_miss_it)) 
        {
            $category = str_replace(' ', '-', strtolower($dont_miss_it['news_rubrics_rubrics_common']));

            if ($dont_miss_it['news_type'] == '1') 
            {
                $news_url = $this->config['rel_url'] . 'photo/' . $category . '/' . $dont_miss_it['news_url'] . '.html';
            } 
            elseif ($dont_miss_it['news_type'] == '2') 
            {
                $news_url = $this->config['rel_url'] . 'video/' . $category . '/' . $dont_miss_it['news_url'] . '.html';
            } 
            else 
            {
                $news_url = $this->config['rel_url'] . $category . '/' . $dont_miss_it['news_url'] . '.html';
            }

            $f['DONT_MISS_IT_TOP'] = 'Previous Article';
            $f['DONT_MISS_IT_TITLE'] = $dont_miss_it['news_title'];
            $f['DONT_MISS_IT_URL'] = $news_url;
            $f['DONT_MISS_IT_ID'] = $dont_miss_it['news_id'];
        } 
        else 
        {
            $dont_miss_it_new = $this->news_model->get_dont_miss_it_new($news_rubrics_rubrics_common, $type = '0');

            $category = str_replace(' ', '-', strtolower($dont_miss_it_new['news_rubrics_rubrics_common']));

            if ($dont_miss_it_new['news_type'] == '1') 
            {
                $news_url = $this->config['rel_url'] . 'photo/' . $category . '/' . $dont_miss_it_new['news_url'] . '.html';
            } 
            elseif ($dont_miss_it_new['news_type'] == '2') 
            {
                $news_url = $this->config['rel_url'] . 'video/' . $category . '/' . $dont_miss_it_new['news_url'] . '.html';
            } 
            else 
            {
                $news_url = $this->config['rel_url'] . $category . '/' . $dont_miss_it_new['news_url'] . '.html';
            }

            $f['DONT_MISS_IT_TOP'] = 'Previous Article';
            $f['DONT_MISS_IT_TITLE'] = $dont_miss_it_new['news_title'];
            $f['DONT_MISS_IT_URL'] = $news_url;
            $f['DONT_MISS_IT_ID'] = $dont_miss_it_new['news_id'];
        }

        $interval = WebCache::App()->get_config('cachetime_default');

        setCache($cacheKey, $ret, $interval);

        return $f;
    }

    // //FUNCTION BERITA SELANJUTNYA
    // function what_next($url, $news_date_publish, $news_id, $dont_miss_it_id) {

    //     $cacheKey = 'what_next_' . MD5($url);

    //     if ($ret = checkCache($cacheKey))
    //         return $ret;

    //     $what_next = $this->news_model->get_what_next($news_date_publish, $type = '0');


    //     if (!empty($what_next)) {
    //         $category = str_replace(' ', '-', strtolower($what_next['news_rubrics_rubrics_common']));
    //         if ($what_next['news_type'] == '1') {
    //             $news_url = $this->config['rel_url'] . 'photo/' . $category . '/' . $what_next['news_url'] . '.html';
    //         } elseif ($what_next['news_type'] == '2') {
    //             $news_url = $this->config['rel_url'] . 'video/' . $category . '/' . $what_next['news_url'] . '.html';
    //         } else {
    //             $news_url = $this->config['rel_url'] . $category . '/' . $what_next['news_url'] . '.html';
    //         }

    //         $g['WHATS_NEXT_TITLE_TOP'] = 'Next Article';
    //         $g['WHATS_NEXT_TITLE'] = $what_next['news_title'];
    //         $g['WHATS_NEXT_URL'] = $news_url;

    //         $ret = $this->view('mobile/read/news/next_article/_view', $g, true);
    //     } else {
    //         $datetime_sekarang = date('Y-m-d H:i:s');
    //         $date = date_create($datetime_sekarang);
    //         date_add($date, date_interval_create_from_date_string('-1 days'));
    //         $tanggal_sebelumnya = date_format($date, 'Y-m-d');
    //         $what_next_new = $this->jsview_model->what_next($tanggal_sebelumnya, $news_id, $dont_miss_it_id);
    //         $category = str_replace(' ', '-', strtolower($what_next_new['news_rubrics_rubrics_common']));
    //         if ($what_next_new['news_type'] == '1') {
    //             $news_url = $this->config['rel_url'] . 'photo/' . $category . '/' . $what_next_new['news_url'] . '.html';
    //         } elseif ($what_next_new['news_type'] == '2') {
    //             $news_url = $this->config['rel_url'] . 'video/' . $category . '/' . $what_next_new['news_url'] . '.html';
    //         } else {
    //             $news_url = $this->config['rel_url'] . $category . '/' . $what_next_new['news_url'] . '.html';
    //         }

    //         $g['WHATS_NEXT_TITLE_TOP'] = 'Next Article';
    //         $g['WHATS_NEXT_TITLE'] = $what_next_new['news_title'];
    //         $g['WHATS_NEXT_URL'] = $news_url;

    //         $ret = $this->view('mobile/read/news/next_article/_view', $g, true);
    //     }

    //     $interval = WebCache::App()->get_config('cachetime_default');

    //     setCache($cacheKey, $ret, $interval);

    //     return $ret;
    // }

}
