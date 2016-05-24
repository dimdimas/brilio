<?php

class CController extends Controller {

    var $inlineparam;
    protected $_categories = [];

    function __construct() {
        parent::__construct();
        $this->model(['Rubrics','News','Tag','Domain','TagToday', 'Jsview', 'WhatHappen', 'SponsorTag', 'InternalBanner'], null, true);
        list($this->_arr_categori, $this->_category) = $this->_get_categori_sm();
        $this->_categories = $this->_set_categories();
        $this->library(array('table', 'lib_date', 'widget'));
    }

    protected function _set_categories()
    {

        $ret = cache(BRILIO_CURRENT_VIEWPORT.'_list_categories', function(){
            $res = [];
            $rows = Rubrics::where('rubrics_domain_id', $this->config['domain_id'])
                    ->where('rubrics_invalid', '0')
                    ->orderBy('rubrics_parent', 'asc')
                    ->get()->toArray();
            foreach($rows as $k => $v){
                $res[$v['rubrics_id']] = $v;
                $res['url_to_id'][$v['rubrics_url']] = $v['rubrics_id'];
                $res['id_to_url'][$v['rubrics_id']] = $v['rubrics_url'];
                $res['url_to_name'][$v['rubrics_url']] = $v['rubrics_name'];
                $res['cat_list'][] = $v['rubrics_name'];
            }
            return $res;
        }, 3600 * 24);

        return $ret;
    }

    protected function _render($template, $data = array()) {

        $pop_under = $this->_pop_under();
        $layout_data = [
            'full_url'          => $data['full_url'],
            'announcement'      => $this->view($this->config['view_folder'].'/box/announcement', [], TRUE),
            'content'           => $this->view($template, $data, true),
            'meta'              => $data['meta'],
            'canonical'         => (isset($data['canonical']) ? $data['canonical'] : $data['meta']['og_url']),
            'amphtml'           => (isset($data['meta']['amphtml']) ? $data['meta']['amphtml'] : NULL),
            'bottom_menu'       => (isset($data['bottom_menu']) ? $data['bottom_menu'] : NULL),
            'pop_under'         => (isset($pop_under[0]['popup_url']) ? $pop_under[0]['popup_url'] : NULL),
        ];

        $ret = $this->view($this->config['view_folder'].'/layouts/layout', $layout_data, true);
        $ret = preg_replace('/onclick="[^"]+"/', '', $ret);
        echo html_entity_decode($ret);

    }

    protected function _mobile_render($template, $data = array()){
      if (!empty($data['box_announcer']))
      {
          $box_announcer =  $data['box_announcer'];
      }
      else
      {
          $box_announcer = '';
      }

      $layout_data = [
          'box_announcer' => $box_announcer,
          // 'tags_top'      => $this->_tags_top(0, 10, 0, $data['TE_2']),
          'tags_top'      => (isset($data['today_tags']) ? $data['today_tags'] : ''),
          'content'       => $this->view($template, $data, true),
          'meta'          => $data['meta'],
          'full_url'      => $data['full_url'],
          'canonical'     => (isset($data['canonical']) ? $data['canonical'] : $data['meta']['og_url']),

      ];

      //clearing track event
      $ret = $this->view($this->config['view_folder'].'/layouts/main', $layout_data, true);
      $ret = preg_replace('/onclick="[^"]+"/', '', $ret);
      echo html_entity_decode($ret);
    }

    function _get_tags_today_top($cacheKey, $TE) {
        if ($ret = checkCache($cacheKey))
            return $ret;

        $tag = cache($cacheKey, function()
        {
            return TagToday::join('tags', 'tags.id', '=' ,'today_tag.id_tag')
                        ->where('domain_id', '=' , $this->config['domain_id'])
                        ->where('status', '=' ,'1')
                        // ->with('get_tag')
                        ->orderBy('order', 'ASC')
                        ->take(5)
                        ->get()->toArray();
        }, 3600);

        foreach ($tag as $key => $v) {

            if($v['tag_url'] != "ramalanshio2016")
            {
                $first_char = substr(strtolower($v["tag_url"]), 0, 1);
                $tag[$key]['tag_url'] = $this->config['rel_url'] . 'tag/' . $first_char . '/' . str_replace(' ', '-', strtolower($v['tag_url']));
            }
            else
            {
                $tag[$key]['tag_url'] = "https://www.brilio.net/life/yuk-intip-prediksi-peruntungan-kamu-di-tahun-monyet-api-160206v.html";
            }

        }

        $list_tags['LIST_TAGS_TODAY'] = '';
        $list_tags['NAMA_HALAMAN'] = $TE;
        if ($tag) {
            $list_tags['LIST_TAGS_TODAY'] = $tag;
        } else {
            $list_tags['LIST_TAGS_TODAY'] = "";
        }


        $ret = $this->view($this->config['view_folder'].'box/_set_box_tags_today', $list_tags, true);
        $interval = WebCache::App()->get_config('cachetime_default');
        setCache($cacheKey, $ret, $interval);
        return $ret;
    }

    function _get_tags_today_bottom($cacheKey, $TE){

        if ($ret = checkCache($cacheKey))
            return unserialize($ret);

        $tag = cache($cacheKey, function()
        {
            return TagToday::join('tags', 'tags.id', '=' ,'today_tag.id_tag')
                    ->where('domain_id', '=' , $this->config['domain_id'])
                    ->where('status', '=' ,'1')
                    // ->with('get_tag')
                    ->orderBy('order', 'ASC')
                    ->skip(5)
                    ->take(5)
                    ->get()->toArray();
        }, 3600);

        foreach ($tag as $key => $v) {
            $first_char = substr(strtolower($v["tag_url"]), 0, 1);
            $tag[$key]['tag_url'] =$this->config['rel_url'] . 'tag/' . $first_char . '/' . str_replace(' ', '-', strtolower($v['tag_url']));
        }

        $list_tags['LIST_TAGS_TODAY'] = '';
        if ($tag) {
            $list_tags['LIST_TAGS_TODAY'] = $tag;
        } else {
            $list_tags['LIST_TAGS_TODAY'] = "";
        }

        $list_tags['TE'] = $TE;
        //$ret = $this->view('box/_set_box_tags_bottom', $list_tags, true);
        $ret =  $list_tags;

        $interval = WebCache::App()->get_config('cachetime_default');
        setCache($cacheKey, serialize($ret), $interval);

        return $ret;
    }

