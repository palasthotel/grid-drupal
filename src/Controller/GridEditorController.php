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
use Drupal\Core\Database\Database;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\grid\Components\GridSafeString;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;

class GridEditorController extends ControllerBase implements AccessInterface
{
    public function editor(RouteMatchInterface $match)
    {
        $node_param=$match->getParameter("node");
        $nid = is_object($node_param) ? $node_param->id() : $node_param;
        $grid_id=grid_get_grid_by_nid($nid);
        if($grid_id===FALSE)
        {
            return \Drupal::formBuilder()->getForm('\Drupal\grid\Form\BuildGridForm',$nid);
        }
        else
        {

            $css=grid_get_library()->editor->getContainerSlotCSS(Database::getConnection()->query("SELECT * FROM {grid_container_type}"));

            $config=$this->config("grid.settings");

            $async_service = "";
            $async_domain = "";
            $async_author = "";
            $async_path = "";
            if($config->get("async_enabled",1)){
                $async_service=$config->get("async_url",'');
                if('' == $async_service){
	                $secure = "";
	                if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on'){
		                $secure = "s";
	                }
	                $async_service = "http".$secure."://async.the-grid.ws";
                }
                global $base_url;
                $async_domain= $base_url;
                $async_author = \Drupal::getContainer()->get("current_user")->getAccountName();
                $async_path="grid-node-id-".$nid;
            }

            $html= grid_get_library()->editor->getEditorHTML(
                $grid_id,
                'grid',
                '/grid/ckeditor_config.js',
                Url::fromRoute("grid.editor.ajax")->toString(),
                $this->config("grid.settings")->get("debug_mode"),
                Url::fromRoute("grid.editor.preview",array("node"=>$nid))->toString(),
	            '/node/'.$nid.'/grid/{REV}/preview',
                $async_service,
                $async_domain,
                $async_author,
                $async_path
            );

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
        ob_start();
        grid_get_library()->api->handleAjaxCall();
        $return=ob_get_clean();
        return new Response($return);
    }

    public function fileUpload()
    {
        $result=grid_get_library()->api->handleUpload();
        return new Response(json_encode(array('result'=>$result)));
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
                throw new NotFoundHttpException();
            }
            else
            {
                $html=grid_get_library()->api->loadGrid($grid_id)->render(FALSE);
                $render_array=array(
                  '#type'=>'grid_preview',
                  '#preview'=>new GridSafeString($html),
                );
                if(\Drupal::config("grid.settings")->get('use_grid_css'))
                {
                  $render_array['#attached']['library'][]='grid/frontend.css';
                }

                return $render_array;
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
                throw new NotFoundHttpException();
            }
            else
            {

	            $grid=grid_get_library()->api->loadGridByRevision($grid_id,$revision);
                $html=$grid->render(FALSE);
                // default grid css
                if($this->config("grid.settings")->get("use_grid_css")){
                    $css=grid_get_library()->editor->getContainerSlotCSS(Database::getConnection()->query("SELECT * FROM {grid_container_type}"));
                    $html="<style>".$css."</style>".$html;
                }
                return array(
                    '#type'=>'markup',
                    '#markup'=>new GridSafeString($html),
                );
            }
        }
    }

    public function access(RouteMatchInterface $match, AccountInterface $account)
    {
        if($match->getRouteName()=="grid.editor.ajax" || $match->getRouteName()=="grid.editor.fileupload")
        {
	        return AccessResult::allowedIfHasPermission($account,"administer grid");
        }
        $node_param = $match->getParameter("node");
        $node = is_object($node_param) ? $node_param : Node::load($node_param);

        if ($node && method_exists($node, "getType")) {
            $type = $node->getType();
        } else {
            return AccessResult::forbidden();
        }

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

    public function CKEditorConfig()
    {
        $styles=array();
        $formats=array();
        $formats_input=\Drupal::moduleHandler()->invokeAll("grid_formats");
        $styles_input=\Drupal::moduleHandler()->invokeAll("grid_styles");
        $ckeditor_plugins = array();
        \Drupal::moduleHandler()->alter("grid_ckeditor_plugins", $ckeditor_plugins);
        foreach($formats_input as $format)
        {
            if(!in_array($format, $formats))
            {
                $formats[]=$format;
            }
        }
        $styles=$styles_input;
        return new Response(
          grid_get_library()->editor->getCKEditorConfig($styles,$formats,$ckeditor_plugins),
          200,
          array("Content-Type"=>"application/javascript")
        );
    }
}
