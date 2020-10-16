<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 14.08.15
 * Time: 16:14
 */

namespace Drupal\grid\filter;


use Drupal\grid\Components\GridAjaxEndpoint;
use grid_ajaxendpoint;
use Palasthotel\Grid\Endpoint;

class FilterPermissions
{
    public function permissions()
    {
        $ajax=new GridAjaxEndpoint();
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
