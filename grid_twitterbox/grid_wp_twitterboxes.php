<?php

class grid_twitter_box extends grid_static_base_box {
	
	public function __construct()
	{
		$this->content=new Stdclass();
		$this->content->limit=5;
		$this->content->user="";
	}
	
	public function type()
	{
		return 'twitter';
	}
	
	protected function prebuild()
	{
		if($this->content->user=="")
			return "";
		return NULL;
	}
	
	protected function fetch($connection)
	{
		$result=$connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json",array("screen_name"=>$this->content->user));
		return $result;		
	}

	public function build($editmode) {
		if($editmode)
		{
			return "Twitter Box";
		}
		else
		{
			$prebuild=$this->prebuild();
			if($prebuild!=NULL)
				return $prebuild;
			else
			{
				$token=get_option('grid_twitterbox_accesstoken');
				$connection=new TwitterOAuth(get_option('grid_twitterbox_consumer_key',''),get_option('grid_twitterbox_consumer_secret',''),$token['oauth_token'],$token['oauth_token_secret']);
				$result=$this->fetch($connection);
				if(count($result)>$this->content->limit)
				{
					$result=array_slice($result, 0,$this->content->limit);
				}
				ob_start();
				$content=$result;
				if(file_exists($this->storage->templatesPath."/grid_twitterbox.tpl.php"))
				{
					require($this->storage->templatesPath."/grid_twitterbox.tpl.php");
				}
				else
				{
					require("grid_twitterbox.tpl.php");
				}
				$result=ob_get_clean();
				return $result;
			}
		}
	}
	
	public function contentStructure () {
		return array(
			array(
				'key'=>'limit',
				'type'=>'number',
				'label' => 'Anzahl der Einträge'
			),
			array(
				'key'=>'user',
				'type'=>'text',
				'label' => 'User'
			),
		);
	}
	
	public function metaSearch($criteria,$query) {
		if(get_option('grid_twitterbox_consumer_key','')=='' || get_option('grid_twitterbox_consumer_secret','')=='' || get_option('grid_twitterbox_accesstoken','')=='')
			return array();
		return array($this);
	}

}

class grid_twitter_hashtag_box extends grid_twitter_box {
	
	public function __construct()
	{
		$this->content=new Stdclass();
		$this->content->limit=5;
		$this->content->hashtag="";
	}

	public function type()
	{
		return "twitter_hashtag";
	}
	
	public function fetch($connection)
	{
		$output=$connection->get("https://api.twitter.com/1.1/search/tweets.json",array("q"=>$this->content->hashtag));
		if(isset($output->statuses))
			$result=$output->statuses;
		else
			$result=array();
		return $result;
	}
	
	protected function prebuild()
	{
		if($this->content->hashtag=="")
			return "";
		return NULL;
	}
	
	public function build($editmode) {
		if($editmode)
		{
			return "Twitter Hashtag Box";
		}
		else
		{
			return parent::build($editmode);
		}
	}
	
	public function contentStructure () {
		return array(
			array(
				'key'=>'limit',
				'label'=>'Limit',
				'type'=>'number',
			),
			array(
				'key'=>'hashtag',
				'label'=>'Hashtag',
				'type'=>'text',
			),
		);
	}
}