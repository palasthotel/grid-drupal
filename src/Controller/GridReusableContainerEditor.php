<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 19.08.15
 * Time: 13:56
 */

namespace Drupal\grid\Controller;


use Drupal\grid\Components\GridSafeString;
use Zend\Diactoros\Response\RedirectResponse;

class GridReusableContainerEditor
{
    public function overview()
    {
        $storage=grid_get_storage();
        global $grid_lib;
        $editor=$grid_lib->getReuseContainerEditor();
        $html=$editor->run($storage,function($id){
            return \Drupal::url("grid.admin.reusablecontainer.editor",array('container'=>$id));
        },function($id){
            return \Drupal::url("grid.admin.reusablecontainer.delete",array('container'=>$id));
        });
        return array(
            '#attached'=>array(
                'library'=>array('grid/editor.reusablecontainer'),
            ),
            '#type'=>'markup',
            '#markup'=>new GridSafeString($html),
        );
    }

    public function editor($container)
    {
        global $grid_lib;
        $editor=$grid_lib->getReuseContainerEditor();
        $html=$editor->runEditor(grid_get_storage(),
            $container,
            "/grid/ckeditor_config.js",
            \Drupal::url('grid.ajax'),
            \Drupal::config('grid.settings')->get('debug_mode'),
            \Drupal::url('grid.admin.reusablecontainer.preview',array('container'=>$container)));
        return array(
            '#attached'=>array(
                'library'=>array('grid/editor.reusablecontainer'),
            ),
            '#type'=>'markup',
            '#markup'=>new GridSafeString($html),
        );
    }

    public function preview($container)
    {
        $storage=grid_get_storage();
        $grid=$storage->loadGrid("container:".$container);
        $html=$grid->render(FALSE);
        return array(
            '#type'=>'markup',
            '#markup'=>new GridSafeString($html),
        );
    }

    public function delete($container)
    {
        $storage=grid_get_storage();
        global $grid_lib;
        $editor=$grid_lib->getReuseContainerEditor();
        $result=$editor->runDelete($storage,$container);
        if($result===TRUE)
        {
            return new RedirectResponse(\Drupal::url('grid.admin.reusablecontainer.overview'));
        }
        return array(
            '#type'=>'markup',
            '#markup'=>new GridSafeString($result),
        );
    }
}