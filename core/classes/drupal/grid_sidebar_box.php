<?php

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\node\Entity\Node;

class grid_sidebar_box extends grid_box
{
	public function type()
	{
		return "sidebar";
	}
	
	public function __construct()
	{
		$this->content=new Stdclass();
		$this->content->nodeid='';
	}
	
	public function build($editmode)
	{
		if($this->content->nodeid!='')
		{
			$gridid=grid_get_grid_by_nid($this->content->nodeid);
			if($gridid!==FALSE)
			{
				$grid=$this->storage->loadGrid($gridid,FALSE);
				return $grid->render($editmode);
			}
			else
			{
				return t("Sidebar is lost.");
			}
		}
		else
		{
			return t("Sidebar not found or none set");
		}
	}

	public function contentStructure()
	{
		$content = array(
			array(
				'key'=>'nodeid',
				'label'=>t('Sidebar'),
				'type'=>'autocomplete-with-links',
				'url'=>'/node/%/grid',
				'linktext'=>t('Edit Sidebar'),
				'emptyurl'=>'/node/add/'.\Drupal::config("grid.settings")->get("sidebar_content"),
				'emptylinktext'=>t('Create Sidebar'),
			),
			array(
				'key'=>'html',
				'type'=>'hidden',
			),
		);
		if($this->content->nodeid!='')
		{
			/** @var Node $node */
			$node=Node::load($this->content->nodeid);
			if($node!=NULL)
			{
				$content[0]['valuekey']=$node->getTitle();
			}
		}
		return $content;
	}

	public function performElementSearch($key,$query)
	{
		if($key!='nodeid')
		{
			return array(array('key'=>-1,'value'=>'invalid key'));
		}
		$results=array();
		/** @var QueryInterface $dbquery */
		$dbquery=\drupal::entityQuery('node');
		$sidebar_content_type = \Drupal::config("grid.settings")->get("sidebar_content");
		$dbquery->condition('bundle', $sidebar_content_type)
		      ->condition('title','%'.$query.'%','LIKE')
		      ->sort('created','DESC');
		$result=$dbquery->execute();
		if(!empty($result))
		{
			$nids=array_keys($result);
			$nodes=Node::loadMultiple($nids);
			foreach($nodes as $node)
			{
				$results[]=array('key'=>$node->id(),'value'=>$node->getTitle());
			}
		}
		return $results;
	}
	
	public function getElementValue($path,$id)
	{
		if($path!='nodeid')
			return 'WRONG PATH: '.$path;
		$node=Node::load($id);
		return $node->getTitle();
	}
}