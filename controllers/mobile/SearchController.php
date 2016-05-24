<?php

class SearchController extends CController {

    function __construct()
    {
        parent::__construct();
        $this->model(['News', 'TagNews', 'Tag', 'Jsview', 'Rubrics'], null, true);
        $this->library(array('table', 'lib_date'));
        $this->ctrler = 'archives';
        $this->helper('mongodb');
    }

    function Index()
    {
        $post        = $_POST['inputSearch'];
        $newtitle    = $this->string_limit_words($post, 6); // First 6 words
        $urltitle    = preg_replace('/[^a-z0-9]/i', ' ', $newtitle);
        $newurltitle = str_replace(" ", "-", $newtitle);
        $search      = strtolower($newurltitle);
        $url         = $this->config['base_url'] . 'search-result/' . $search . '/';
        echo "<meta http-equiv ='refresh' content='0; url=" . $url . "'>";

        exit;
    }
//
    function string_limit_words($string, $word_limit)
    {
        $words = explode(' ', $string);
        return implode(' ', array_slice($words, 0, $word_limit));
    }

    function bla($keywords){
        echo $keywords;
    }

    function data($keywords)
    {
        $post = $keywords;

        $url    = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $meta = array(
            'meta_title'         => 'Search Result "'.$post.'" - Brilio.net' ,
            'meta_description'   => "Temukan segala kisah kehidupan, gaya hidup, renungan, inspirasi, dan ilmu pengetahuan di sini",
            'meta_keywords'      => $post,
            'og_url'            => $this->config['base_url'],
            'og_image'          => $this->config['base_url']. substr($this->config['assets_image_url'] . 'logo-BRILIO.png', strlen($this->config['rel_url'])),
            'og_image_secure'   => $this->config['base_url']. substr($this->config['assets_image_url'] . 'logo-BRILIO.png', strlen($this->config['rel_url'])),
            'img_url'           => $this->config['assets_image_url'],
            'expires'           => date(DATE_RFC1036),
            'last_modifed'      => date(DATE_RFC1036),
            'chartbeat_sections' => $post,
            'chartbeat_authors'  => 'Brilio.net',
            'meta_alternate'     => $this->config['www_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'          => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        );

        $TE = 'Search result';

        $data = array(
            'full_url'      => $url,
            'meta'          => $meta,
            'TE_2'          => $TE,
            'NEWS'          => $post, //$this->search($post),
            'breadcrumb'    => $post,
            'box_announcer' => $this->view('mobile/box/_announcer_banner', [], TRUE),
            'img_url'       => $this->config['assets_image_url']
        );

        $this->_mobile_render('mobile/search/search', $data);

    }

    function breadcumb($post) {
        $data_breadcumb['DATA_BREADCUMB'] = $post;
        return $data_breadcumb;
    }

    //NEWS
    function search($post) {

        if ($ret = checkCache($cacheKey))
            return $ret;

        $records['DATA_SEARCH'] = $post;

        return $records;
    }

}
