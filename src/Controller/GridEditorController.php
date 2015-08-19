<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 14.08.15
 * Time: 15:19
 */

namespace Drupal\grid\Controller;


use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\grid\Components\GridSafeString;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

class GridEditorController extends ControllerBase implements AccessInterface
{
    public function editor(RouteMatchInterface $match)
    {
        global $grid_lib;
        $nid=$match->getParameter("node");
        $grid_id=grid_get_grid_by_nid($nid);
        if($grid_id===FALSE)
        {
            return \Drupal::formBuilder()->getForm('\Drupal\grid\Form\BuildGridForm',$nid);
        }
        else
        {

            $css=$grid_lib->getContainerSlotCSS(db_query("SELECT * FROM {grid_container_type}"));
            $html= $grid_lib->getEditorHTML(
                $grid_id,
                'grid',
                '/grid/ckeditor_config.js',
                \Drupal::url("grid.editor.ajax"),
                $this->config("grid.settings")->get("debug_mode"),
                \Drupal::url("grid.editor.preview",array("node"=>$nid)),
                'node/'.$nid.'/grid/{REV}/preview');
            $html="<style>".$css."</style>".$html;
            return array(
                '#attached'=>array(
                    'library'=>array('grid/editor')
                ),
                '#type'=>'markup',
                '#markup'=>new GridSafeString($html),
            );
        }
    }

    public function ajax()
    {
        $storage=grid_get_storage();
        ob_start();
        $storage->handleAjaxCall();
        $return=ob_get_clean();
        return new Response($return);
    }

    public function preview($node)
    {
        /** @var Node $node */
        $node=Node::load($node);
        $type=$node->getType();
        $types=$this->config("grid.settings")->get("enabled_node_types");
        $enabled=in_array($type,$types);
        if($enabled)
        {
            $grid_id=grid_get_grid_by_nid($node->id());
            if($grid_id===FALSE)
            {
                //TODO: throw http exception! (see: https://www.drupal.org/node/1616360)
            }
            else
            {
                global $grid_lib;

                // default grid css
                if($this->config("grid.settings")->get("use_grid_css")){
                    //drupal_add_css($grid_lib->getContainerSlotCSS(db_query("SELECT * FROM {grid_container_type}")),array('type'=>'inline'));
                    //TODO: inline our css
                }

                $storage=grid_get_storage();
                $storage->templatesPaths=grid_get_templates_paths();

                $grid=$storage->loadGrid($grid_id);
                return array(
                    '#type'=>'markup',
                    '#markup'=>$grid->render(FALSE),
                );
            }
        }
    }

    public function previewRevision($node,$revision)
    {
        /** @var Node $node */
        $node=Node::load($node);
        $type=$node->getType();
        $types=$this->config("grid.settings")->get("enabled_node_types");
        $enabled=in_array($type,$types);
        if($enabled)
        {
            $grid_id=grid_get_grid_by_nid($node->id());
            if($grid_id===FALSE)
            {
                //TODO: throw http exception!
            }
            else
            {
                global $grid_lib;

                // default grid css
                if($this->config("grid.settings")->get("use_grid_css")){
                    //drupal_add_css($grid_lib->getContainerSlotCSS(db_query("SELECT * FROM {grid_container_type}")),array('type'=>'inline'));
                    //TODO: inline our css
                }

                $storage=grid_get_storage();
                $storage->templatesPaths=grid_get_templates_paths();

                $grid=$storage->loadGrid($grid_id,$revision);
                return array(
                    '#type'=>'markup',
                    '#markup'=>$grid->render(FALSE),
                );
            }
        }
    }

    public function access(RouteMatchInterface $match, AccountInterface $account)
    {
        if($match->getRouteName()=="grid.editor.ajax")
        {
            return AccessResult::allowedIfHasPermission($account,"administer grid");
        }
        $nid=$match->getParameter("node");
        /** @var NodeInterface $node */
        $node=Node::load($nid);
        $type=$node->getType();
        $enabled=$this->config("grid.settings")->get("enabled_node_types");
        if(in_array($type,$enabled))
        {
            return AccessResult::allowedIfHasPermission($account,"administer grid");
        }
        else
        {
            return AccessResult::forbidden();
        }
    }
}