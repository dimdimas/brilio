<?php

class News_model extends Model {

    public function __construct() {
        parent::__construct();

        mysql_query("SET time_zone='+07:00';");

        $this->prefix = $this->db_config['prefix'];
        $this->domain_id = $this->db_config['domain_id'];
        $this->table_news = $this->prefix . 'news';
        $this->table_tags = $this->prefix . 'tags';
        $this->table_news_rubrics = $this->prefix . 'news_rubrics';
        $this->table_today_tag = $this->prefix . 'today_tag';
        $this->table_jsview = $this->prefix . 'jsview';
        $this->table_what_happen = $this->prefix . 'what_happen';
        $this->table_fbinfo = $this->prefix . 'fbinfo';
        $this->table_tag_news = $this->prefix . 'tag_news';
    }

    //BRILIO 2.0

    public function index_headline($news_id) {
        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`')
                ->table($this->table_news)
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_top_headline = '1' AND " .
                        $this->table_news . ".news_id != '" . $news_id . "'  AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->limit(0, 4)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    public function index_headline_first($sticky_id) {
        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`')
                ->table($this->table_news)
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_top_headline = '1' AND " .
                        $this->table_news . ".news_id != '" . $sticky_id . "'  AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->limit(0, 1)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return isset($rows[0]) ? $rows[0] : array();
    }

    public function index_headline_second($news_date_publish, $sticky_id) {

        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`')
                ->table($this->table_news)
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_top_headline = '1' AND " .
                        $this->table_news . ".news_id != '" . $sticky_id . "'  AND " .
                        $this->table_news . ".news_date_publish < '" . $news_date_publish . "'")
                ->groupby('news_id')
                ->limit(0, 2)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    public function index_sticky($news_id) {
        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`')
                ->table($this->table_news)
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_id = '" . $news_id . "'  AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->get();
        return isset($rows[0]) ? $rows[0] : array();
    }

    public function index_stream_news($news_id, $limit) {
        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_tag_news . '.`tag_news_tag_id`')
                ->table($this->table_news)
                ->join($this->table_tag_news, $this->table_news . '.news_id=' . $this->table_tag_news . '.tag_news_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_id NOT IN (" . $news_id . ")  AND " .
                        $this->table_news . ".news_editor_pick = '1' AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->groupby('news_id')
                ->limit(0, $limit)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    public function index_just_update($news_id, $limit) {
        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_tag_news . '.`tag_news_tag_id`')
                ->table($this->table_news)
                ->join($this->table_tag_news, $this->table_news . '.news_id=' . $this->table_tag_news . '.tag_news_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_id NOT IN (" . $news_id . ")  AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->groupby('news_id')
                ->limit(0, $limit)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    public function category_box_big($category) {
        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news . '.`news_synopsis`'
                        /*. $this->table_news_rubrics . '.`news_rubrics_rubrics_common`'*/)
                ->table($this->table_news)
//                ->join($this->table_news_rubrics, $this->table_news . '.news_id=' . $this->table_news_rubrics . '.news_rubrics_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_date_publish < NOW() AND ".
                        $this->table_news . ".news_category = '[\"$category\"]'"/*AND " .
                        $this->table_news_rubrics . ".news_rubrics_rubrics_common = '" . $category . "'"*/)
                ->limit(0, 1)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return isset($rows[0]) ? $rows[0] : array();
    }

