<?php
class TagNews extends Eloquent{

	public $table = 'tag_news';

	public function tag()
    {
        return $this->belongsTo('Tag', 'tag_news_tag_id', 'id', 'tag_url');
    }

    public function news()
    {
        return $this->belongsTo('News', 'tag_news_news_id', 'news_id');
    }

}	
?>