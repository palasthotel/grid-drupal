<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 20.08.15
 * Time: 10:51
 */

namespace Drupal\grid\Controller;


use Symfony\Component\HttpFoundation\Response;

class GridFrontendController
{
    public function css()
    {
        global $grid_lib;
        return new Response($grid_lib->getContainerSlotCSS(db_query("SELECT * FROM {grid_container_type}")),200,array("Content-Type"=>"text/css"));
    }
}