    function _popular_tags($cacheKey, $TE)
    {
        $interval = WebCache::App()->get_config('cachetime_default');

        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $tag = cache("query_".$cacheKey, function()
        {
            return TagToday::join('tags', 'tags.id', '=' ,'today_tag.id_tag')
                    ->where('domain_id', '=' , $this->config['domain_id'])
                    ->where('status', '=' ,'1')
                    ->orderBy('order', 'ASC')
                    ->take(6)
                    ->get()->toArray();
        }, $interval);

        foreach ($tag as $key => $v)
        {
            $first_char = substr(strtolower($v["tag_url"]), 0, 1);
            $tag_fix[$key]['TAG_TITLE'] = $v['tag_url'];
            $tag_fix[$key]['TAG_URL']   = $this->config['rel_url'] . 'tag/' . $first_char . '/' . str_replace(' ', '-', strtolower($v['tag_url']));
            $tag_fix[$key]['TE']        = $TE;
        }

        $list_tags['POPULAR_TAGS'] = '';
        if (!empty($tag))
        {
            $list_tags['POPULAR_TAGS'] = $tag_fix;
        }
        else
        {
            $list_tags['POPULAR_TAGS'] = "";
        }

        //$ret = $this->view(BRILIO_CURRENT_VIEWPORT.'/box/right_popular_tags', $list_tags, true);
        $ret = $list_tags;
        setCache($cacheKey, serialize($ret), $interval);
        return $ret;
    }

    function _most_popular_tag($cacheKey, $TE){

        if ($ret = checkCache($cacheKey))
            return $ret;

        $tag = cache($cacheKey, function()
        {
            return TagToday::join('tags', 'tags.id', '=' ,'today_tag.id_tag')
                    ->where('domain_id', '=' , $this->config['domain_id'])
                    ->where('status', '=' ,'1')
                    ->orderBy('order', 'ASC')
                    ->skip(5)
                    ->take(5)
                    ->get()->toArray();
        }, 3600);

        foreach ($tag as $key => $v)
        {
            $first_char = substr(strtolower($v["tag_url"]), 0, 1);
            $tag_fix[$key]['TAG_TITLE'] = $v['tag_url'];
            $tag_fix[$key]['TAG_URL']   = $this->config['rel_url'] . 'tag/' . $first_char . '/' . str_replace(' ', '-', strtolower($v['tag_url']));
            $tag_fix[$key]['TE']        = $TE;
        }
        // $query_today_bottom = $this->today_tags_model->get_tags_today($order, $limit);
        $list_tags['LIST_TAGS_TODAY'] = '';
        if ($tag) {
            $list_tags['LIST_TAGS_TODAY'] = $tag_fix;
        } else {
            $list_tags['LIST_TAGS_TODAY'] = "";
        }


        //$ret = $this->view('box/_popular_tags', $list_tags, true);
        $ret = $list_tags;

        $interval = WebCache::App()->get_config('cachetime_default');
        setCache($cacheKey, $ret, $interval);

        return $ret;
    }

    private function _get_categori_sm()
    {
        $row = cache('_get_categori_sm_'.BRILIO_CURRENT_VIEWPORT, function(){
         return Rubrics::where('rubrics_invalid', '0')
                    ->where('rubrics_domain_id', $this->config['domain_id'])
                    ->orderBy('rubrics_parent', 'asc')
                    ->get()
                    ->toArray();
        }, 3600 * 24);

            $data_row = $row;
            $dt       = array();

            foreach ($row as $rs)
            {
                $dt['id_to_name'][$rs['rubrics_id']]   = $rs['rubrics_name'];
                $dt['name_to_id'][$rs['rubrics_name']] = $rs['rubrics_id'];
                $dt['url_to_id'][$rs['rubrics_url']]   = $rs['rubrics_id'];
                $dt['id_to_url'][$rs['rubrics_id']]    = $rs['rubrics_url'];

                if ($rs['rubrics_parent'] == '0' || !$rs['rubrics_parent'])
                {
                    $dt2[$rs['rubrics_id']] = $rs;
                    $dt['grandparent_name_to_id'][$rs['rubrics_name']] = $rs['rubrics_id'];
                    $dt['grandparent_id_to_name'][$rs['rubrics_id']]   = $rs['rubrics_name'];
                    $dt['grandparent_url_to_id'][$rs['rubrics_url']]   = $rs['rubrics_id'];
                    $dt['grandparent_id_to_url'][$rs['rubrics_id']]    = $rs['rubrics_url'];
                }
                else
                {
                    if (isset($dt['id_to_name'][$rs['rubrics_parent']]))
                    {
                        $dt2[$rs['rubrics_parent']]['child'][$rs['rubrics_id']] = $rs;
                        $dt['child_id_to_parent'][$rs['rubrics_id']]            = $dt['id_to_name'][$rs['rubrics_parent']];
                        $dt['child_id_to_parenturl'][$rs['rubrics_id']]         = $dt['id_to_url'][$rs['rubrics_parent']];
                        $dt['child_name_to_parent'][$rs['rubrics_name']]        = $dt['id_to_name'][$rs['rubrics_parent']];
                        $dt['child_url_to_parent'][$rs['rubrics_url']]          = $dt['id_to_name'][$rs['rubrics_parent']];
                        $dt['child_url_to_parentid'][$rs['rubrics_url']]        = $rs['rubrics_parent'];
                    }
                }
            }
        return [$dt, $dt2];
    }

