<?php

class Rubric_model extends Model {

    public function __construct() {
        parent::__construct();

        mysql_query("SET time_zone='+07:00';");

        $this->prefix = $this->db_config['prefix'];
        $this->domain_id = $this->db_config['domain_id'];
        $this->table_rubrics = $this->prefix . 'rubrics';
    }

    function get_category($category_id) {
        $rows = $this->select($this->table_rubrics . '.`rubrics_id`,'
                .$this->table_rubrics . '.`rubrics_name`,'
                .$this->table_rubrics . '.`rubrics_url`,'
                .$this->table_rubrics . '.`rubrics_common`')
                ->table($this->table_rubrics)
                ->where($this->table_rubrics . ".rubrics_id = '$category_id'")
                ->get();
        return isset($rows[0]) ? $rows[0] : array();
    }
    
    function get_all_category() {
        $rows = $this->table($this->table_rubrics)
                ->where('rubrics_domain_id', $this->domain_id)
                ->where('rubrics_invalid', '0')
                ->orderBy('rubrics_parent', 'asc')
                ->get();
//        echo $rows;die();
        return $rows;
    }

}
