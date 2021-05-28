<?php

use Drupal\block\Entity\Block;

class grid_block_box extends grid_box {

	public function type()
	{
		return 'block';
	}

	public function build($editmode) {
		if($editmode)
		{
			/** @var Block $block */
			$block=Block::load($this->content->block_id);
			return t("Block").": ".$block->label();
		}
		else
		{
			/** @var Block $block */
			$block=Block::load($this->content->block_id);
			$entityTypeManager=\Drupal::entityTypeManager();
			$output=$entityTypeManager->getViewBuilder("block")->view($block);
			return (string)\Drupal::service("renderer")->render($output);
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
		$blocks=Block::loadMultiple();
		$results=array();
		foreach($blocks as $idx=>$block)
		{
			if(in_array($block->id(),\Drupal::config("grid.settings")->get("blocks")))
			{
				$info=$block->label();
				if ($info==""){
					$info="~~~~~";
				}
				if($query=='' || strstr($info, $query)!==FALSE)
				{
					$box=new grid_block_box();
					$box->storage=$this->storage;
					$box->content=new StdClass();
					$box->content->block_id=$block->id();
					$results[]=$box;
				}
			}
		}
		return $results;
	}

	public function contentStructure () {
		return array(
			array(
				'key'=>'block_id',
				'type'=>'hidden',
			),
		);
	}

}