    function generate_news_url($row = array())
    {

        if (is_array($row) && count($row) > 0)
        {
            foreach ($row as $i => $v)
            {
                $id_category = json_decode($v['news_category'], true);
                $id_category = (is_array($id_category) && count($id_category) > 0) ? $id_category[0] : false;
                $url_cat     = (isset($this->_arr_categori['id_to_url'][$id_category]) ? $this->_arr_categori['id_to_url'][$id_category] : false);
                $name_cat       = (isset($this->_arr_categori['id_to_name'][$id_category]) ? $this->_arr_categori['id_to_name'][$id_category] : false);
                $parent_url_cat = (isset($this->_arr_categori['child_id_to_parenturl'][$id_category]) ? $this->_arr_categori['child_id_to_parenturl'][$id_category] : false);
                $parent_name_cat= (isset($this->_arr_categori['child_id_to_parent'][$id_category]) ? $this->_arr_categori['child_id_to_parent'][$id_category] : false);

                $url_all_cat = $url_cat;
                if ($parent_url_cat)
                    $url_all_cat = $parent_url_cat/*.'/'.$url_all_cat*/;

                $url_cat2 = $url_cat;
                if ($parent_url_cat)
                    $url_cat2 = $parent_url_cat.'/'.$url_cat2;

                $entry = explode(' ', $v['news_entry']);
                $entry = str_replace('-', '/', $entry[0]);
                $type  = array('news', 'photonews', 'video');

                $urlPhoto = '';
                if ($v['news_type'] == '1')
                {
                    $urlPhoto = "photo/";
                }
                else if ($v['news_type'] == '2')
                {
                    $urlPhoto = "video/";
                }

                $v['news_id_category']          = $id_category;
                $v['news_category_name']        = $name_cat;
                $v['news_category_url']         = $this->config['rel_url']. $url_cat .'/';
                $v['news_category_parent_name'] = $parent_name_cat;
                $v['news_category_parent_url']  = $this->config['rel_url'] . $parent_url_cat . '/';
                $v['news_url_full']             = $this->config['rel_url'] . $urlPhoto . $url_cat . '/' . $v['news_url'] . '.html';

                if (BRILIO_CURRENT_LANG == 'id') {
                  $v['news_url_with_base']        = $this->config['www_url']. $urlPhoto . $url_cat . '/' . $v['news_url'] . '.html';
                  $v['news_url_with_base_amp']    = $this->config['www_url']. 'amp/' . $urlPhoto . $url_cat . '/' . $v['news_url'] . '.html';
                }else{
                  $v['news_url_with_base']        = $this->config['www_url_en']. $urlPhoto . $url_cat . '/' . $v['news_url'] . '.html';
                  $v['news_url_with_base_amp']    = $this->config['www_url_en']. 'amp/' . $urlPhoto . $url_cat . '/' . $v['news_url'] . '.html';
                }
                $v['news_url_without_html']     = $this->config['rel_url'] . $urlPhoto . $url_cat . '/' . $v['news_url'] . '/';
                $v['news_url_raw']              = $this->config['rel_url'] . $urlPhoto . $url_cat . '/';
                $v['news_image_location']       = $this->config['klimg_url'].$type[$v['news_type']] . '/' . $entry .'/'. $v['news_id']. '/';

                if (BRILIO_CURRENT_LANG == 'id')
                {
                  # code...
                  if(is_numeric($v['news_date_publish']))
                  {
                      $v['news_date_publish_indo'] = $this->lib_date->indo(date("Y-m-d H:i:s", $v['news_date_publish']));
                  }
                  else
                  {
                      $v['news_date_publish_indo'] = $this->lib_date->indo($v['news_date_publish']);
                  }
                }
                else
                {
                  if(is_numeric($v['news_date_publish']))
                  {
                      $v['news_date_publish_indo'] = $this->lib_date->english(date("Y-m-d H:i:s", $v['news_date_publish']));
                  }
                  else
                  {
                      $v['news_date_publish_indo'] = $this->lib_date->english($v['news_date_publish']);
                  }
                }

                //cek apakah gambar terdapat pada halaman paging
                $get_temp = $v['news_image'];
                if (strpos($get_temp,'/paging/') !== FALSE)
                {
                    $split_img_path = explode('/', $v['news_image']);
                    $filename = end($split_img_path);
                    unset($split_img_path[count($split_img_path) - 1]);

                    $v['news_image_location_full'] = $this->config['klimg_url']. implode('/', $split_img_path).'/657xauto-'.$filename ;
                }
                else
                $v['news_image_location_full']      = $this->config['klimg_url'].$type[$v['news_type']] . '/' . $entry .'/'. $v['news_id']. '/750xauto-'. $v['news_image'];
                $v['news_image_location_raw']       = $type[$v['news_type']] . '/' . $entry .'/'. $v['news_id']. '/';
                $v['news_type_name']                = $type[$v['news_type']];

                // replace prefix brilio.net/en to brilio.net and delete non ascii character
                if(isset($v['news_content'])){
                    $v['news_content'] = str_replace('Brilio.net/en', 'Brilio.net', $v['news_content']);
                    $v['news_content'] =  preg_replace('/[^\x0A\x20-\x7E]/', '', $v['news_content']);
                }

                if(isset($v['news_title'])){
                    $v['news_title'] =  preg_replace('/[^\x0A\x20-\x7E]/', '', $v['news_title']);
                }

                if(isset($v['news_subtitle'])){
                    $v['news_subtitle'] =  preg_replace('/[^\x0A\x20-\x7E]/', '', $v['news_subtitle']);
                }

                if(isset($v['news_synopsis'])){
                    $v['news_synopsis'] =  preg_replace('/[^\x0A\x20-\x7E]/', '', $v['news_synopsis']);
                }
                //eof delete non ascii character

                //read editor
                if(isset($v['news_editor']))
                {
                    $author                       = json_decode($v['news_editor']);
                    $author                       = $author[0];
                    $v['author']['id']            = (isset($author->id) ? $author->id : '');
                    $v['author']['user_fullname'] = (isset($author->user_fullname) ? $author->user_fullname : '');

                    $v['author']['inisial_editor'] = '(brl/' . $this->widget->inisial_editor($v['author']['id']) . ')';
                }

                //read reporter
                if(isset($v['news_reporter']))
                {
                    $reporter               = json_decode($v['news_reporter']);
                    $reporter               = (count($reporter) ? $reporter[0] : []);
                    $v['reporter']['id']    = (isset($reporter->id) ? $reporter->id : '');
                    $v['reporter']['name']  = (isset($reporter->name) ? $reporter->name : '');
                }

                if (isset($v['tag_url']) && !empty($v['tag_url']))
                    $v['tag_url_full'] = $this->config['rel_url']. 'tag/'. $v['tag_url'][0] .'/'. $v['tag_url'] .'/';
                if (isset($v['tag'][0]['tag_url']))
                    $v['tag_url_full'] = $this->config['rel_url']. 'tag/'. $v['tag'][0]['tag_url'][0] .'/'. $v['tag'][0]['tag_url'] .'/';

                if (isset($v['news_tag']))
                {
                    foreach ($v['news_tag'] as $k=>$v1)
                    {
                        $v['news_tag'][$k]['tag_url_full'] = $this->config['rel_url']. 'tag/'. $v1['tag_url'][0] .'/'. $v1['tag_url'] .'/';
                    }
                }

                if (isset($v['news_to_tag_news']))
                {
                    foreach ($v['news_to_tag_news'] as $k=>$v1)
                    {
                        $huruf_awal = strtolower( $v1['tag_news_tags']{0} );
                        $tag_url    = strtolower( preg_replace('/[#! .]/', '-', $v1['tag_news_tags'] ) );
                        $v['news_to_tag_news'][$k]['tag_url_full'] = $this->config['rel_url']. 'tag/'. $huruf_awal .'/'. $tag_url .'/';
                    }
                }

                if (isset($v['news_tag_list']))
                {
                    foreach ($v['news_tag_list'] as $k=>$v1)
                    {
                        $tag_url    = strtolower( str_replace(' ', '-', $v1['tag_news_tags'] ) );
                        $tag_url    = strtolower( preg_replace('/[#!.]/', '', $tag_url ) );
                        $huruf_awal = strtolower( $tag_url{0} );
                        $v['news_tag_list'][$k]['tag_url_full'] = $this->config['rel_url']. 'tag/'. $huruf_awal .'/'. $tag_url .'/';
                    }
                }

                //function sponsor
                if ( !empty($v['sponsor_tag'][0]) )
                {
                    $val = $v['sponsor_tag'][0];
                    $split_time                   = explode(' ', $val['created_at']);
                    $entry_brand                  = str_replace('-', '/', $split_time[0]);
                    $firt_alpabhet                = substr(url_title($val['tag']), 0,1);
                    $sponsor_temp['id']           = $val['id'];
                    $sponsor_temp['brand_name']   = $val['brand_name'];
                    $sponsor_temp['brand_url']    = $val['brand_url'];
                    $sponsor_temp['tag_name']     = $val['tag'];
                    $sponsor_temp['tag_alphabet'] = $firt_alpabhet;
                    $sponsor_temp['tag_brand_url']      = $this->config['rel_url'].'brands/'.$firt_alpabhet.'/'.$val['url'];
                    $sponsor_temp['brand_image']        = $this->config['klimg_url']. 'tag-sponsorship/'. $entry_brand.'/'. $val['id'].'/'.$val['image'] ;
                    $v['sponsor_tag']   = $sponsor_temp;
                }
                //end sponsor tag

                $row[$i] = $v;
            }
        }
        
        return $row;
    }

