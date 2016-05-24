<?php
class Tag extends Eloquent{

	public $table = 'tags';

        public function tag()
    {
        return $this->belongsToMany('News');
    }

    public function tag_to_news()
    {
        return $this->belongsTo('News', 'tag_url', 'tag_url');
    }

}
?>