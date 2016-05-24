<?php

class FeedController extends CController {

    function __construct() {
        parent::__construct();
        $this->model(array('news_model'));
        $this->model(['News', 'TagNews', 'PhotonewsDetail', 'WhatHappen', 'NewsPaging', 'NewsRelated'], null, TRUE);
        $this->library(array('table', 'lib_date'));
        $this->helper('mongodb');        
    }

    function index() 
    {
        Header("Content-Type: text/xml");
        $data = 
        [
            'content' => $this->data_feed(),
        ];
        //echopre($data['content']);die;
        return $this->view('desktop/feed/feed_view', $data);
    }
     
    function data_feed() 
    {
        $cacheKey = 'feed/rss';
        
        $interval = WebCache::App()->get_config('cachetime_default');
        
        if ($ret = checkCache($cacheKey))
        {
            return unserialize($ret);
        }
            
        $news_feed = cache("query-".$cacheKey, function () {
            return News::where('news_domain_id', '=', $this->config['domain_id'])
                ->where('news_level', '=', '1')
                ->where('news_date_publish', '<', date('Y-m-d H:i:s'))
                ->orderBy('news_date_publish', 'DESC')
                ->take(30)
                ->get()
                ->toArray();
        }, $interval );

        $news_feed = $this->generate_news_url($news_feed);

        $view['ITEM'] = [];
        foreach ($news_feed as $b) 
        {
            $list['NEWS_IMAGES']        = $b['news_image_location'] . '180x90-potrait-' . $b['news_image'];  
            $list['NEWS_TITLE']         = htmlspecialchars(htmlentities($b['news_title']));
            $list['NEWS_ID']            = $b['news_id'];
            $list['NEWS_DATE_PUBLISH']  = date("r", strtotime($b['news_date_publish']));
            $list['NEWS_URL']           = $b['news_url_with_base'];
            $list['CATEGORY_TITLE']     = $b['news_category_name'];
            $list['CATEGORY_URL']       = $b['news_category_url'];
            $list['NEWS_SYNOPSIS']      = htmlspecialchars(htmlentities($b['news_synopsis']));
            $view['ITEM'][]             = $list;      
        }

        $view['PUBDATE'] = date("r");
        
        $ret = $view;
        setCache($cacheKey, serialize($ret), $interval);

        return $ret;          
    }
    
    private $count = 0;
    function br_count($matches) 
    {
        return '-<br/>' . $this->count++ . '-';
    }
    
