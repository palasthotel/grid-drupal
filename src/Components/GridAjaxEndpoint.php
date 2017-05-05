<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 19.08.15
 * Time: 12:57
 */

namespace Drupal\grid\Components;


use Drupal\node\Entity\Node;
use grid_box;

class GridAjaxEndpoint extends \grid_ajaxendpoint
{
    public function Rights()
    {
        $rights=parent::Rights();
        $results=array();
        foreach($rights as $right)
        {
            if(\Drupal::currentUser()->hasPermission("grid: ".$right))
            {
                $results[]=$right;
            }
        }
        return $results;
    }

    public function loadGrid($gridid)
    {
        $return=parent::loadGrid($gridid);
        $nid=grid_get_nid_by_gridid($gridid);
        if(is_numeric($nid))
        {
            /** @var Node $node */
            $node=Node::load($nid);
            $node->save();
            $type=$node->getType();
            if($type==\Drupal::config("grid.settings")->get("sidebar_content"))
            {
                $return['isSidebar']=TRUE;
            }
            else
            {
                $return['isSidebar']=FALSE;
            }
        }
        return $return;
    }

    public function publishDraft($gridid)
    {
        $result=parent::publishDraft($gridid);
        if($result)
        {
            $nid=grid_get_nid_by_gridid($gridid);
            /** @var Node $node */
            $node=Node::load($nid);
            \Drupal\Core\Cache\Cache::invalidateTags($node->getCacheTags());
            \Drupal::moduleHandler()->invokeAll('grid_published',array($nid));
        }
        return $result;
    }
    public function getMetaTypesAndSearchCriteria($grid_id){
        $result=parent::getMetaTypesAndSearchCriteria($grid_id);
        \Drupal::moduleHandler()->alter('grid_metaboxes',$result,$grid_id,grid_get_nid_by_gridid($grid_id));
        return $result;
    }

    public function Search($grid_id,$metatype,$searchstring,$criteria)
    {
        $result=parent::Search($grid_id,$metatype,$searchstring,$criteria);
        \Drupal::moduleHandler()->alter('grid_boxes_search',$result,$grid_id,grid_get_nid_by_gridid($grid_id));
        return $result;
    }

    public function getContainerTypes($grid_id)
    {
        $result=parent::getContainerTypes($grid_id);
        \Drupal::moduleHandler()->alter('grid_containers',$result,$grid_id,grid_get_nid_by_gridid($grid_id));
        return $result;
    }

    public function getReusableContainers($grid_id)
    {
        $result=parent::getReusableContainers($grid_id);
        \Drupal::moduleHandler()->alter('grid_reusable_containers',$result,$grid_id,grid_get_nid_by_gridid($grid_id));
        return $result;
    }

    public function UpdateBox($gridid,$containerid,$slotid,$idx,$boxdata)
    {
        $result=parent::UpdateBox($gridid,$containerid,$slotid,$idx,$boxdata);
        if($result!=FALSE)
        {
            $grid=$this->storage->loadGrid($gridid);
            foreach($grid->container as $container)
            {
                if($container->containerid==$containerid)
                {
                    foreach($container->slots as $slot)
                    {
                        if($slot->slotid==$slotid)
                        {
                            if(isset($slot->boxes[$idx]))
                            {
                                //we found a box.
                                $box=$slot->boxes[$idx];
                                $box=\Drupal::moduleHandler()->invokeAll('grid_persist_box',array($box));
                                if(count($box)>0 && $box[0]!==NULL)
                                {
                                    /** @var grid_box $box */
                                    $box=$box[0];
                                    $slot->boxes[$idx]=$box;
                                    $box->persist();
                                }
                                else
                                {
                                    $box=$slot->boxes[$idx];
                                }
                                return $this->encodeBox($box);
                            }
                            return FALSE;
                        }
                    }
                }
            }
        }
    }

	public function getFileInfo($gridid,$containerid,$slotid,$idx,$path,$fid)
	{
		$grid=$this->storage->loadGrid($gridid);
		foreach($grid->container as $container)
		{
			if($container->containerid==$containerid)
			{
				foreach($container->slots as $slot)
				{
					if($slot->slotid==$slotid)
					{
						/**
						 * @var grid_box $box
						 */
						$box=$slot->boxes[$idx];
						$info = $box->getFileInfo($fid,$path);

						if(FALSE == $info ){
							if(empty($fid)){
								return array(
									"fid" => $fid,
									"src" => "",
								);
							}
							// not overwritten in box, so default file info
							$file= \Drupal\file\Entity\File::load($fid);
							$src = file_create_url($file->getFileUri());
							return array(
								"fid" => $file->id(),
								"src" => $src,
							);
						}

						return $info;
					}
				}
				return "WRONG SLOT";
			}
		}
		return "WRONG CONTAINER";
	}

}
