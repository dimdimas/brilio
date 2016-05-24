<?php
class Featured extends Eloquent{

    public $table = 'featured_content';

    public function featured_news(){
        return $this->hasMany('News','news_id', 'news');
    }
}
?>
