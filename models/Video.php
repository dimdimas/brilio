<?php
class Video extends Eloquent{

    public $table = 'video';
    protected $primaryKey = 'video_news_id';

    function video(){
        return $this->belongsTo('News');
    }

}
?>