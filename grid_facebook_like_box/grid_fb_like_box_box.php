<?php

class grid_fb_like_box_box extends grid_static_base_box {
	
	public function type()
	{
		return 'fb_like_box';
	}

	public function __construct()
	{
		$this->content=new Stdclass();
		$this->content->fb_page='';
		$this->content->appid = "";
		$this->content->show_faces = "true";
		$this->content->show_header = "true";
		$this->content->datastream = "false";
    $this->content->language = "de_DE";
		$this->content->colorscheme = "light";
		$this->content->show_border = "true";
		$this->content->force_wall = "false";
	}

	public function build($editmode) {
		if($editmode)
		{
			return t("Facebook Like Box").": <br/>".$this->content->fb_page;
		}
		else
		{
			$fb_page = $this->content->fb_page;
			$appid = "";
			if(isset($this->content->appid)) $appid = $this->content->appid;
			$show_faces = $this->content->show_faces;
			$show_header = $this->content->show_header;
			$datastream = $this->content->datastream;
      if(!isset($this->content->language)) { $this->content->language = "de_DE"; }
      $language = $this->content->language;
			$colorscheme = $this->content->colorscheme;
			$show_border = $this->content->show_border;
			$force_wall = $this->content->force_wall;

			$fb_url = "http://www.facebook.com/";
			$fbs_url = "https://www.facebook.com/";
			if(strpos($fb_page, $fb_url) === false && strpos($fb_page, $fbs_url) === false){
				$fb_page = $fb_url.$fb_page;
			}

			ob_start();
			?>
			<script>
			(function(d, s, id) {
			  if (d.getElementById(id)) return;
			  div = d.createElement(s); 
			  div.id = id;
			  d.body.insertBefore(div, document.body.childNodes[0]);
			}(document, 'div', 'fb-root'));
			(function(d, s, id) {
			  var js = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) return;
			  js = d.createElement(s); js.id = id;
			  js.src = "//connect.facebook.net/<?= $language; ?>/all.js#xfbml=1&appId=<?= $appid; ?>";
			  d.head.insertBefore(js, document.head.childNodes[0])
			}(document, 'script', 'facebook-jssdk'));
			</script>

			<div 
			class="fb-like-box" 
			data-href="<?= $fb_page; ?>" 
			data-colorscheme="<?= $colorscheme; ?>" 
			data-show-faces="<?= $show_faces; ?>" 
			data-header="<?= $show_header; ?>" 
			data-stream="<?= $datastream; ?>" 
			data-show-border="<?= $show_border; ?>"></div>

			<?php
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
	}
	
	public function contentStructure () {
		return array(
			array(
				'key'=>'fb_page',
				'label'=>t('Facebook page'),
				'type'=>'text'
			),
			array(
				'key'=>'appid',
				'label'=>t('Facebook APP Id'),
				'type'=>'text'
			),
			array(
				'key' => 'width',
				'label' => t('Width in pixel (optional, default 300)'),
				'type' => 'number',
			),
			array(
				'key' => 'height',
				'label' => t('Height in pixel(optional, default 556 or 63 without stram and faces)'),
				'type' => 'number',
			),
			array(
				'key' => 'colorscheme',
				'label' => t('Color scheme'),
				'type' => 'select',
				'selections'=>
				array(
					array(
						"key" => "light",
						"text" => t("light"),
					),
					array(
						"key" => "dark",
						"text" => t("dark"),
					),
				),
			),
			array(
				'key' => 'show_faces',
				'label' => t('Faces'),
				'type' => 'select',
				'selections'=>
				array(
					array(
						"key" => "true",
						"text" => t("show"),
					),
					array(
						"key" => "false",
						"text" => t("hide"),
					),
				),
			),
			array(
				'key' => 'show_header',
				'label' => t('Box header'),
				'type' => 'select',
				'selections'=>
				array(
					array(
						"key" => "true",
						"text" => t("show"),
					),
					array(
						"key" => "false",
						"text" => t("hide"),
					),
				),
			),
			array(
				'key' => 'datastream',
				'label' => t('Stream of latest posts'),
				'type' => 'select',
				'selections'=>
				array(
					array(
						"key" => "true",
						"text" => t("show"),
					),
					array(
						"key" => "false",
						"text" => t("hide"),
					),
				),
			),
			array(
				'key' => 'show_border',
				'label' => t('Border of box'),
				'type' => 'select',
				'selections'=>
				array(
					array(
						"key" => "true",
						"text" => t("show"),
					),
					array(
						"key" => "false",
						"text" => t("hide"),
					),
				),
			),
			array(
				'key' => 'force_wall',
				'label' => t('Force "place" Pages'),
				'type' => 'select',
				'selections'=>
				array(
					array(
						"key" => "true",
						"text" => t("On"),
					),
					array(
						"key" => "false",
						"text" => t("Off"),
					),
				),
			),
		);
	}

}