    function add_te_link_in_content($content,$TE){
        $content = html_entity_decode($content);
        $get_content_link = preg_match('/<a href=".*?">/', $content, $matches);
        if (count($get_content_link) > 0) {

            preg_match_all('/<a href="([^>"]*)" target="[^"]*"/', $content, $url_link);

            foreach ($url_link[1] as $key) {
                preg_match('/(<a href="[^>"]*")/', $content, $old_link);
                $kode_onclick = "<a href='$key' onclick=\"ga('send', 'event', '$TE', 'Hyperlink content', '$key')\"";
                $content = str_replace($old_link, $kode_onclick , $content);
            }
        }

        return $content;
    }

    function gen_base_url()
    {
        $url = $this->config['base_url'];

        {
            $url   = str_replace(array("http://", 'https://'), '', $url);
            $tmp   = explode('.', $url);
            $first = current($tmp);
            switch (true)
            {
                case ($first == 'www' || $first == 'dev'):
                    $first = 'www';
                    break;
                case intval($first):
                    $first = $first;
                    break;
                case (strpos($first, 'dev') === false):
                    $first .= 'www';//'-dev';
                    break;
            }
            $tmp[0] = $first;
            $url    = 'https://' . implode('.', $tmp);
        }
        return $url;
    }

    function _editor_picks($cacheKey, $nama_halaman, $limit){
        $limit = 4;
        $interval = WebCache::App()->get_config('cachetime_short');
        if ($ret = checkCache($cacheKey))
            return $ret;

        $_q = cache('query_'. $cacheKey, function() use ($limit) {
        return News::join('tag_news', 'news_id', '=', 'tag_news_news_id')
                  ->join('tags','tags.id','=','tag_news.tag_news_tag_id')
                 ->where('news_domain_id', $this->config['domain_id'])
                 ->where('news_level', 1)
                 ->where('news_editor_pick', 1)
                 ->where('news_date_publish', '<', date('Y-m-d H:i:s'))
                 ->orderBy('news_date_publish', 'DESC')
                 ->groupBy('news_id')
                 ->take($limit)
                ->get()
                ->toArray();
        }, $interval );

        $_editor = $this->generate_news_url($_q);

        $list_editor = [];
        $no = 1;
        foreach ($_editor as $key => $value) {
            $list_editor[$key] = $_editor[$key];
            $list_editor[$key]['no'] = $no;
            $list_editor[$key]['TE'] = $nama_halaman;
            $no++;
            $list_editor[$key]['news_image_location_106'] = $value['news_image_location']. '/106x106-'. $value['news_image'];
        }

        //$ret = $this->view(BRILIO_CURRENT_VIEWPORT.'/box/right_editors_pick',['list_editor' => $list_editor], true);
        $ret = ['list_editor' => $list_editor];

        setCache($cacheKey, $ret, $interval);

        return $ret;

    }

    function _whats_hot ($nama_halaman){
        $cacheKey = BRILIO_CURRENT_VIEWPORT.'whats_hot_'. $nama_halaman;

        $interval = WebCache::App()->get_config('cachetime_short');
        $limit = 13;

        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $_query = cache('query_'. $cacheKey, function() use ($limit) {
            return WhatHappen::where('what_happen_domain_id', $this->config['domain_id'])
                    ->where('what_happen_flag', 1)
                    ->where('what_happen_schedule', '<', 'what_happen_schedule_end')
                    ->take($limit)
                    ->orderBy('what_happen_order', 'ASC')
                    ->get()
                    ->toArray();
        }, $interval);

        $whats_hot = [];
        foreach($_query as $key => $val)
        {
            $whats_hot[$key] = $_query[$key];

            $split_img_path= explode('/', $val['what_happen_image']);
            $filename= end($split_img_path);
            unset($split_img_path[count($split_img_path) - 1]);
            $temp = $this->config['klimg_url'] . implode('/', $split_img_path).'/278x185-'.$filename ;
            $whats_hot[$key]['what_happen_image_location_full'] = $temp;
        }

        setCache($cacheKey, serialize($whats_hot), $interval);
        return $whats_hot;
    }

