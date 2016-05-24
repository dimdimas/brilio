<?php
class TagToday extends Eloquent{

	public $table = 'today_tag';

		public function get_tag()
    {
        return $this->hasOne('Tag',  'id', 'id_tag');
    	// return $this->belongsTo('Tag');
    }

		public function sponsor_tag()
		{
				return $this->hasMany('SponsorTag', 'id', 'id_tag');
		}

		public function tag_data()
    {
        return $this->hasMany('Tag','id', 'id_tag');
    }

}
?>
