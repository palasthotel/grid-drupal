<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 19.08.15
 * Time: 13:33
 */

namespace Drupal\grid\Controller;


use Drupal\grid\Components\GridSafeString;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GridReusableBoxesEditor
{
    public function boxesOverview()
    {
        $storage=grid_get_storage();
        global $grid_lib;
        $editor=$grid_lib->getReuseBoxEditor();
        $html=$editor->run($storage,function($id){
            return \Drupal::url("grid.admin.reusableboxes.editor",array('box'=>$id));
        },function($id){
            return \Drupal::url("grid.admin.reusableboxes.delete",array('box'=>$id));
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
        $storage=grid_get_storage();
        global $grid_lib;
        $editor=$grid_lib->getReuseBoxEditor();
        $html=$editor->runEditor($storage,
            $box,
            \Drupal::url('grid.editor.ckeditorjs'),
            \Drupal::url('grid.editor.ajax'),
            \Drupal::config('grid.settings')->get('debug_mode'),
            \Drupal::url('grid.admin.reusableboxes.preview',array('box'=>$box)));
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
        $storage=grid_get_storage();
        $grid=$storage->loadGrid("box:".$box);
        return array(
            '#type'=>'markup',
            '#markup'=>new GridSafeString($grid->render(FALSE)),
        );
    }

    public function delete($box)
    {
        $storage=grid_get_storage();
        global $grid_lib;
        $editor=$grid_lib->getReuseBoxEditor();
        $result=$editor->runDelete($storage,$box);
        if($result===TRUE)
        {
            return new RedirectResponse(\Drupal::url('grid.admin.reusableboxes.overview'));
        }
        return array(
            '#type'=>'markup',
            '#markup'=>new GridSafeString($result),
        );
    }
}