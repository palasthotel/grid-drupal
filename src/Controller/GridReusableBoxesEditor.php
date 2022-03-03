<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 19.08.15
 * Time: 13:33
 */

namespace Drupal\grid\Controller;


use Drupal\Core\Url;
use Drupal\grid\Components\GridSafeString;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GridReusableBoxesEditor
{
    public function boxesOverview()
    {
        $editor=grid_get_library()->editor->getReuseBoxEditor();
        $html=$editor->run(function($id){
            return Url::fromRoute("grid.admin.reusableboxes.editor",array('box'=>$id))->toString();
        },function($id){
            return Url::fromRoute("grid.admin.reusableboxes.delete",array('box'=>$id))->toString();
        });
        return array(
            '#attached'=>array(
                'library'=>array('grid/editor.reusableboxes'),
            ),
            '#type'=>'markup',
            '#markup'=>new GridSafeString($html),
        );
    }

    public function editor($box)
    {
        $editor=grid_get_library()->editor->getReuseBoxEditor();
        $html=$editor->runEditor(
            $box,
            Url::fromRoute('grid.editor.ckeditorjs')->toString(),
            Url::fromRoute('grid.editor.ajax')->toString(),
            \Drupal::config('grid.settings')->get('debug_mode'),
            Url::fromRoute('grid.admin.reusableboxes.preview',array('box'=>$box))->toString());
        return array(
            '#attached'=>array(
                'library'=>array('grid/editor.reusableboxes'),
            ),
            '#type'=>'markup',
            '#markup'=>new GridSafeString($html),
        );
    }

    public function preview($box)
    {
        $grid=grid_get_library()->api->loadGrid("box:".$box);
        return array(
            '#type'=>'markup',
            '#markup'=>new GridSafeString($grid->render(FALSE)),
        );
    }

    public function delete($box)
    {
        $storage=grid_get_storage();
        $editor=grid_get_library()->editor->getReuseBoxEditor();
        $result=$editor->runDelete($storage,$box);
        if($result===TRUE)
        {
            return new RedirectResponse(Url::fromRoute('grid.admin.reusableboxes.overview')->toString());
        }
        return array(
            '#type'=>'markup',
            '#markup'=>new GridSafeString($result),
        );
    }
}