    public function category_box_small($category, $offset, $limit, $exclude) {
        if ($offset == -1) {
            $offset_limit = 0;
        } else {
            $offset_limit = $offset;
        }

        $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news . '.`news_synopsis`')
                ->table($this->table_news)
//                ->join($this->table_news_rubrics, $this->table_news . '.news_id=' . $this->table_news_rubrics . '.news_rubrics_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_category = '[\"$category\"]' AND " .
//                        $this->table_news_rubrics . ".`news_rubrics_rubrics_common` = '" . $category . "' AND " .
//                        $this->table_news . ".news_date_publish < '" . $news_date_publish . "'  AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->groupby('news_id')
                ->limit($offset_limit, $limit)
                ->orderby(array("news_date_publish" => "DESC"));
        if ($exclude)
            $this->where('news_id not in ('.implode($exclude).')');
        $rows = $this->get();
        return $rows;
    }

    public function category_box_small_second($category, $offset, $limit, $news_date_publish) {
        if ($offset == -1) {
            $offset_limit = 0;
        } else {
            $offset_limit = $offset;
        }

        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news . '.`news_synopsis`,'
                        . $this->table_news_rubrics . '.`news_rubrics_rubrics_common`')
                ->table($this->table_news)
                ->join($this->table_news_rubrics, $this->table_news . '.news_id=' . $this->table_news_rubrics . '.news_rubrics_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news_rubrics . ".`news_rubrics_rubrics_common` = '" . $category . "' AND " .
                        $this->table_news . ".news_date_publish < '" . $news_date_publish . "'")
                ->groupby('news_id')
                ->limit(0, $limit)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    public function category_total_box_small($category, $exclude) {

        $this->table($this->table_news)
                ->select('*')
                ->table($this->table_news)
//                ->join($this->table_news_rubrics, $this->table_news . '.news_id=' . $this->table_news_rubrics . '.news_rubrics_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
//                        $this->table_news_rubrics . ".`news_rubrics_rubrics_common` = '" . $category . "' AND " .
                        $this->table_news . ".news_date_publish < NOW() AND ".
                        $this->table_news . ".news_category = '[\"$category\"]'");
        if ($exclude)
            $this->where('news_id not in ('.implode($exclude).')');
            
        $sql = $this->groupby('news_id')
                ->query()
                ->numrow();
        return $sql;
    }

    //BRILIO 2.0
    //EDITOR PICKS
    public function index_editor_picks($news_id, $limit) {
        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_tag_news . '.`tag_news_tag_id`')
                ->table($this->table_news)
                ->join($this->table_tag_news, $this->table_news . '.news_id=' . $this->table_tag_news . '.tag_news_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_id NOT IN (" . $news_id . ")  AND " .
                        $this->table_news . ".news_editor_pick = '1' AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->groupby('news_id')
                ->limit($limit)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    //JUST UPADATED
    //MUST READ STORIES
    public function index_must_read_stories($not_in, $news_id, $offset, $limit) {
        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_tag_news . '.`tag_news_tag_id`')
                ->table($this->table_news)
                ->join($this->table_tag_news, $this->table_news . '.news_id=' . $this->table_tag_news . '.tag_news_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_id NOT IN (" . $not_in . ")  AND " .
                        $this->table_news . ".news_id < '" . $news_id . "'  AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->groupby('news_id')
                ->limit($offset, $limit)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    //CATEGORY BOX BESAR 
    //CATEGORY BOX KECIL
    //CATEGORY EDITOR PICKS
    public function category_editor_picks($category, $limit) {
        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news_rubrics . '.`news_rubrics_rubrics_common`,'
                        . $this->table_tag_news . '.`tag_news_tags`,'
                        . $this->table_tags . '.`tag`,'
                        . $this->table_tags . '.`tag_url`')
                ->table($this->table_news)
                ->join($this->table_news_rubrics, $this->table_news . '.news_id=' . $this->table_news_rubrics . '.news_rubrics_news_id')
                ->join($this->table_tag_news, $this->table_news . '.news_id=' . $this->table_tag_news . '.tag_news_news_id')
                ->join($this->table_tags, $this->table_tag_news . '.tag_news_tag_id =' . $this->table_tags . '.id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news_rubrics . ".`news_rubrics_rubrics_common` = '" . $category . "' AND " .
                        $this->table_news . ".news_editor_pick = '1' AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->groupby('news_id')
                ->limit($limit)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    //JUST UPADATED
    public function widget_just_update($limit) {
        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_tag_news . '.`tag_news_tag_id`')
                ->table($this->table_news)
                ->join($this->table_tag_news, $this->table_news . '.news_id=' . $this->table_tag_news . '.tag_news_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->groupby('news_id')
                ->limit($limit)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    //TAG EDITOR PICKS
    public function tag_editor_picks($limit) {
        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news_rubrics . '.`news_rubrics_rubrics_common`,'
                        . $this->table_tag_news . '.`tag_news_tags`,'
                        . $this->table_tags . '.`tag`,'
                        . $this->table_tags . '.`tag_url`')
                ->table($this->table_news)
                ->join($this->table_news_rubrics, $this->table_news . '.news_id=' . $this->table_news_rubrics . '.news_rubrics_news_id')
                ->join($this->table_tag_news, $this->table_news . '.news_id=' . $this->table_tag_news . '.tag_news_news_id')
                ->join($this->table_tags, $this->table_tag_news . '.tag_news_tag_id =' . $this->table_tags . '.id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_editor_pick = '1' AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->groupby('news_id')
                ->limit($limit)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    public function total_news_per_tags($tags, $news_id) {

        $sql = $this->table($this->table_news)
                ->select('*')
                ->where("news_domain_id = '" . $this->domain_id . "' AND news_type = '0' AND news_level = '1'")
                //->where("news_type = '0' AND news_level != '9'")
                ->query()
                ->numrow();
        return $sql;
    }

    //FUNCTION NEWS PERTANGGAL
    public function get_news_per_date($tanggal, $news_id) {
        $tanggal_sekarang = date("Y-m-d");
        $satuminggu = strtotime('-7 day', strtotime($tanggal_sekarang));
        $satuminggu = date('Y-m-d', $satuminggu);

        if ($tanggal == 'today' || $tanggal == '') {
            $filter_tanggal = ' AND ' . $this->table_news . '.`news_date_publish` BETWEEN "' . $tanggal_sekarang . ' 00:00:00" AND "' . $tanggal_sekarang . ' 23:59:59"';
        } elseif ($tanggal == 'week') {
            $filter_tanggal = ' AND ' . $this->table_news . '.`news_date_publish` BETWEEN "' . $satuminggu . ' 00:00:00" AND "' . $tanggal_sekarang . ' 23:59:59"  AND ' . $this->table_news . '.news_date_publish < NOW()';
        } else {
            $filter_tanggal = " AND DATE(" . $this->table_news . ".`news_date_publish`) = '" . $tanggal . "'";
        }

        $rows = $this->select($this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_synopsis`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_tag_news . '.`tag_news_tag_id`')
                ->table($this->table_jsview)
                ->join($this->table_news, $this->table_jsview . '.jsview_news_id=' . $this->table_news . '.news_id')
                ->join($this->table_tag_news, $this->table_news . '.news_id=' . $this->table_tag_news . '.tag_news_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_id != '" . $news_id . "'  AND " .
                        $this->table_news . ".news_level = '1'" . $filter_tanggal . " AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->groupby('news_id')
                ->limit(0, 19)
                ->orderby(array("jsview_counter" => "DESC"))
                ->get();
        return $rows;
    }

    function get_read_news($keyword) {
        $sql = $this->select(
                        $this->table_news . '.`news_id`,' .
                        $this->table_news . '.`news_url`,' .
                        $this->table_news . '.`news_title`,' .
                        $this->table_news . '.`news_content`,' .
                        $this->table_news . '.`news_date_publish`,' .
                        $this->table_news . '.`news_category`,' .
                        $this->table_news . '.`news_entry`,' .
                        $this->table_news . '.`news_synopsis`,' .
                        $this->table_news . '.`news_reporter`,' .
                        $this->table_news . '.`news_editor`,' .
                        $this->table_news . '.`news_image`,' .
                        $this->table_news . '.`news_image_thumbnail`,' .
                        $this->table_news . '.`news_image_potrait`,' .
                        $this->table_news . '.`news_imageinfo`,' .
                        $this->table_news . '.`news_sensitive`')
                ->table($this->table_news)
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_type = '0' AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_url = '$keyword'  AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->create_query();
        $row = $this->query($sql)->fetchrow();
        return isset($row[0]) ? $row[0] : array();
    }

    function get_more_news($dont_miss_it_id, $news_id, $news_entry, $limit) {
        $rows = $this->select($this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_tag_news . '.`tag_news_tag_id`')
                ->table($this->table_news)
                ->join($this->table_tag_news, $this->table_news . '.news_id=' . $this->table_tag_news . '.tag_news_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_id NOT IN (" . $dont_miss_it_id . "," . $news_id . ")  AND " .
                        $this->table_news . ".news_date_publish < '" . $news_entry . "'")
                ->groupby('news_id')
                ->limit(0, $limit)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    function get_dont_miss_it($news_date_publish, $type) {

        $rows = $this->select($this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_entry`')
                ->table($this->table_news)
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_type = '$type' AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_date_publish < '" . $news_date_publish . "' AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->limit(0, 1)
                ->orderby(array("news_date_publish" => "DESC"))
                ->create_query();
        $row = $this->query($rows)->fetchrow();
        return isset($row[0]) ? $row[0] : array();
    }

    function get_dont_miss_it_new($category, $type) {

        $rows = $this->select($this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news_rubrics . '.`news_rubrics_rubrics_common`')
                ->table($this->table_news)
                ->join($this->table_news_rubrics, $this->table_news . '.news_id=' . $this->table_news_rubrics . '.news_rubrics_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_type = '$type' AND " .
                        $this->table_news_rubrics . ".news_rubrics_rubrics_common = '$category' AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->limit(0, 1)
                ->orderby(array("news_date_publish" => "DESC"))
                ->create_query();
        $row = $this->query($rows)->fetchrow();
        return isset($row[0]) ? $row[0] : array();
    }

    function get_what_next($news_date_publish, $type) {
        $rows = $this->select($this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_entry`')
                ->table($this->table_news)
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_type = '$type' AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_date_publish > '" . $news_date_publish . "' AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->limit(0, 1)
                ->orderby(array("news_date_publish" => "ASC"))
                ->create_query();
        $row = $this->query($rows)->fetchrow();
        return isset($row[0]) ? $row[0] : array();
    }

    function get_what_next_lain($news_id, $type) {
        $rows = $this->select($this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news_rubrics . '.`news_rubrics_rubrics_common`')
                ->table($this->table_news)
                ->join($this->table_news_rubrics, $this->table_news . '.news_id=' . $this->table_news_rubrics . '.news_rubrics_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_type = '$type' AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_id != '" . $news_id . "' AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->limit(0, 1)
                ->orderby(array("news_date_publish" => "ASC"))
                ->create_query();
        $row = $this->query($rows)->fetchrow();
        return isset($row[0]) ? $row[0] : array();
    }

    function get_what_next_new($category, $type) {
        $rows = $this->select($this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news_rubrics . '.`news_rubrics_rubrics_common`')
                ->table($this->table_news)
                ->join($this->table_news_rubrics, $this->table_news . '.news_id=' . $this->table_news_rubrics . '.news_rubrics_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_type = '$type' AND " .
                        $this->table_news_rubrics . ".news_rubrics_rubrics_common = '$category' AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->limit(0, 1)
                ->orderby(array("news_date_publish" => "DESC"))
                ->create_query();
        $row = $this->query($rows)->fetchrow();
        return isset($row[0]) ? $row[0] : array();
    }

    function get_news_last_update() {
        $rows = $this->select($this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_category`')
                ->table($this->table_news)
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_type = '0' AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->limit(0, 1)
                ->orderby(array("news_date_publish" => "DESC"))
                ->create_query();
        $row = $this->query($rows)->fetchrow();
        return isset($row[0]) ? $row[0] : array();
    }

    //NEWS MOST LIKE -> POPULAR
    public function get_news_most_like($category, $tanggal) {
        $tanggal_sekarang = date("Y-m-d");
        $satuminggu = strtotime('-7 day', strtotime($tanggal_sekarang));
        $satuminggu = date('Y-m-d', $satuminggu);

        if ($tanggal == 'today' || $tanggal == '') {
            $filter_tanggal = " AND DATE(" . $this->table_news . ".`news_date_publish`) = DATE(NOW())";
        } elseif ($tanggal == 'week') {
            $filter_tanggal = ' AND ' . $this->table_news . '.`news_date_publish` BETWEEN "' . $satuminggu . ' 00:00:00" AND "' . $tanggal_sekarang . ' 23:59:59"';
        } else {
            $filter_tanggal = " AND DATE(" . $this->table_news . ".`news_date_publish`) = '" . $tanggal . "'";
        }

        if ($category == 'most-liked') {
            $filter_order = 'ORDER BY fbinfo_news_id DESC';
        } elseif ($category == 'most-commented') {
            $filter_order = 'ORDER BY fbinfo_comment DESC';
        } elseif ($category == 'most-shared') {
            $filter_order = 'ORDER BY fbinfo_share DESC';
        } else {
            $filter_order = 'ORDER BY fbinfo_news_id DESC';
        }


        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_synopsis`,'
                        . $this->table_fbinfo . '.`fbinfo_news_id`')
                ->table($this->table_fbinfo)
                ->join($this->table_news, $this->table_fbinfo . '.fbinfo_news_id=' . $this->table_news . '.news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND "
                        . $this->table_news . ".news_type = '0' AND "
                        . $this->table_news . ".news_level = '1'"
                        . $filter_tanggal)
                ->limit(0, 20)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    //GET READ PHOTO
    function get_read_photo($keyword) {
        $sql = $this->select(
                        $this->table_news . '.`news_id`,' .
                        $this->table_news . '.`news_url`,' .
                        $this->table_news . '.`news_title`,' .
                        $this->table_news . '.`news_content`,' .
                        $this->table_news . '.`news_date_publish`,' .
                        $this->table_news . '.`news_entry`,' .
                        $this->table_news . '.`news_synopsis`,' .
                        $this->table_news . '.`news_reporter`,' .
                        $this->table_news . '.`news_editor`,' .
                        $this->table_news . '.`news_image`,' .
                        $this->table_news . '.`news_image_thumbnail`,' .
                        $this->table_news . '.`news_image_potrait`,' .
                        $this->table_news . '.`news_imageinfo`,' .
                        $this->table_news . '.`news_sensitive`,' .
                        $this->table_news . '.`news_category`')
                ->table($this->table_news)
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_type = '1' AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_url = '$keyword'  AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->create_query();
        $row = $this->query($sql)->fetchrow();
        return isset($row[0]) ? $row[0] : array();
    }

    public function get_photos_detail($news_id) {
        $sql = $this->select(
                        $this->table_news . '.`news_title`,' .
                        $this->table_news . '.`news_content`,' .
                        $this->table_news . '.`news_date_publish`,' .
                        $this->table_news . '.`news_entry`,' .
                        $this->table_news . '.`news_synopsis`,' .
                        $this->table_news . '.`news_reporter`,' .
                        $this->table_news . '.`news_editor`,' .
                        $this->table_news . '.`news_id`,' .
                        $this->table_news . '.`news_image`,' .
                        $this->table_news . '.`news_image_thumbnail`,' .
                        $this->table_news . '.`news_image_potrait`,' .
                        $this->table_news . '.`news_url`,' .
                        $this->table_news . '.`news_imageinfo`,' .
                        $this->table_news . '.`news_sensitive`,' .
                        $this->table_news . '.`news_category`')
                ->table($this->table_news)
                ->where($this->table_news . ".news_domain_id = '" .
                        $this->domain_id . "'  AND " .
                        $this->table_news . ".news_type = '1' AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_id < '$news_id'  AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->limit(0, 6)
                ->get();
        return $sql;
    }

    public function get_more_news_detail($news_id, $news_date) {
        $sql = $this->select(
                        $this->table_news . '.`news_title`,' .
                        $this->table_news . '.`news_date_publish`,' .
                        $this->table_news . '.`news_entry`,' .
                        $this->table_news . '.`news_type`,' .
                        $this->table_news . '.`news_id`,' .
                        $this->table_news . '.`news_image`,' .
                        $this->table_news . '.`news_image_thumbnail`,' .
                        $this->table_news . '.`news_image_potrait`,' .
                        $this->table_news . '.`news_url`,' .
                        $this->table_news . '.`news_category`')
                ->table($this->table_news)
                ->where($this->table_news . ".news_domain_id = '" .
                        $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_id != '$news_id'  AND " .
                        $this->table_news . ".news_date_publish < '$news_date'")
                ->limit(0, 20)
                ->get();
        return $sql;
    }

    //GET READ Video
    function get_read_video($keyword) {
        $sql = $this->select(
                        $this->table_news . '.`news_id`,' .
                        $this->table_news . '.`news_url`,' .
                        $this->table_news . '.`news_title`,' .
                        $this->table_news . '.`news_content`,' .
                        $this->table_news . '.`news_date_publish`,' .
                        $this->table_news . '.`news_entry`,' .
                        $this->table_news . '.`news_synopsis`,' .
                        $this->table_news . '.`news_reporter`,' .
                        $this->table_news . '.`news_editor`,' .
                        $this->table_news . '.`news_image`,' .
                        $this->table_news . '.`news_image_thumbnail`,' .
                        $this->table_news . '.`news_image_potrait`,' .
                        $this->table_news . '.`news_imageinfo`,' .
                        $this->table_news . '.`news_sensitive`,' .
                        $this->table_news . '.`news_category`')
                ->table($this->table_news)
                ->where($this->table_news . ".news_domain_id = '" .
                        $this->domain_id . "'  AND " .
                        $this->table_news . ".news_type = '2' AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_url = '$keyword'  AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->create_query();
        $row = $this->query($sql)->fetchrow();
        return isset($row[0]) ? $row[0] : array();
    }

    public function get_video_detail($news_id) {
        $sql = $this->select(
                        $this->table_news . '.`news_title`,' .
                        $this->table_news . '.`news_content`,' .
                        $this->table_news . '.`news_date_publish`,' .
                        $this->table_news . '.`news_entry`,' .
                        $this->table_news . '.`news_synopsis`,' .
                        $this->table_news . '.`news_reporter`,' .
                        $this->table_news . '.`news_editor`,' .
                        $this->table_news . '.`news_id`,' .
                        $this->table_news . '.`news_image`,' .
                        $this->table_news . '.`news_image_thumbnail`,' .
                        $this->table_news . '.`news_image_potrait`,' .
                        $this->table_news . '.`news_url`,' .
                        $this->table_news . '.`news_imageinfo`,' .
                        $this->table_news . '.`news_sensitive`,' .
                        $this->table_news . '.`news_category`')
                ->table($this->table_news)
                ->where($this->table_news . ".news_domain_id = '" .
                        $this->domain_id . "'  AND " .
                        $this->table_news . ".news_type = '2' AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_id < '$news_id'  AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->limit(0, 6)
                ->get();
        return $sql;
    }

    function get_latest() {
        $rows = $this->select($this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_content`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_synopsis`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news_rubrics . '.`news_rubrics_rubrics_common`')
                ->table($this->table_news)
                ->join($this->table_news_rubrics, $this->table_news . '.news_id=' . $this->table_news_rubrics . '.news_rubrics_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->limit(0, 15)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    //POPULAR -> HEADLINE
    function popular_headline($tanggal) {
        $tanggal_sekarang = date("Y-m-d");
        $satuminggu = strtotime('-7 day', strtotime($tanggal_sekarang));
        $satuminggu = date('Y-m-d', $satuminggu);

        if ($tanggal == 'today' || $tanggal == '') {
            $filter_tanggal = ' AND ' . $this->table_news . '.`news_date_publish` BETWEEN "' . $tanggal_sekarang . ' 00:00:00" AND "' . $tanggal_sekarang . ' 23:59:59"';
        } elseif ($tanggal == 'week') {
            $filter_tanggal = ' AND ' . $this->table_news . '.`news_date_publish` BETWEEN "' . $satuminggu . ' 00:00:00" AND "' . $tanggal_sekarang . ' 23:59:59"';
        } else {
            $filter_tanggal = " AND DATE(" . $this->table_news . ".`news_date_publish`) = '" . $tanggal . "'";
        }

        $rows = $this->select($this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_synopsis`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_tag_news . '.`tag_news_tag_id`')
                ->table($this->table_jsview)
                ->join($this->table_news, $this->table_jsview . '.jsview_news_id=' . $this->table_news . '.news_id')
                ->join($this->table_tag_news, $this->table_news . '.news_id=' . $this->table_tag_news . '.tag_news_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1'" . $filter_tanggal . " AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->limit(0, 1)
                ->orderby(array("jsview_counter" => "DESC"))
                ->get();
        return isset($rows[0]) ? $rows[0] : array();
    }

    //MOST -> HEADLINE
    function most_headline($category, $tanggal) {

        $tanggal_sekarang = date("Y-m-d");
        $satuminggu = strtotime('-7 day', strtotime($tanggal_sekarang));
        $satuminggu = date('Y-m-d', $satuminggu);

        if ($tanggal == 'today' || $tanggal == '') {
            $filter_tanggal = ' AND ' . $this->table_jsview . '.`jsview_last_date` BETWEEN "' . $satuminggu . ' 00:00:00" AND "' . $tanggal_sekarang . ' 23:59:59"';
        } elseif ($tanggal == 'week') {
            $filter_tanggal = ' AND ' . $this->table_news . '.`news_date_publish` BETWEEN "' . $satuminggu . ' 00:00:00" AND "' . $tanggal_sekarang . ' 23:59:59"';
        } else {
            $filter_tanggal = " AND DATE(" . $this->table_news . ".`news_date_publish`) = '" . $tanggal . "'";
        }

        if ($category == 'most-liked') {
            $filter_order = 'fbinfo_news_id';
        } elseif ($category == 'most-commented') {
            $filter_order = 'fbinfo_comment';
        } elseif ($category == 'most-shared') {
            $filter_order = 'fbinfo_share';
        } else {
            $filter_order = 'fbinfo_news_id';
        }


        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_synopsis`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_fbinfo . '.`fbinfo_news_id`,'
                        . $this->table_fbinfo . '.`fbinfo_comment`,'
                        . $this->table_fbinfo . '.`fbinfo_share`,'
                        . $this->table_tag_news . '.`tag_news_tag_id`')
                ->table($this->table_fbinfo)
                ->join($this->table_news, $this->table_fbinfo . '.fbinfo_news_id=' . $this->table_news . '.news_id')
                ->join($this->table_tag_news, $this->table_news . '.news_id=' . $this->table_tag_news . '.tag_news_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND "
                        . $this->table_news . ".news_level = '1'"
                        . $filter_tanggal)
                ->limit(0, 1)
                ->orderby(array("$filter_order" => "DESC"))
                ->get();
        return isset($rows[0]) ? $rows[0] : array();
    }

    //MOST -> LIST-> NEWS_ID
    public function most_list($category, $tanggal, $news_id) {
        $tanggal_sekarang = date("Y-m-d");
        $satuminggu = strtotime('-7 day', strtotime($tanggal_sekarang));
        $satuminggu = date('Y-m-d', $satuminggu);

        if ($tanggal == 'today' || $tanggal == '') {
            $filter_tanggal = ' AND ' . $this->table_jsview . '.`jsview_last_date` BETWEEN "' . $satuminggu . ' 00:00:00" AND "' . $tanggal_sekarang . ' 23:59:59"';
        } elseif ($tanggal == 'week') {
            $filter_tanggal = ' AND ' . $this->table_news . '.`news_date_publish` BETWEEN "' . $satuminggu . ' 00:00:00" AND "' . $tanggal_sekarang . ' 23:59:59"';
        } else {
            $filter_tanggal = " AND DATE(" . $this->table_news . ".`news_date_publish`) = '" . $tanggal . "'";
        }

        if ($category == 'most-liked') {
            $filter_order = 'fbinfo_news_id';
        } elseif ($category == 'most-commented') {
            $filter_order = 'fbinfo_comment';
        } elseif ($category == 'most-shared') {
            $filter_order = 'fbinfo_share';
        } else {
            $filter_order = 'fbinfo_news_id';
        }


        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_category`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_synopsis`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_tag_news . '.`tag_news_tag_id`')
                ->table($this->table_fbinfo)
                ->join($this->table_news, $this->table_fbinfo . '.fbinfo_news_id=' . $this->table_news . '.news_id')
                ->join($this->table_tag_news, $this->table_news . '.news_id=' . $this->table_tag_news . '.tag_news_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_id != '" . $news_id . "'  AND " .
                        $this->table_news . ".news_level = '1'" .
                        $filter_tanggal)
                ->groupby('news_id')
                ->limit(0, 19)
                ->orderby(array("$filter_order" => "DESC"))
                ->get();
        return $rows;
    }

    //RELATED NEWS
    public function get_related($news_id, $category, $type_news) {
        $rows = $this->select($this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_synopsis`,'
                        . $this->table_news_rubrics . '.`news_rubrics_rubrics_common`,'
                        . $this->table_tag_news . '.`tag_news_tags`')
                ->table($this->table_news)
                ->join($this->table_news_rubrics, $this->table_news . '.news_id=' . $this->table_news_rubrics . '.news_rubrics_news_id')
                ->join($this->table_tag_news, $this->table_news . '.news_id=' . $this->table_tag_news . '.tag_news_news_id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news_rubrics . ".`news_rubrics_rubrics_common` = '" . $category . "' AND " .
                        $this->table_news . ".news_id != '" . $news_id . "'  AND " .
                        $this->table_news . ".news_type = '" . $type_news . "' AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->groupby('news_id')
                ->limit(0, 3)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    public function feed($offset, $limit) {
        $rows = $this->select($this->table_news . '.`news_title`,'
                        . $this->table_news . '.`news_id`,'
                        . $this->table_news . '.`news_image`,'
                        . $this->table_news . '.`news_image_thumbnail`,'
                        . $this->table_news . '.`news_image_potrait`,'
                        . $this->table_news . '.`news_entry`,'
                        . $this->table_news . '.`news_date_publish`,'
                        . $this->table_news . '.`news_url`,'
                        . $this->table_news . '.`news_type`,'
                        . $this->table_news . '.`news_synopsis`,'
                        . $this->table_news_rubrics . '.`news_rubrics_rubrics_common`,'
                        . $this->table_tag_news . '.`tag_news_tags`,'
                        . $this->table_tags . '.`tag`,'
                        . $this->table_tags . '.`tag_url`')
                ->table($this->table_news)
                ->join($this->table_news_rubrics, $this->table_news . '.news_id=' . $this->table_news_rubrics . '.news_rubrics_news_id')
                ->join($this->table_tag_news, $this->table_news . '.news_id=' . $this->table_tag_news . '.tag_news_news_id')
                ->join($this->table_tags, $this->table_tag_news . '.tag_news_tag_id =' . $this->table_tags . '.id')
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->groupby('news_id')
                ->limit($offset, $limit)
                ->orderby(array("news_date_publish" => "DESC"))
                ->get();
        return $rows;
    }

    //added by dimas
    //get all news detail from news
    function get_all_news_detail($news_keyword) {
         $sql = $this->select('*')
                ->table($this->table_news)
                ->where($this->table_news . ".news_domain_id = '" . $this->domain_id . "'  AND " .
                        $this->table_news . ".news_type = '0' AND " .
                        $this->table_news . ".news_level = '1' AND " .
                        $this->table_news . ".news_url = '$news_keyword'  AND " .
                        $this->table_news . ".news_date_publish < NOW()")
                ->create_query();
        $row = $this->query($sql)->fetchrow();
        return isset($row[0]) ? $row[0] : array();
    }

}
