<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 19.08.15
 * Time: 13:56
 */

namespace Drupal\grid\Controller;


use Drupal\Core\Url;
use Drupal\grid\Components\GridSafeString;
use Zend\Diactoros\Response\RedirectResponse;

class GridReusableContainerEditor
{
    public function overview()
    {
        $storage=grid_get_storage();
        $editor=grid_get_library()->editor->getReuseContainerEditor();
        $html=$editor->run($storage,function($id){
            return Url::fromRoute("grid.admin.reusablecontainer.editor",array('container'=>$id))->toString();
        },function($id){
            return Url::fromRoute("grid.admin.reusablecontainer.delete",array('container'=>$id))->toString();
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
        $editor=grid_get_library()->editor->getReuseContainerEditor();
        $html=$editor->runEditor(
            $container,
            "/grid/ckeditor_config.js",
          Url::fromRoute('grid.editor.ajax')->toString(),
            \Drupal::config('grid.settings')->get('debug_mode'),
          Url::fromRoute('grid.admin.reusablecontainer.preview',array('container'=>$container))->toString());
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
        $grid=grid_get_library()->api->loadGrid("container:".$container);
        $html=$grid->render(FALSE);
        return array(
            '#type'=>'markup',
            '#markup'=>new GridSafeString($html),
        );
    }

    public function delete($container)
    {
        $storage=grid_get_storage();
        $editor=grid_get_library()->editor->getReuseContainerEditor();
        $result=$editor->runDelete($storage,$container);
        if($result===TRUE)
        {
            return new RedirectResponse(Url::fromRoute('grid.admin.reusablecontainer.overview')->toString());
        }
        return array(
            '#type'=>'markup',
            '#markup'=>new GridSafeString($result),
        );
    }
}
