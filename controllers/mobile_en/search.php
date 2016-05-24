<?php

class Search extends CController {

    function __construct() {
        parent::__construct();
        $this->model(array('news_model', 'today_tags_model', 'jsview_model', 'what_happen_model'));
        $this->library(array('table', 'lib_date'));
        $this->ctrler = 'archives';
    }

    function index() {

        $post = $_POST['search'];

        $content = $this->content($post);

        $dt = array(
            'title' => 'Homepage',
            'header' => '',
            'footer' => '',
            'content' => $content,
            'custom_css' => array(
                $this->config['assets_css_url'] . 'style.css'
            ),
            'custom_js' => array(
                $this->config['assets_js_url'] . 'main.js'
            ),
            'description' => $post,
            'url' => $post,
            'og_image' => $post,
            'expires' => $post,
            'keywords' => $post,
            'img_url' => $this->config['assets_image_url']
        );

        $this->render('mobile/templates/main', $dt);
    }

    //CONTENT
    function content($post) {
        $cn = array(
            'BASE_URL' => $this->config['rel_url'],
            'MENU_TODAY_TAGS' => $this->list_menu_tags_today(0, 10, 0),
            'CATEGORY_TITLE' => $post,
            'TAGS_TODAY' => $this->list_front_tags_today(),
            'NEWS' => $this->search($post),
            'LATEST' => $this->latest(),
            'CHECK_THIS_OUT' => $this->check_this_out()
        );
        return $this->view('mobile_en/search/_content', $cn, true);
    }

    //LIST TODAY TAG
    function list_front_tags_today() {
        $list_tags = $this->today_tags_model->get_list_tags_today();
        $list_tags_list['LIST_TAGS_TODAY'] = '';
        if ($list_tags) {
            foreach ($list_tags as $a => $b) {
                $c["ID_TAG"] = $b['id_tag'];
                $c["TAG_NAME"] = $b["title"];
                $huruf_awal = substr(strtolower($b["title"]), 0, 1);
                $c["TAG_LINK"] = $this->config['rel_url'] . 'tag/' . $huruf_awal . '/' . str_replace(' ', '-', strtolower($b["title"])) . '/';
                $list_tags_list['LIST_TAGS_TODAY'] .= $this->view('mobile_en/category/today_tag/_list', $c, true);
            }
        } else {
            $list_tags_list['LIST_TAGS_TODAY'] .= "";
        }

        return $this->view('mobile_en/search/today_tag/_view', $list_tags_list, true);
    }

    //NEWS
    function search($post) {
        //$post;
        $records['DATA_SEARCH'] = "INDONESIA";
        return $this->view('mobile_en/search/news/_view', $records, true);
    }

    //MOST VIEWED
    function most_viewed() {
        $most_viewed = $this->jsview_model->get_most_view();
        $list_most_viewed['LIST_MOST_VIEWED'] = '';
        if ($most_viewed) {
            $no = 1;
            foreach ($most_viewed as $a => $b) {
                $c["NO"] = $no;
                $c["NEWS_TITLE"] = $b["news_title"];
                $c["CATEGORY"] = str_replace(' ', '-', strtolower($b["news_rubrics_rubrics_common"]));
                $c["NEWS_URL"] = $b["news_url"];
                $list_most_viewed['LIST_MOST_VIEWED'] .= $this->view('mobile_en/search/most_viewed/_list', $c, true);
                $no++;
            }
        } else {
            $list_most_viewed['LIST_MOST_VIEWED'] .= "";
        }
        return $this->view('mobile_en/search/most_viewed/_view', $list_most_viewed, true);
    }