    function ia_fb_validation($content, $news_id, $news_title)
    {
        $content = html_entity_decode($content, ENT_XML1, "UTF-8");
        // VALIDATION IMAGE WITH CAPTION
        $content = preg_replace('/<\/?\s?br\s?\/?><\/?\s?br\s?\/?>(<\/?\s?br\s?\/?>)*/', '<br/>', $content);

        preg_match_all('/<p class="content-image-caption"[^>]*>[\s\S]*?<\/p>/', $content, $match_img_cap);
        if(!empty($match_img_cap[0]))
        {
            foreach ($match_img_cap[0] as $v)
            {
                $temp = str_replace("<br />", " ", $v);
                $content = str_replace($v, $temp, $content);
            }
        }

        // VALIDATION IMAGE
        preg_match_all('/(<strong>|<em>|<figure>|<blockquote>|<h1>|<h2>|<h3>|<h4>|<h5>|<h6>|<ol>\s\n?<li>|<u>|<pre>|<br\s?\/>)?(<img [^>]*\/>)(<\/em>|<\/strong>|<\/figure>|<\/blockquote>|<\/h1>|<\/h2>|<\/h3>|<\/h4>|<\/h5>|<\/h6>|<\/li>\s\n?<\/ol>|<\/u>|<\/pre>|<br\s?\/>)?/', $content, $match_img);
        if(!empty($match_img[0]))
        {
            $no=0;
            foreach ($match_img[2] as $v)
            {                // clear alt property
                $v_cls_alt = preg_replace('/alt="[\\s\\S]*?"/', '', $v);
                $v_cls_alt = preg_replace('/title="[\\s\\S]*?"/', '', $v_cls_alt);
                if(preg_match('/<h[1-6]|<ol|<li|<pre|<blockquote/', $match_img[0][$no])){
                  $content = str_replace($match_img[0][$no], '<p><figure>'.$v_cls_alt.'</figure></p>', $content);
                }else{
                  $content = str_replace($match_img[0][$no], '<figure>'.$v_cls_alt.'</figure>', $content);
                }
                $no++;
            }
        }
        // VALIDATION <p>
        preg_match_all('/<p[^>]*>/', $content, $match_p_style);
        if(!empty($match_p_style[0]))
        {
            foreach ($match_p_style[0] as $v)
            {
                // clear style property
                $v_cls_title = preg_replace('/\s?style="[^"]*"/', '', $v);
                
                // 
                $content = str_replace($v, $v_cls_title, $content);
            }
        }

        // VALIDATION <IFRAME>
        preg_match_all('/<iframe[^>]*>/', $content, $match_iframe_attr);
        if(!empty($match_iframe_attr[0]))
        {
            foreach ($match_iframe_attr[0] as $v)
            {
                // clear width or height with '%' or 'px'
                $v_cls_title = str_replace('px', '', $v);
                $v_cls_title = preg_replace('/\s?width="[^"]*(%)"/', '', $v_cls_title);
                $v_cls_title = preg_replace('/\s?height="[^"]*(%)"/', '', $v_cls_title);
                
                $content = str_replace($v, $v_cls_title, $content);
            }
        }

        // VALIDATION LINK
        preg_match_all('/<a .*\/a>/', $content, $match_a);
        if(!empty($match_a[0]))
        {
            foreach ($match_a[0] as $a)
            {
                // clear alt property
                $v_cls_title = preg_replace('/title="[\\s\\S]*?"/', '', $a);
                
                // 
                $content = str_replace($a,$v_cls_title, $content);
            }
        }
        
        // VALIDATION TWITTER
        preg_match_all('/<blockquote class="twitter[^>]*>[\s\S]*?(<\/script><\/p>|<\/script>\s\n<\/p>)|<blockquote class="twitter[^>]*>[\s\S]*?<\/script>/', $content, $match_tw);
        if(!empty($match_tw[0]))
        {
            foreach ($match_tw[0] as $v)
            {
                $content = str_replace($v, '<figure class="op-social"><iframe>'.$v.'</iframe></figure>', $content);
            }
        }
        
        // VALIDATION INSTAGRAM
        //preg_match_all('/<blockquote class="instagram[^>]*>[\s\S]*?<\/script>/', $content, $match_ig);
        preg_match_all('/<p>(<blockquote class="instagram[^>]*>[\s\S]*?<\/script>)<\/p>/', $content, $match_ig_first);
        if(!empty($match_ig_first[1])){
            foreach ($match_ig_first[1] as $v)
            {
                $content = str_replace($v, '<figure class="op-social"><iframe>'.$v.'</iframe></figure>', $content);
            }
                
        }else{
            preg_match_all('/<blockquote class="instagram[^>]*>[\s\S]*?(<\/script><\/p>|<\/script>\s\n<\/p>)|<blockquote class="instagram[^>]*>[\s\S]*?<\/script>/', $content, $match_ig);
            if(!empty($match_ig[0]))
            {
                foreach ($match_ig[0] as $v)
                {
                    $content = str_replace($v, '<figure class="op-social"><iframe>'.$v.'</iframe></figure>', $content);
                }
                
            }
        }
        
        // VALIDATION FACEBOOK
        preg_match_all('/<div id="fb-root">[\s\S]*?<\/blockquote>[\s\S]*?<\/div>[\s\S]*?<\/div>/', $content, $match_fb);
        if(!empty($match_fb[0]))
        {
            foreach ($match_fb[0] as $v)
            {
                $content = str_replace($v, '<figure class="op-social"><iframe>'.$v.'</iframe></figure>', $content);
            }
        }
        
        // VALIDATION YOUTUBE
        preg_match_all('/<iframe.*(youtube.com|vimeo.com).*<\/iframe>/', $content, $match_yt);
        if(!empty($match_yt[0]))
        {
            foreach ($match_yt[0] as $v)
            {
                $content = str_replace($v, '<figure class="op-social">'.$v.'</figure>', $content);
            }
        }
        
        // VALIDATION SOUNDCLOUDE  
        preg_match_all('/<iframe src="https:\/\/w.soundcloud.com\/player[\s\S]*?<\/iframe>/', $content, $match_sc);
        //preg_match_all('/<iframe.*https:\/\/w.soundcloud.com\/player\/.*<\/iframe>/', $content, $match_sc);
        if(!empty($match_sc[0]))
        {
            foreach ($match_sc[0] as $v)
            {
                $content = str_replace($v, '<figure class="op-interactive">'.$v.'</figure>', $content);
            }
            
        }
        
        // VALIDATION SURVEY <iframe src="https:\/\/www.brilio.net\/survey[\s\S]*?<\/iframe
        preg_match_all('/<iframe id="survey-frame[^>]*><\/iframe>/', $content, $match_sv);
        //preg_match_all('/<iframe.*https:\/\/www.brilio.net\/survey.*<\/iframe>/', $content, $match_sv);
        if(!empty($match_sv[0]))
        {
            foreach ($match_sv[0] as $v)
            {
                $content = str_replace($v, '<figure class="op-interactive">'.$v.'</figure>', $content);
            }
            
        }
        // VALIDATION <BR> IN <STRONG>
        preg_match_all('/<strong>[\s\S]*?<\/strong>/', $content, $match_strong);
        if(!empty($match_strong[0]))
        {
            foreach ($match_strong[0] as $v_strong)
            {
                if(preg_match('/<br[\s]?\/>/', $v_strong)){
                    $temp_strong = preg_replace('/<br[\s]?\/>/', '', $v_strong);
                    $content = str_replace($v_strong, $temp_strong.'<br/>', $content);
                }
            }
        }
        // VALIDATION <BR> IN <P>
        //Count <br /> or <br> in content

        $content = preg_replace_callback('/<br[\s]?[\/]?>|<BR[\s]?[\/]?>/', [$this, 'br_count'], $content);
        preg_match_all('/<p[\s]*?>[\s\S]*?<\/p>/', $content, $match_p);
        if(!empty($match_p[0]))
        {
            foreach ($match_p[0] as $value_p)
            {
                preg_match_all('/-<br\/>[0-9]+-/', $value_p, $match_br);
                $init = 0;
                if(!empty($match_br[0]))
                {

                    $tmp_p = $value_p;
                    foreach ($match_br[0] as $v_br) 
                    {
                        if($init < 5)
                        {
                            $tmp_p = str_replace($v_br, "<br />", $tmp_p);
                            
                        }
                        else
                        {
                            $tmp_p = str_replace($v_br, "</p><p>", $tmp_p);
                        }
                        $init++;
                    }
                    $content = str_replace($value_p, $tmp_p, $content);
                }
            }
        }


        //delete <br/> outside
        $content = preg_replace('/-<br\/>[0-9]+/','', $content);
        // VALIDATION <FIGURE> IN <P> with word
        $content = $this->figure_in_p($content);
        //VALIDATION BLOCKQUOTE NOT EMBED
        preg_match_all('/<blockquote>[\s\S]*?<\/blockquote>/', $content, $match_blockquote);
        if(!empty($match_blockquote[0]))
        {
            foreach ($match_blockquote[0] as $v_blockquote)
            {
                $tmp_blockquote = preg_replace('/<p>|<\/p>/','', $v_blockquote);
                $content = str_replace($v_blockquote, $tmp_blockquote, $content);
            }
        }


        //VALIDATION <SCRIPT> IN <P>
        preg_match_all('/<p>\s\n(<script[^>]*>[\s\S]*?<\/script>)\s\n<\/p>|<p>(<script[^>]*>[\s\S]*?<\/script>)<\/p>/', $content, $match_p_script);
        if(!empty($match_p_script[0]) && !empty($match_p_script[1]))
        {
            $no=0;
            foreach ($match_p_script[0] as $v_p_script)
            {  
               $content = str_replace($v_p_script, $match_p_script[1][$no], $content);
                $no++;
            }
        }
        
        // CROSSLINK
        $content = $this->crosslink($content, $news_id);
        
        // Clear <p><div> without sentences
        $content = preg_replace('/<strong[^>]*>([\s]|[<\/?\s?br\s?\/?>])*?<\/strong>/', '', $content);
        $content = preg_replace('/<p[^>]*>(\s|&nbsp;|<\/?\s?br\s?\/?>)*<\/?p>/', '', $content);
        $content = preg_replace('/<div>(\s|&nbsp;|<\/?\s?br\s?\/?>)*<\/div>/', '', $content);
        
        preg_match_all('/<p[^>]*>(\s|&nbsp;|<\/?\s?br\s?\/?>)*<\/?p>/', $content, $p_blank);
        if(!empty($p_blank[0]))
        {
            foreach ($p_blank[0] as $v_p_blank)
            {
                $content = str_replace($v_p_blank, '', $content);
            }
        }
        // VALIDATION <CAPTION> IN <EM>
        preg_match_all('/<\/figure>[\s\n]*?<p[^>]*>[<\/?\s?br\s?\/?>]*?<em>([\s\S]*?)<\/em>/', $content, $match_caption);
        if(!empty($match_caption[0]) && !empty($match_caption[1]))
        {
           $no=0;
           foreach ($match_caption[0] as $v_caption)
           {  
              $content = str_replace($v_caption, "<figcaption>".$match_caption[1][$no]."</figcaption></figure><p>", $content);
              $no++;
           }
        }

         // VALIDATION <CAPTION> IN <p class="content-image-caption">
        preg_match_all('/<\/figure>\s*?\n\s*?<p class="content-image-caption"[^>]*>([\s\S]*?)<\/p>/', $content, $match_caption);
        if(!empty($match_caption[0]) && !empty($match_caption[1]))
        {
           $no=0;
           foreach ($match_caption[0] as $v_caption)
           {  
              $content = str_replace($v_caption, "<figcaption>".$match_caption[1][$no]."</figcaption></figure>", $content);
              $no++;
           }
        }

        // VALIDATION <img> WITHOUT <caption>
        preg_match_all('/(<img [\s]*?src="[^>]*>)<\/figure>/', $content, $match_caption);
        if(!empty($match_caption[1]))
        {
           $no=0;
           foreach ($match_caption[1] as $v_caption)
           {  
              $content = str_replace($v_caption,$v_caption."<figcaption>$news_title</figcaption>", $content);
              $no++;
           }
        }
        //DELETE latin 1 <p>Â </p>
        $content = htmlentities($content);
        $content = str_replace('&lt;p&gt;&nbsp;&lt;/p&gt;', '', $content);
        $content = html_entity_decode($content, ENT_QUOTES | ENT_XML1, "UTF-8");    

        $content = preg_replace('/<p[^>]*?>([\s]|<br \/>)*?<\/p>/', '', $content);
        
        return $content;
    }

