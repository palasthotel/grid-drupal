<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 19.08.15
 * Time: 13:26
 */

namespace Drupal\grid\Controller;


use Drupal\grid\Components\GridSafeString;

class GridStylesEditor
{
    public function styles()
    {
        global $grid_lib;
        return array(
            '#type'=>'markup',
            '#markup'=>new GridSafeString($grid_lib->getStyleEditor()->run(grid_get_storage()))
        );
    }
}