    //CHECK THIS OUT
    function check_this_out() {
        $check_this_out = $this->what_happen_model->get_check_this_out(20);
        $list_check_this_out['LIST_CHECK_THIS_OUT'] = '';
        if ($check_this_out) {
            $no = 1;
            foreach ($check_this_out as $a => $b) {

                $c["NO"] = $no;
                $c["WHAT_HAPPEN_URL"] = $b["what_happen_url"];
                $c["WHAT_HAPPEN_CONTENT"] = $b["what_happen_content"];
                $c["WHAT_HAPPEN_IMAGES"] = $this->config['klimg_url'] . $b["what_happen_image"];
                $list_check_this_out['LIST_CHECK_THIS_OUT'] .= $this->view('mobile_en/search/check_this_out/_list', $c, true);
                $no++;
            }
        } else {
            $list_check_this_out['LIST_CHECK_THIS_OUT'] .= "";
        }
        return $this->view('mobile_en/search/check_this_out/_view', $list_check_this_out, true);
    }

    //LATEST
    function latest() {
        $latest = $this->news_model->get_latest();
        $list_latest['LIST_LATEST'] = '';

        if ($latest) {
            $no = 1;
            foreach ($latest as $b) {
                $category = str_replace(' ', '-', strtolower($b['news_rubrics_rubrics_common']));
                $datetime = explode(" ", $b['news_entry']);
                $datetime_clear = explode("-", $datetime[0]);
                $year = $datetime_clear[0];
                $month = $datetime_clear[1];
                $date = $datetime_clear[2];
                if ($b['news_type'] == '1') {
                    $type_news = '<div class="left-post-img-type">
                                    <img src="' . $this->config['assets_image_url'] . 'photo-label.png"/>
                                </div>';
                    $news_link = $this->config['rel_url'] . 'photo/' . $category . '/' . $b['news_url'] . '.html';
                    $url_img_news = $this->config['klimg_url'] . 'photonews' . '/' . $year . '/' . $month . '/' . $date . '/' . $b['news_id'] . '/240x160-' . $b['news_image'];
                } elseif ($b['news_type'] == '2') {
                    $type_news = '<div class="left-post-img-type">
                                    <img src="' . $this->config['assets_image_url'] . 'video-label.png"/>
                                </div>';
                    $news_link = $this->config['rel_url'] . 'video/' . $category . '/' . $b['news_url'] . '.html';
                    $url_img_news = $this->config['klimg_url'] . 'video' . '/' . $year . '/' . $month . '/' . $date . '/' . $b['news_id'] . '/240x160-' . $b['news_image'];
                } else {
                    $type_news = '';
                    $news_link = $this->config['rel_url'] . $category . '/' . $b['news_url'] . '.html';
                    $url_img_news = $this->config['klimg_url'] . 'news' . '/' . $year . '/' . $month . '/' . $date . '/' . $b['news_id'] . '/240x160-' . $b['news_image'];
                }

                $c["NEWS_TITLE"] = $b["news_title"];
                $c["NEWS_URL"] = $news_link;
                $list_latest['LIST_LATEST'] .= $this->view('mobile_en/read/latest/_list', $c, true);
                $no++;
            }
        } else {
            $list_latest['LIST_LATEST'] = "";
        }
        return $this->view('mobile_en/read/latest/_view', $list_latest, true);
    }

    //TODAY TAG -> MENU
    function list_menu_tags_today($offcet, $limit, $order) {
        $list_tags = $this->today_tags_model->index_list_tags_today($offcet, $limit, $order);

        $list_tags_list['LIST_TAGS_TODAY'] = '';

        if ($list_tags) {
            foreach ($list_tags as $a => $b) {
                $c["ID_TAG"] = $b['id_tag'];
                $c["TAG_NAME"] = $b["title"];
                $huruf_awal = substr(strtolower($b["title"]), 0, 1);
                $c["TAG_LINK"] = $this->config['rel_url'] . 'tag/' . $huruf_awal . '/' . str_replace(' ', '-', strtolower($b["tag"])) . '/';
                $list_tags_list['LIST_TAGS_TODAY'] .= $this->view('mobile_en/index/menu_today_tag/_list', $c, true);
            }
        } else {
            $list_tags_list['LIST_TAGS_TODAY'] .= "";
        }

        return $this->view('mobile_en/index/menu_today_tag/_view', $list_tags_list, true);
    }

}