    function _pop_under (){
        $cacheKey = BRILIO_CURRENT_VIEWPORT.'_pop_under';

        $interval = WebCache::App()->get_config('cachetime_default');
        $limit = 1;

        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $_pop_under = cache('query_'. $cacheKey, function() use ($limit) {
            return InternalBanner::where('popup_domain_id', $this->config['domain_id'])
                    ->where('popup_status', 1)
                    ->where('popup_type', 'pop_under')
                    ->where('popup_schedule_start', '<',  date('Y-m-d H:i:s'))
                    ->where('popup_schedule_end', '>',  date('Y-m-d H:i:s'))
                    ->take($limit)
                    ->orderBy('popup_id', 'ASC')
                    ->get()
                    ->toArray();
        }, $interval);

        setCache($cacheKey, serialize($_pop_under), $interval);
        return $_pop_under;
    }

    function _most_popular_new($cacheKey, $nama_halaman, $limit)
    {
        $interval = WebCache::App()->get_config('cachetime_short');
        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        $tgl_sekarang       = date('Y-m-d');
        $date               = date_create($tgl_sekarang);
        date_add($date, date_interval_create_from_date_string('-2 day'));
        $tgl_sebelumnya     = date_format($date, 'Y-m-d');

        $q_most_popular_2_days_ago = cache($cacheKey, function() use ($limit, $tgl_sebelumnya) {
            return Jsview::whereHas('News', function($q) use ($tgl_sebelumnya) {
                        $q->where('news_domain_id', '3');
                        $q->where('news_date_publish', '<', date('Y-m-d H:i:s'));
                        $q->where('news_level', '1');
                        $q->whereBetween('news_date_publish', [$tgl_sebelumnya, date('Y-m-d')]);
                    })
                    ->with(['News' => function ($q){
                        $q->select('news_id', 'news_category' , 'news_url', 'news_title', 'news_type');
                    }])
                    ->orderBy('jsview_counter', 'DESC')
                    ->take($limit)
                    ->get()->toArray();
        }, $interval );
        $list_most_popular['LIST_MOST_POPULAR'] = '';

        if(empty($q_most_popular_2_days_ago))
        {
            date_add($date, date_interval_create_from_date_string('-4 day'));
            $tgl_sebelumnya     = date_format($date, 'Y-m-d');
            $q_most_popular     = cache($cacheKey, function() use ($limit, $tgl_sebelumnya) {
                return Jsview::whereHas('News', function($q) use ($tgl_sebelumnya) {
                        $q->where('news_domain_id', '3');
                        $q->where('news_date_publish', '<', date('Y-m-d H:i:s'));
                        $q->where('news_level', '1');
                        $q->whereBetween('news_date_publish', ['2016-01-25', date('Y-m-d')]);
                        })
                        ->with(['News' => function ($q){
                            $q->select('news_id', 'news_category' , 'news_url', 'news_title', 'news_type');
                        }])
                        ->orderBy('jsview_counter', 'DESC')
                        ->take($limit)
                        ->get()->toArray();
                }, 3600);
        }
        else
        {
            $q_most_popular = $q_most_popular_2_days_ago;
        }

        if(!empty($q_most_popular))
        {
            $no = 1;
            foreach ($q_most_popular as $b) {
                $category_meta = $this->_category($b['news']['news_category']);

                $category = $category_meta['CATEGORY_URL'];

                if ($b['news']['news_type'] == '1')
                {
                    $news_link = $this->config['base_url'] . 'photo/' . $category . '/' . $b['news']['news_url'] . '.html';
                }
                elseif ($b['news']['news_type'] == '2')
                {
                    $news_link = $this->config['base_url'] . 'video/' . $category . '/' . $b['news']['news_url'] . '.html';
                }
                else
                {
                    $news_link = $this->config['base_url'] . $category . '/' . $b['news']['news_url'] . '.html';
                }

                $most_popular_data["NO"] = $no;
                $most_popular_data["NAMA_HALAMAN"] = $nama_halaman;
                $most_popular_data["NEWS_TITLE"] = $b['news']["news_title"];
                $most_popular_data["NEWS_URL"] = $news_link;
                $list_most_popular['LIST_MOST_POPULAR'][] = $most_popular_data;
                $no++;
            }
        }

        $ret = $list_most_popular;

        setCache($cacheKey, $ret, $interval);

        return $ret;

    }

    function _trending($limit, $TE)
    {
        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = BRILIO_CURRENT_VIEWPORT . '_trending_' . $TE;
        $limit = 30 ;

        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        $two_days_ago    = date('Y-m-d',strtotime('- 2 days', time()));
        $seven_days_ago = date('Y-m-d',strtotime('- 7 days', time()));
        $q_trending = cache("desktop_query".$cacheKey, function() use ($two_days_ago, $seven_days_ago) {
           return News::where('news_domain_id', '=', $this->config['domain_id'])
                    ->where('news_level', '=', '1')
                    ->where('news_date_publish', '<=', $two_days_ago)
                    ->where('news_date_publish', '>=', $seven_days_ago)
                    ->orderby('news_date_publish', 'DESC')
                    ->with('news_tag_list')
                    ->take(30)
                    ->get()
                    ->toArray();
        }, $interval);

        if(!empty($q_trending))
        {
            $q_trending = $this->generate_news_url($q_trending);

            foreach ($q_trending as $key => $val)
            {
               $random = array_rand($q_trending);
               $trending[] = $q_trending[ $random ];
               unset($q_trending[ $random ]);
            }

            $no = 1;
            foreach ($trending as $b)
            {
                $tmp["TE"]              = $TE;
                $tmp["NO"]              = $no;
                $tmp["NEWS_TITLE"]      = $b["news_title"];
                $tmp["NEWS_URL"]        = $b['news_url_full'];
                $tmp["NEWS_IMAGE_URL"]  = $this->config['klimg_url']. $b['news_image_location_raw']. '/200x100-'. $b['news_image_potrait'];
                $tmp["NEWS_TAG"]        = $b['news_tag_list'];
                $list_trending[]   = $tmp ;
                $no++;
            }
        }
        else
        {
            $list_trending = [];
        }

        $ret = ['trending' => $list_trending, 'TE' => $TE];
        setCache($cacheKey, $ret, $interval);

        return $ret;
    }


