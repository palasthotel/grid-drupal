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
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Symfony\Component\Routing\Route;

class GridEditorController extends ControllerBase implements AccessInterface
{
    public function editor()
    {
        return array(
            '#type'=>'markup',
            '#markup'=>'<h4>Hello World!</h4>',
        );
    }

    public function access(RouteMatchInterface $match, AccountInterface $account)
    {
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