    function figure_in_p($content_new, $count=0, $step=0){
        // VALIDATION <FIGURE> IN <P> with word
        $step++;
        preg_match_all('/<p[^>]*>[\s\S]*?(<figure[^>]*>[\s\S]*?<\/figure>)/', $content_new, $match_p_figure);
        if(!empty($match_p_figure[0]))
        {
            $no=0;
            foreach ($match_p_figure[0] as $v_p_figure)
            { 
              //if not detect </p>
              if(!preg_match('/<\/p>(\s\n)?<figure[^>]*>[\s\S]*?<\/figure>/', $v_p_figure)){
                $count++;
                $content_new = str_replace($match_p_figure[1][$no], "</p>".$match_p_figure[1][$no]."<p>", $content_new);
              } 
              $no++;  
            }
            if($count > 0){
                $content_new = $this->figure_in_p($content_new,0,$step);
            }else{
                return $content_new;
            }
        }
        
        return $content_new;
        
    }
    
    function crosslink($content, $news_id, $is_feed = 'fb')
    {
        //function crosslink
        $cross = cache('query_news_crosslink_'.$news_id, function () use($news_id) {
                $cross_ = news_related::where('news_related_news_id', '=', $news_id)
                        ->get()
                        ->toArray();
                
                return $cross_;
                
        } ,  3600 );
        
        if(!empty($cross))
        {
            $no = 1;
            foreach ($cross as $v)
            {
                if(empty($v['news_related_title']))
                {
                    $title = '<h2><strong>RECOMMENDED</strong></h2>';
                }
                else
                {
                    $title = '<h2><strong>'. $v['news_related_title'] .'</strong></h2>';
                }
                
                $temp = json_decode($v['news_related_content']);
                
                $data_crosslink = '';
                $data_crosslink = $title;
                
                if($is_feed == "kurio")
                {
                    foreach ($temp as $v)
                    {
                        $temp = explode("|", $v);
                        $data_crosslink .= '<p><a href="'.$temp[1].'?utm_source=KurioApp&amp;utm_medium=Feed&amp;utm_campaign=BrilioKurio">'.$temp[0].'</a></p>';
                    }
                }
                else
                {   
                    foreach ($temp as $v)
                    {
                        $temp = explode("|", $v);
                        $data_crosslink .= '<p><a href="'.$temp[1].'">'.$temp[0].'</a></p>';
                    }
                }
                
                
                
                preg_match('/\[crosslink_'.$no.'\]/', $content, $match_cl);
                if(!empty($match_cl[0]))
                {
                    preg_match('/<p>[<br \/>]*?\[crosslink_'.$no.'\][<br \/>]*?<\/p>/', $content, $match_cl_in_p);
                    if(!empty($match_cl_in_p[0]))
                    {   
                        $content = str_replace($match_cl_in_p[0], $data_crosslink, $content);
                    }
                    else
                    {
                        $content = str_replace('[crosslink_'.$no.']', '', $content);
                        $content = $content."".$data_crosslink;
                    }
                }
                $no++;
            }
        }
        return html_entity_decode($content, ENT_XML1, "UTF-8");
    }