    function _check_this_out($nama_halaman, $limit)
    {

        $interval = WebCache::App()->get_config('cachetime_short');
        $cacheKey = BRILIO_CURRENT_VIEWPORT."_check_this_out_".$nama_halaman;
        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        $q_check_this_out = cache('query_'.$cacheKey, function() use ($limit){
            return WhatHappen::groupBy('what_happen_id')
                    ->where('what_happen_domain_id', '=', $this->config['domain_id'])
                    ->where('what_happen_flag', '=', '1')
                    ->take($limit)
                    ->orderBy('what_happen_order', 'ASC')
                    ->orderBy('what_happen_schedule', 'DESC')
                    ->get()->toArray();
        }, $interval );

        if ($q_check_this_out) {

            $no = 1;
            foreach ($q_check_this_out as $b) {
                $pecah      = explode("/", $b['what_happen_image']);
                $folder     = $pecah[0];
                $year       = $pecah[1];
                $month      = $pecah[2];
                $date       = $pecah[3];
                $id         = $pecah[4];
                $nama_file  = $pecah[5];
                $ukuran     = '250x125';
                $check_this_out_data["NO"]                      = $no;
                $check_this_out_data["NAMA_HALAMAN"]            = $nama_halaman;
                $check_this_out_data["WHAT_HAPPEN_URL"]         = $b["what_happen_url"];
                $check_this_out_data["WHAT_HAPPEN_CONTENT"]     = $b["what_happen_content"];
                $check_this_out_data["WHAT_HAPPEN_IMAGES"]      = $this->config['klimg_url'] . $folder . '/' . $year . '/' . $month . '/' . $date . '/' . $id . '/' . $ukuran . '-' . $nama_file;
                $list_check_this_out['LIST_CHECK_THIS_OUT'][]   = $check_this_out_data;
                $no++;
            }
        } else {
            $list_check_this_out['LIST_CHECK_THIS_OUT'] = "";
        }

        //$ret = $this->view(BRILIO_CURRENT_VIEWPORT.'/box/right_check_this_out', $list_check_this_out, true);
        $ret = $list_check_this_out;
        setCache($cacheKey, $ret, $interval);

        return $ret;
    }

    function _just_update($cacheKey, $TE){

        $interval = WebCache::App()->get_config('cachetime_short');
        $limit = 7;
        if ($ret = checkCache($cacheKey))
        {
            return $ret;
        }

        $q_just_update = cache('query_'.$cacheKey, function() use ($limit){
            return News::groupBy('news_id')
                    ->where('news_domain_id', $this->config['domain_id'])
                    ->where('news_date_publish', '<', date('Y-m-d H:i:s'))
                    ->where('news_level', '1')
                    ->orderBy('news_date_publish', 'DESC')
                    ->take($limit)
                    ->get()->toArray();
        }, $interval);

        $_just = $this->generate_news_url($q_just_update);

        foreach ($_just as $key => $value) {
            $_just[$key]['TE'] = $TE;
        }

        //$ret = $this->view(BRILIO_CURRENT_VIEWPORT.'/box/right_just_update', ['list_just_update' => $_just], true);
        $ret = ['list_just_update' => $_just];

        setCache($cacheKey, $ret, $interval);

        return $ret;

    }


    function _category($category) {
        $interval = WebCache::App()->get_config('cachetime_day');
        // $this->model("rubric_model");
        $string = $category;
        $pattern = '/([^0-9]+)/';
        $replacement = '';
        $category_id = preg_replace($pattern, $replacement, $string);

        $cacheKey = BRILIO_CURRENT_VIEWPORT.'_category_' . $category_id;
        if ($ret = checkCache($cacheKey))
            return $ret;

        // $query_category = $this->rubric_model->get_category($category_id);
        $query_category = cache('query_'.$cacheKey, function() use ($category_id){
            $row = Rubrics::where('rubrics_id', $category_id)->get()->toArray();
            return $row[0];
        }, $interval);

        $ret['CATEGORY_ID'] = $query_category['rubrics_id'];
        $ret['CATEGORY_TITLE'] = $query_category['rubrics_name'];
        $ret['CATEGORY_URL'] = str_replace(' ', '-', strtolower($query_category['rubrics_common']));


        setCache($cacheKey, $ret, $interval);

        return $ret;
    }

    function _tag($tag_id) {
        $this->model("tags_model");
        $cacheKey = 'tag-' . $tag_id;
        if ($ret = checkCache($cacheKey))
            return $ret;

        $query_tag = $this->tags_model->get_tag($tag_id);
        if (count($query_tag) != 0) {
            $huruf_awal = substr(strtolower($query_tag["tag_url"]), 0, 1);

            $ret['TAG_TITLE'] = $query_tag['tag'];
            $ret['TAG_URL'] = $this->config['rel_url'] . 'tag/' . $huruf_awal . '/' . $query_tag['tag_url'] . '/';
        } else {
            $ret['TAG_TITLE'] = '';
            $ret['TAG_URL'] = '';
        }

        $interval = WebCache::App()->get_config('cachetime_day');
        setCache($cacheKey, $ret, $interval);

        return $ret;
    }

    function _splitter($data_content, $page = 1, $per_page = 5) {

        $min = (($page * $per_page) - $per_page) + 1;
        $max = $page * $per_page;
        $count = 1;
        $page_data = array();
        foreach ($data_content as $item) {
            if ($count >= $min && $count <= $max) {
                $page_data[] = $item;
            }
            $count++;
        }
        $pages = ceil(count($data_content) / $per_page);
        return array(
            'DATA_CONTENT' => $page_data,
            'JML_DATA' => $pages,
            // 'HALAMAN' => $page
            'HALAMAN' => str_replace('.html', '', $page)
        );
    }

    function _today_tags($offcet, $limit, $order, $te, $site = '') {
        $interval = WebCache::App()->get_config('cachetime_default');
        $cacheKey = BRILIO_CURRENT_VIEWPORT.'_tag_bottom_' . $te. '_'. $site;

        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }

        $tag_today = cache($cacheKey, function() use ($order)
        {
            return TagToday::where('status', '=', 1)
                            ->where('domain_id', '=', $this->config['domain_id'])
                            ->where('order', '>', $order)
                            ->with(['sponsor_tag' => function ($q) {
                                  $q->select('id', 'tag', 'brand_name', 'brand_url', 'url');
                            }])
                            ->with(['tag_data' => function ($q) {
                                  $q->select('id', 'tag', 'tag_title', 'tag_url');
                            }])
                            ->take(10)
                            ->orderBy('order', 'asc')
                            ->get()
                            ->toArray();
        }, $interval);

