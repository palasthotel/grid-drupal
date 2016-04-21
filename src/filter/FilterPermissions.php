<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 14.08.15
 * Time: 16:14
 */

namespace Drupal\grid\filter;


use grid_ajaxendpoint;

class FilterPermissions
{
    public function permissions()
    {
        $storage=grid_get_storage();
        $ajax=new grid_ajaxendpoint();
        $rights=$ajax->Rights();
        $results=array();
        foreach($rights as $right)
        {
            $results["grid: ".$right]=array(
                'title'=>"grid: ".$right,
            );
        }
        $results["administer grid"]=array(
            'title'=>'administer grid',
        );
        $results['edit grid']=array(
            'title'=>'Edit Grids'
        );
        return $results;
    }
}