    function facebook()
    {
        $cacheKey = 'desktop/feed/fb';
        $interval = WebCache::App()->get_config('cachetime_long');
        
        $exclude_news_id = [];
        
        // GET NEWS ID FROM URL WHATS HOT
        $wh_news_id =  cache("query-whatshot-".$cacheKey, function (){
            $wh = WhatHappen::select('what_happen_url')
                                ->where('what_happen_domain_id', '=', $this->config['domain_id'])
                                ->get()
                                ->toArray();
            
            foreach ($wh as $v)
            {
                $parse_url  = explode('/', $v['what_happen_url']);
                $url        = substr(end($parse_url), 0, count(end($parse_url)) - 6);
                $news       = News::select('news_id')
                                    ->where('news_url', '=', $url)
                                    ->get()
                                    ->toArray();
                if(!empty($news[0]))
                {
                    $get_url[]  = $news[0]['news_id'];
                }
            }
            
            return $get_url;
            
        }, $interval);
        
        $exclude_news_id = $wh_news_id;
        
        $news = cache("query-".$cacheKey, function () use ($exclude_news_id){
            return News::where('news_domain_id', '=', $this->config['domain_id'])
                ->where('news_level', '=', '1')
                ->where('news_sensitive', '=', '0')
                ->where('news_top_headline', '=', '0')
                ->whereIn('news_type', ['0', '1'])
                ->whereNotIn('news_id', $exclude_news_id)
                ->where('news_date_publish', '<', date('Y-m-d H:i:s'))
                ->orderBy('news_date_publish', 'DESC')
                ->take(10)
                ->get()
                ->toArray();
        }, $interval );
        
        $news = $this->generate_news_url($news);
        
        foreach($news as $k => $v)
        {
            $data['date_pub']   = date('Y-m-d')."T00:00:00+07:00";
            $data['news_id']    = $v['news_id'];
            $data['full_url']   = $v['news_url_with_base'];
            $data['title']      = $v['news_title'];
            $data['synopsis']   = $v['news_synopsis'];
            $data['reporter']   = $v['reporter']['name'];
            $data['date_iso']   = str_replace(" ", "T", $v['news_date_publish'])."+07:00";
            $data['date_time']  = date("F j, g:i A", strtotime($v['news_date_publish']));
            $data['img_url']    = $v['news_image_location_full'];
            //FUNCTION READ Y M D
            $datetime       = explode(" ", $v['news_entry']);
            $datetime_clear = explode("-", $datetime[0]);
            $year           = $datetime_clear[0];
            $month          = $datetime_clear[1];
            $date           = $datetime_clear[2];
            if(empty($v['news_imageinfo']))
            {
                $data['img_info'] = 'Brilio.net';
            }
            else
            {
                $data['img_info'] = $v['news_imageinfo'];
            }
            $data['category_name'] = $v['news_category_name'];
            
            // Checking news_type for content style
            if($v['news_type'] == "0")
            {
                $tmp = html_entity_decode($v['news_content']);
                preg_match('/<!-- splitter content -->/', $tmp, $match);
                if($match)
                {
                    // SPLIT CONTENT
                    preg_match('/<p><!-- splitter content -->[\s\S]+/', $tmp , $match_split_no_word);
                    if($match_split_no_word){
                       $p_strong = ''; 
                    }else{
                       $p_strong = '</strong></p>';
                    }

                    preg_match('/<!-- splitter content -->[\s\S]+/', $tmp, $match_split);
                    $tmp = str_replace($match_split, '', $tmp);
                    
                    $content = $tmp . $p_strong .'
                                        <p>
                                            <a href="'. substr($v['news_url_with_base'], 0, count($v['news_url_with_base']) - 6) . '-splitnews-2.html" target="_blank">NEXT</a>
                                        </p>'; 
                    
                    //CHECK FOR DOUBLE <P>                   
                    $content = preg_replace('/(<p>)[^\w][^<]*<p>/', '<p>', $content);
                    
                    $data['content'] = $this->ia_fb_validation(html_entity_decode($content), $v['news_id'],$v['news_title']);
                }
                else
                {
                    if($v['has_paging'] == "1")
                    {
                        $news_paging = cache('query_paging_'. $v['news_id'] .$cacheKey , function() use($v) {
                             $paging = news_paging::where('news_paging_status','=',1)->where('news_paging_news_id',$v['news_id'])
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
                        
                        $news_intro = $this->ia_fb_validation(html_entity_decode($v['news_content']), $v['news_id'], $v['news_title']);
                        if($v['news_imageinfo']==''){
                            $news_imgcaption = $v['news_title'];
                        }else{
                            $news_imgcaption = $v['news_imageinfo'];
                        }
                        $news_img_secondary = $v['news_image_location'].$v['news_image_potrait'];
                        $content =  $news_intro .
                                      '<figure>
                                         <img src='. $news_img_secondary .' />
                                         <figcaption><cite>'. $news_imgcaption .'</cite></figcaption>
                                      </figure>
                                    <p>';
                        if(!empty($news_paging[0]))
                        {
                            $content .= 'Ulasan Berikutnya : <br />
                                    <a href="'. substr($v['news_url_with_base'], 0, count($v['news_url_with_base']) - 6) . '/' . $news_paging[0]['news_paging_url'] . '.html" target="_blank">1. '. $news_paging[0]['news_paging_title'] .'</a>
                                    </p>';  
                        }
                        
                        $data['content'] = html_entity_decode($content);
//                        echopre($data['content']);die;
                    }
                    else
                    {
                        $data['content'] = $this->ia_fb_validation(html_entity_decode($v['news_content']), $v['news_id'], $v['news_title']);
                    }
                }
            }
            elseif($v['news_type'] == "1")
            {
                // Content for photo news
                $photo = cache($v['news_id'].$cacheKey, function () use ($v) {
                    return PhotonewsDetail::where('photonews_newsid', '=', $v['news_id'])
                                            ->orderby('photonews_id', 'ASC')
                                            ->get()
                                            ->toArray();
                });
                $next_photo = substr($v['news_url_with_base'], 0, count($v['news_url_with_base']) - 6) . '/' . $photo[1]['photonews_url'] . '.html';
                $photonews_src = $this->config['klimg_url'] . 'photonews' . '/' . $year . '/' . $month . '/' . $date . '/' . $photo[0]['photonews_newsid'] . '/750xauto-' . basename($photo[0]['photonews_src']);
                $tmp = html_entity_decode($v['news_content']);
                $content = $tmp.'<p><img src='. $photonews_src .' /></p>
                                   <p class="content-image-caption">'.
                                       $photo[0]['photonews_description'].'                 
                                      <cite>'. $photo[0]['photonews_copyright'] .'</cite></p>   
                            <p>                 
                            Foto Berikutnya : <br />                    
                                <a href="'.$next_photo.'" target="_blank">'. $photo[1]['photonews_description'] .'</a>                  
                            </p>';
                $data['content'] = $this->ia_fb_validation(html_entity_decode($content), $v['news_id'], $v['news_title']);
                $data['content'] = html_entity_decode($data['content']);
            }
            $data_feed_fb[] = $data;
            
            
            
        }
//        echopre($data_feed_fb);die;
        //Header("Content-Type: text/xml");
        return $this->view('desktop/feed/feed_facebook', ['content' => $data_feed_fb]);
    }
    
    function kurio_validation($content, $url)
    {
        $content = str_replace("&mdash;", "—", $content);
        $content = str_replace("&nbsp;", " ", $content);
        $utm_default = "?utm_source=KurioApp&amp;utm_medium=Feed&amp;utm_campaign=BrilioKurio";
        
        // VALIDATION TWITTER
        preg_match_all('/<blockquote class="twitter[^>]*>[\s\S]*?<\/script>[<\/p>]?/', $content, $match_tw);
        if(!empty($match_tw[0]))
        {
            foreach ($match_tw[0] as $v)
            {
                $content = str_replace($v, "<a href='".$url.$utm_default."'>Lihat disini</a>", $content);
            }
        }

        // VALIDATION FACEBOOK
        preg_match_all('/<div id="fb-root">[\s\S]*?<\/blockquote>[\s\S]*?<\/div>[\s\S]*?<\/div>/', $content, $match_fb);
        if(!empty($match_fb[0]))
        {
            foreach ($match_fb[0] as $v)
            {
                $content = str_replace($v, "<a href='".$url.$utm_default."'>Lihat disini</a>", $content);
            }
        }
        
        // VALIDATION YOUTUBE OR VIMEO OR SOUNCLOUD
        preg_match_all('/<iframe.*(youtube.com|vimeo.com|soundcloud.com).*<\/iframe>/', $content, $match_yt);
        if(!empty($match_yt[0]))
        {
            foreach ($match_yt[0] as $v)
            {
                $content = str_replace($v, "<a href='".$url.$utm_default."'>Lihat disini</a>", $content);
            }
        }
        

        preg_match_all('/<blockquote class="instagram[^>]*>[\s\S]*?<\/script>/', $content, $match_ig);
        if(!empty($match_ig[0])){
            foreach ($match_ig[0] as $v)
            {
                $content = str_replace($v, "<a href='".$url.$utm_default."'>Lihat disini</a>", $content);
            }
                
        }

        $content = preg_replace('/<script[^>]*>[\s]*?<\/script>/', "<a href='".$url.$utm_default."'>Lihat disini</a>", $content);
        
        return $content;
    }
    
    function kurio()
    {
        $cacheKey = 'desktop/feed/kurio';
        $interval = 3600;
        
        $news = cache("query-".$cacheKey, function () {
            return News::where('news_domain_id', '=', $this->config['domain_id'])
                ->where('news_level', '=', '1')
                ->where('news_sensitive', '=', '0')
                ->where('news_top_headline', '=', '0')
                ->where('has_paging', '=', '0')
                ->whereIn('news_type', ['0', '1'])
                ->where('news_date_publish', '<', date('Y-m-d H:i:s'))
                ->where('news_sponsorship', NULL)
                ->orderBy('news_date_publish', 'DESC')
                ->take(20)
                ->get()
                ->toArray();
        }, $interval );
        
        $news = $this->generate_news_url($news);
        
        foreach($news as $k => $v)
        {
            $data['lastBuildDate']  = date('Y-m-d')."T".date('H:i:s')."+07:00";
            $data['news_id']        = $v['news_id'];
            $data['full_url']       = $v['news_url_with_base'];
            $data['title']          = $v['news_title'];
            $data['synopsis']       = $v['news_synopsis'];
            $data['category_name']  = $v['news_category_name'];
            $data['reporter']       = $v['reporter']['name'];
            $data['date_iso']       = str_replace(" ", "T", $v['news_date_publish'])."+07:00";
            $data['date_time']      = date("d M Y H:i:s", strtotime($v['news_date_publish']))." +0700";
            $data['img_url']        = $v['news_image_location_full'];
            $data['media_code']     = $this->config['video_sponsor']['url_code'];
            $data['media_title']    = $this->config['video_sponsor']['video_title'];
            $data['media_description'] = strtoupper($this->config['video_sponsor']['video_deskrip']);
            $data['img_headline']   = $v['news_image_location_full'];
            $data['img_mime']       = 'image/jpeg';
            $data['img_size']       = 0;
            
            if(empty($v['news_imageinfo']))
            {
                $data['img_info'] = 'Brilio.net';
            }
            else
            {
                $data['img_info'] = $v['news_imageinfo'];
            }
            
            if($v['news_type'] == "0")
            {
                $tmp = html_entity_decode($v['news_content']);
                preg_match('/<!-- splitter content -->/', $tmp, $match);
                if($match)
                {
                    // SPLIT CONTENT
                    preg_match('/<!-- splitter content -->[\s\S]+/', $tmp, $match_split);
                    $tmp = str_replace($match_split, '', $tmp);
                    
                    $content = $tmp.'</p>
                                <p><a href="'. substr($v['news_url_with_base'], 0, count($v['news_url_with_base']) - 6) . '-splitnews-2.html?utm_source=KurioApp&utm_medium=RSS&utm_campaign=BrilioKurio" target="_blank">NEXT</a></p>';
                    
                    $data['content'] = $this->crosslink(html_entity_decode($content), $v['news_id'], "kurio");
                }
                else
                {
                    $data['content'] = $this->crosslink(html_entity_decode($v['news_content']), $v['news_id'], "kurio");
                }
            }
            elseif($v['news_type'] == "1")
            {
                $photo = cache("query-".$v['news_id'].$cacheKey, function () use ($v) {
                    return PhotonewsDetail::where('photonews_newsid', '=', $v['news_id'])
                                            ->orderby('photonews_id', 'ASC')
                                            ->get()
                                            ->toArray();
                });
                $next_photo = substr($v['news_url_with_base'], 0, count($v['news_url_with_base']) - 6) . '/' . $photo[1]['photonews_url'] . '.html';
                $content = $v['news_content'].'             
                           <p><a href="'.$next_photo.'?utm_source=KurioApp&utm_medium=RSS&utm_campaign=BrilioKurio" target="_blank">'. $photo[1]['photonews_description'] .'</a></p>';
                
                $data['content'] = $this->crosslink(html_entity_decode($content), $v['news_id'], "kurio");
            }
            
            // Clear not needed
            $data['content'] = $this->kurio_validation($data['content'], $v['news_url_with_base']);
            
            $data_feed_kurio[] = $data;
        }
        
        Header("Content-Type: text/xml");
        return $this->view('desktop/feed/feed_kurio', ['content' => $data_feed_kurio]);
    }
}