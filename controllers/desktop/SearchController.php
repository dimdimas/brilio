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
        $meta =
        [
            'description'       => $post,
            'expires'           => date(DATE_RFC1036),
            'keywords'          => $post,
            'og_url'            => $this->config['base_url']. 'search-result/' . $post,
            'og_image'          => $this->config['base_url']. '/assets/img/logo-BRILIO.png',
            'img_url'           => $this->config['base_url']. '/assets/img/logo-BRILIO.png',
            'chartbeat_sections'=> $post,
            'chartbeat_authors' => 'Brilio.net',
            'meta_alternate'    => $this->config['m_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
            'iframe_kl'         => $this->config['base_url'] . substr($_SERVER['REQUEST_URI'], strlen($this->config['rel_url'])),
        ];

        $TE = 'Search result';

        $data =
        [
            'meta'          => $meta,
            'url'           => $url,
            'nama_halaman'  => 'Search Result',
            'TE'            => $TE,
            'full_url'      => $url,
            'breadcrumb'    => $post,
            'NEWS'          => $post, //$this->search($post),
            'whats_hot'     => $this->_whats_hot($TE),
            'trending'      => $this->view('desktop/box/right_trending', $this->_trending(7, $TE), TRUE),
            'collect_email' => $this->view('desktop/box/_email', [], TRUE),
            'bottom_menu'   => $this->view('desktop/box/bottom_menu', [], TRUE),
        ];

        $this->_render('desktop/search/search', $data);

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