        if ($tag_today)
        {
            foreach ($tag_today as $k => $v)
            {
                $c["TAG_ID"] = $v['id'];
                $c["TAG_TITLE"] = $v['title'];
                $c["TE"] = $te;

                if ( !empty($v['sponsor_tag']) ) {
                  $huruf_awal = substr(strtolower($v['sponsor_tag'][0]['url']), 0, 1);
                  $c['TAG_URL'] = $this->config['rel_url']. 'brands/'. $huruf_awal. '/' .$v['sponsor_tag'][0]['url'];
                }
                if( !empty($v['tag_data']) ){
                  $huruf_awal = substr(strtolower($v['tag_data'][0]['tag_url']), 0, 1);
                  $c['TAG_URL'] = $this->config['rel_url']. 'tag/'. $huruf_awal. '/' .$v['tag_data'][0]['tag_url'];
                }
                $list_today_tags_data[] = $c;
            }
        }
        else
        {
            $list_today_tags_data[] = '';
        }

        // $ret = $this->view('box/_tags_bottom', ['list_today_tags' => $list_today_tags_data], true);
        $ret = $list_today_tags_data ;
        setCache($cacheKey, serialize($ret), $interval);

        return $ret;
    }

    protected function _amp_render($template, $data = array()) {
        if (!empty($data['box_announcer']))
        {
            $box_announcer =  $data['box_announcer'];
        }
        else
        {
            $box_announcer = '';
        }
        $layout_data = [
            'box_announcer' => $box_announcer,
            'tags_top'      => $this->view('mobile/box/amp_tags_top', ['list_tags_bottom' => $this->_today_tags(0, 10, 0, $data['TE_2']) ], true),
            'content'       => $this->view($template, $data, true),
            'meta'          => $data['meta'],
            'full_url'      => $data['full_url'],
            'canonical'     => $data['meta']['og_url'],
            'date_publish'  => $data['news_data']['news_date_publish'],
        ];
        return $this->view($this->config['view_folder'].'/layouts/amp/amp_main', $layout_data);
    }

    protected function _amp_validation($news_detail)
     {
        $content = $news_detail['news_content'];

        // CLEAR DENIED PROPERTY
        $content = preg_replace('/(class="embed-script")/', '', $content);
        $content = preg_replace('/(<script[^>]*>([\s\S]*?)<\/script>)/', '', $content);
        $content = preg_replace('/(style="[^"]*")/', '', $content);
        $content = preg_replace('/(onclick="[^"]*")/', '', $content);
        $content = preg_replace('/(onclick=&quot;[^.?]+&quot;)/', '', $content);
        $content = str_replace('width="100%"', '', $content);
        $content = preg_replace('/frameborder="no"/', '', $content);
        $content = preg_replace('/(<div id="fb-root"[^>]*>([\s\S]*?)<\/div>)/', '', $content);
        // END CLEAR DENIED PROPERTY

        // INSTAGRAM VALIDATION
        // get instagram code
        preg_match_all('/instagram.com\/p\/([a-zA-z0-9-]+)\//', $content, $matches_code_instagram);
        if(!empty($matches_code_instagram[1]))
        {
            foreach ($matches_code_instagram[1] as $code)
            {
                $data_amp_instagram = '<p><amp-instagram
                                            data-shortcode="'. $code .'"
                                            width="383"
                                            height="249"
                                            layout="responsive">
                                        </amp-instagram></p>';
                preg_match('/(<blockquote class="instagram-media"[^>]*>([\s\S]*?)<\/blockquote>)/', $content, $match);
                $content = str_replace($match[0], $data_amp_instagram, $content);
            }
        }
        // END INSTAGRAM VALIDATION

        // TWITTER VALIDATION
        // get twitter code
        preg_match_all('/https:\/\/twitter.com\/[^"]+(\/[0-9]+)/', $content, $matches_code_twitter);
        if(!empty($matches_code_twitter[1]))
        {
            foreach ($matches_code_twitter[1] as $code)
            {
                $data_amp_twitter = '<p><amp-twitter
                                        width="381"
                                        height="241"
                                        layout="responsive"
                                        data-tweetid="'. str_replace('/', '', $code) .'">
                                    </amp-twitter></p>';

                preg_match('/(<blockquote class="twitter-tweet"[^>]*>([\s\S]*?)<\/blockquote>)/', $content, $match_tweet);
                if(!empty($match_tweet))
                {
                    $content = str_replace($match_tweet[0], $data_amp_twitter, $content);
                }
                else
                {
                    preg_match('/(<blockquote class="twitter-video"[^>]*>([\s\S]*?)<\/blockquote>)/', $content, $match_tw_video);
                    if(!empty($match_tw_video))
                    {
                        $content = str_replace($match_tw_video[0], $data_amp_twitter, $content);
                    }
               }
            }
        }
        // END TWITTER VALIDATION

        // FACEBOOK VALIDATION
        preg_match_all('/https:\/\/www.facebook.com\/[^"]+(\/[0-9]+)/', $content, $matches_code_facebook);
        if(!empty($matches_code_facebook[0]))
        {
            preg_replace('/(<div id="fb-root"[^>]*>([\s\S]*?)<\/div>)/', '', $content);
            // get facebook url that needed
            for($i = 0; $i<count($matches_code_facebook[0]); $i++)
            {
                $fb_url[] = $matches_code_facebook[0][$i];
                $i = $i + 2;
            }
            foreach ($fb_url as $data)
            {
                // helper replace cos can't use preg_replace
                preg_match('/(<div class="fb-xfbml-parse-ignore"[^>]*>([\s\S]*?)<\/div>)/', $content, $match_fb_xfbml);
                $content = str_replace($match_fb_xfbml[0], '', $content);

                if (preg_match("/videos/", $data, $match))
                {
                    $amp_fb_videos = '<p><amp-facebook width="552" height="310"
                                        layout="responsive"
                                        data-embed-as="video"
                                        data-href="'. $data .'/">
                                      </amp-facebook></p>';
                    preg_match('/(<div class="fb-video"[^>]*>([\s\S]*?)<\/div>)/', $content, $match_fb_video);
                    $amp['amp'][] = $amp_fb_videos;
                    $content = str_replace($match_fb_video[0], $amp_fb_videos, $content);
                }
                else
                {
                    $amp_fb_post = '<p><amp-facebook width="552" height="310"
                                    layout="responsive"
                                    data-href="'. $data .'/">
                              </amp-facebook></p>';
                    preg_match('/(<div class="fb-post"[^>]*>([\s\S]*?)<\/div>)/', $content, $match_fb_post);
                    $content = str_replace($match_fb_post[0], $amp_fb_post, $content);
                }
            }
        }
        // END FACEBOOK VALIDATION

        // IMG TAG VALIDATION
        $content = str_replace("<img ", "<amp-img width='383' height='256' layout='responsive' ", $content);
        // END IMG TAG VALIDATION

        // VIDEO TAG VALIDATION
        $content = str_replace("<video ", "<amp-video width='383' height='256' layout='responsive' ", $content);
        $content = str_replace("</video>", "</amp-video>' ", $content);
        // END VIDEO TAG VALIDATION

        // AUDIO TAG VALIDATION
        $content = str_replace("<audio ", "<amp-audio width='383' ", $content);
        $content = str_replace("</audio>", "</amp-audio>", $content);
        // END AUDIO TAG VALIDATION

        // IFRAME VALIDATION
        $content = str_replace("<iframe ", '<amp-iframe
                                            width="383"
                                            height="249"
                                            sandbox="allow-same-origin allow-scripts allow-forms"
                                            layout="responsive"
                                            frameborder="0" ', $content);
        $content = str_replace("</iframe>", '</amp-iframe>', $content);
        // END IFRAME VALIDATION

        // FOR EMBED VIDEO
        if(!empty($news_detail['news_embed_video']['video_path']))
        {
            $content_embed_vid = $news_detail['news_embed_video']['video_path'];
            $content_embed_vid = preg_replace("/allowfullscreen/", "", $content_embed_vid);
            $content_embed_vid = str_replace("<iframe", '<amp-iframe
                                            width="383"
                                            height="249"
                                            sandbox="allow-same-origin allow-scripts allow-forms"
                                            layout="responsive"
                                            frameborder="0" ', $content_embed_vid);
            $content_embed_vid = str_replace("</iframe>", '</amp-iframe>', $content_embed_vid);
            $content_embed_vid = str_replace("&lt;iframe", '<amp-iframe
                                                width="383"
                                                height="249"
                                                sandbox="allow-same-origin allow-scripts allow-forms"
                                                layout="responsive"
                                                frameborder="0" ', $content_embed_vid);
            $content_embed_vid = str_replace("&lt;/iframe&gt;", '</amp-iframe>', $content_embed_vid);
            $news_detail['news_embed_video']['video_path'] = $content_embed_vid;
        }

        $news_detail['news_content'] = $content;
        return $news_detail;
     }

     function _promote_video (){

         if ($ret = checkCache($cacheKey))
         {
             return unserialize($ret);
         }

         $_promote_video = cache('query_'. $cacheKey, function() use ($limit) {
             return InternalBanner::where('popup_domain_id', $this->config['domain_id'])
                     ->where('popup_status', 1)
                     ->where('popup_type', 'leaderboard')
                     ->where('popup_schedule_start', '<',  date('Y-m-d H:i:s'))
                     ->where('popup_schedule_end', '>',  date('Y-m-d H:i:s'))
                     ->take($limit)
                     ->orderBy('popup_id', 'ASC')
                     ->get()
                     ->toArray();
         }, $interval);
         setCache($cacheKey, serialize($_pop_under), $interval);
         return $_promote_video;
     }

     function get_video_sponsor($news_sponsorship, $init_brand){

       //news_sponsorship is an ID from sponsor_tag
       if ( !empty($news_sponsorship) || $init_brand == TRUE )
       {
           if (in_array($news_sponsorship, $this->config['video_sponsor']['allowed_sponsorship_tag'])) {
               //to check for allowed sponsor_tag to show video
               $promote_video = $this->config['video_sponsor'];
           }
           else{
             $promote_video = '';
           }
       }
       else
       {
           $promote_video = $this->config['video_sponsor'];
       }
       return $promote_video;
     }

     function _get_announcement(){
       $cacheKey = BRILIO_CURRENT_VIEWPORT.'_announcement';

       $interval = WebCache::App()->get_config('cachetime_default');
       $limit = 1;

       if ($ret = checkCache($cacheKey))
       {
           return unserialize($ret);
       }

       $_announcement = cache('query_'. $cacheKey, function() use ($limit) {
           return InternalBanner::where('popup_domain_id', $this->config['domain_id'])
                   ->where('popup_status', 1)
                   ->where('popup_type', 'announcement')
                   ->where('popup_schedule_start', '<',  date('Y-m-d H:i:s'))
                   ->where('popup_schedule_end', '>',  date('Y-m-d H:i:s'))
                  //  ->take($limit)
                   ->orderBy('popup_id', 'ASC')
                   ->first()
                   ->toArray();
       }, $interval);

       setCache($cacheKey, serialize($_announcement), $interval);
       return $_announcement;
     }

     function _get_leaderboard($news_sponsorship, $init_brand){
       $cacheKey = BRILIO_CURRENT_VIEWPORT.'_leaderboard';

       $interval = WebCache::App()->get_config('cachetime_default');
       $limit = 1;

       if ($ret = checkCache($cacheKey))
       {
           return unserialize($ret);
       }

       $_leaderboard['leaderboard_data'] = cache('query_'. $cacheKey, function() use ($limit) {
           return InternalBanner::where('popup_domain_id', $this->config['domain_id'])
                   ->where('popup_status', 1)
                   ->where('popup_type', 'leaderboard')
                   ->where('popup_schedule_start', '<',  date('Y-m-d H:i:s'))
                   ->where('popup_schedule_end', '>',  date('Y-m-d H:i:s'))
                   ->orderBy('popup_id', 'ASC')
                   ->first()
                   ->toArray();
       }, $interval);

       setCache($cacheKey, serialize($_leaderboard), $interval);
       return $_leaderboard;
     }

     function mobile_menu_bottom($TE){

       $_list_menu = [];
       foreach ($this->_categories['cat_list'] as $key => $val)
       {
          $tmp = array_rand($this->_categories['cat_list']);
          $_list_menu['TE'] = $TE;
          $_list_menu['list_cat'][] = $this->_categories['cat_list'][$tmp];
          unset($this->_categories['cat_list'][$tmp]);
       }

      $ret = $_list_menu;
      return $ret;
     }

}
