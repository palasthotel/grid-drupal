<?php

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\node\Entity\Node;

class grid_node_box extends grid_box {

	public function type()
	{
		return 'node';
	}

	public function build($editmode) {
		/** @var Node $node */
		$node=Node::load($this->content->nid);
		if($node==FALSE)
		{
		  watchdog_exception("Grid", new Exception("Node Lost"), "Node Lost, Node-ID:".$this->content->nid." on Grid-ID:".$this->grid->gridid, array(), "warning");
			return (string)t("Node is lost");
		}
		if($editmode)
		{
		  if($node->hasTranslation(\Drupal::languageManager()->getCurrentLanguage()->getId())) {
		    $node=$node->getTranslation(\Drupal::languageManager()->getCurrentLanguage()->getId());
      }
			return $node->getType().': '.$node->getTitle().' ('.date("Y-m-d h:i:s",$node->getCreatedTime()).")";
		}
		else
		{
			$view_modes=grid_viewmodes();

			// print_r($view_modes);

			if (!array_key_exists($this->content->viewmode, $view_modes)){
			    $this->content->viewmode = grid_default_viewmode();
			}
			/** @var \Drupal\node\NodeAccessControlHandler $accesscontrolhandler */
            if($node->hasTranslation(\Drupal::languageManager()->getCurrentLanguage()->getId())) {
                $node=$node->getTranslation(\Drupal::languageManager()->getCurrentLanguage()->getId());
            }
			$accesscontrolhandler=\Drupal::entityTypeManager()->getAccessControlHandler('node');
			if($accesscontrolhandler->access($node,"view") )
			{
				//$renderarray=node_view($node,$this->content->viewmode);
        $builder = \Drupal::entityTypeManager()->getViewBuilder('node');
        $renderarray= $builder->view($node,$this->content->viewmode);
				return (string)\Drupal::service("renderer")->render($renderarray);
       // return $renderarray;

			}
			else
				return "";
		}
	}

	public function isMetaType() {
		return TRUE;
	}

	public function metaTitle() {
		return "Contents";
	}

	public function metaSearchCriteria() {
		return array("title");
	}

	public function metaSearch($criteria,$search) {
		$results=array();
		/** @var QueryInterface $query */
		$query=\Drupal::entityQuery('node');
		$words=explode(" ", $search);
		$query->sort('created','DESC');
		if($search!='')
		{
			foreach($words as $word)
			{
				$query->condition('title','%'.$word.'%','LIKE');
			}
		}
		$query->range(0,50);
		$result=$query->execute();
		if(!empty($result))
		{
			/** @var Node[] $nodes */
			$nodes=Node::loadMultiple($result);
			foreach($nodes as $node)
			{
				$type=$node->getType();
				$box=new grid_node_box();
				$box->storage=$this->storage;
				$box->content=new StdClass();
				$box->content->nid=$node->id();
				$box->content->viewmode=grid_default_viewmode();
				$results[]=$box;
			}
		}
		return $results;
	}
	
	public function contentStructure () {
		$view_modes=grid_viewmodes();
		$modes=array();
		$node=NULL;
		if($this->content->nid!="")
		{
			$node=Node::load($this->content->nid);
		}
		foreach($view_modes as $key=>$info)
		{
			if($key=='full')
			{
				// noticegefahr durch nicht immer gesetztes $node Objekt
				if($node!=NULL && !in_array($node->getType(),\Drupal::config("grid.settings")->get("enabled_node_types")))
				{
					$modes[]=array('key'=>$key,'text'=>$info['label']);
				}
			}
			else
			{
				$modes[]=array('key'=>$key,'text'=>$info['label']);
			}
		}
		$params=array(
			array(
				'key'=>'viewmode',
				'type'=>'select',
				'label'=> t('Viewmode'),
				'selections'=>$modes,
			),
			array(
				'key'=>'nid',
				'type'=>'hidden',
			),
		);
		return $params;
	}

}
