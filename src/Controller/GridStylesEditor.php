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
        return array(
            '#type'=>'markup',
            '#markup'=>new GridSafeString(grid_get_library()->editor->getStyleEditor()->run(grid_get_storage()))
        );
    }
}
