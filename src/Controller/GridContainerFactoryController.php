<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 19.08.15
 * Time: 12:43
 */

namespace Drupal\grid\Controller;


use Drupal\grid\Components\GridSafeString;

class GridContainerFactoryController
{
    public function containerFactory()
    {
        global $grid_lib;
        $grid_db=grid_get_storage();
        $editor=$grid_lib->getContainerEditor();
        $html=$editor->run($grid_db);
        return array(
            '#attached'=>array(
                'library'=>array('grid/editor.container'),
            ),
            '#type'=>'markup',
            '#markup'=>new GridSafeString($html),
        );
    }
}