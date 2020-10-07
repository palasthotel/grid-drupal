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
        $editor=grid_get_library()->editor->getContainerEditor();
        $html=$editor->run(grid_get_storage());
        return array(
            '#attached'=>array(
                'library'=>array('grid/editor.container'),
            ),
            '#type'=>'markup',
            '#markup'=>new GridSafeString($html),
        );
    }
}
