<?php
class News extends Eloquent{

    public $table = 'news';
    protected $primaryKey = 'news_id';

    public function tags(){
        return $this->hasMany('Tag');
    }

    /**
     * relation many to many table tag
     * @return $this
     */
    public function rubrics(){
        return $this->belongsTo('Rubrics');
    }

    public function Tag()
    {
        return $this->belongsToMany('Tag','TagNews');
    }

    public function tag_news()
    {
        return $this->belongsTo('TagNews');
    }

    public function tag_news_news_id()
    {
        return $this->belongsTo('TagNews', 'tag_news_news_id');
    }

    public function scopePublished($query, $limit = 0){
        if ($limit)
            $query = $query->take($limit);
        return $query->where('news_domain_id', Config::App()->get('domain_id'))
            ->orderBy('news_date_publish','DESC')
            // ->where('news_date_publish','<=', date('Y-m-d H:i:s'))
            ->where('news_level','=','1');
    }

    public function scopePreviewPublished($query, $limit = 0){
        if ($limit)
            $query = $query->take($limit);
        return $query->where('news_domain_id', Config::App()->get('domain_id'))
            ->orderBy('news_date_publish','DESC')
            // ->where('news_date_publish','<=', date('Y-m-d H:i:s'))
            ->where('news_level','=','1');
    }

    public function news_tag_news()
    {
        //return only 1 tag
        return $this->belongsTo('TagNews', 'news_id', 'tag_news_news_id');
    }

   public function video()
   {
       return $this->hasOne('video', 'video_news_id', 'news_id')
           ->where('video_status', 1);
   }

    public function news_tag_list()
    {
        // return all tags in an article
        return $this->hasMany('TagNews','tag_news_news_id', 'news_id');
    }

    public function sponsor_tag()
    {
        return $this->hasMany('SponsorTag', 'id', 'news_sponsorship');
    }

}
?>
