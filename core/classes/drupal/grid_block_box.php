<?php

class grid_block_box extends grid_box {

	public function type()
	{
		return 'block';
	}

	public function build($editmode) {
		if($editmode)
		{
			$blocks=module_invoke($this->content->module,'block_info');
			global $theme_key;
			drupal_alter('block_info',$blocks,$theme_key,$blocks);
			$block=$blocks[$this->content->delta];
			return t("Block").": ".$block['info'];
		}
		else
		{
			$block=module_invoke($this->content->module,'block_view',$this->content->delta);
			if(@is_string($block['content'])) {
				return $block['content'];
			}
			else {
				// #232, "return drupal_render($block);" doesn´t consider block templates
				// @url https://api.drupal.org/comment/44553#comment-44553
				// @author Kim-Christian Meyer <kim.meyer@palasthotel.de>
				$block_load = block_load($this->content->module, $this->content->delta);
				return drupal_render(_block_get_renderable_array(_block_render_blocks(array($block_load))));
			}
		}
	}

	public function isMetaType() {
		return TRUE;
	}

	public function metaTitle() {
		return t("Blocks");
	}

	public function metaSearchCriteria() {
		return array("info");
	}

	public function metaSearch($criteria,$query) {
		global $theme_key;
		$blocks=array();
		$results=array();
		foreach(module_implements('block_info') as $module)
		{
			$module_blocks=module_invoke($module,'block_info');
			$blocks[$module]=$module_blocks;
		}
		drupal_alter('block_info',$blocks,$theme_key,$blocks);
		foreach($blocks as $module=>$modblocks)
		{
			foreach($modblocks as $delta=>$block)
			{
				if(variable_get("grid_block_".$module."_".$delta."_enabled",0))
				{
					$info=$block['info'];
					if ($info==""){
						$info="~~~~~";
					}
					if($query=='' || strstr($info, $query)!==FALSE)
					{
						$box=new grid_block_box();
						$box->content=new StdClass();
						$box->content->module=$module;
						$box->content->delta=$delta;
						$results[]=$box;

					}
				}
			}
		}
		return $results;
	}

	public function contentStructure () {
		return array(
			array(
				'key'=>'module',
				'type'=>'hidden',
			),
			array(
				'key'=>'delta',
				'type'=>'hidden',
			),
		);
	}

}