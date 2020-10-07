<?php


namespace Palasthotel\Grid\Drupal;


use Palasthotel\Grid\iHook;

class Hook implements iHook {

	public function fire( $name, $arguments = null ) {
	  if($arguments != null){
	    if(!is_array($arguments)) $arguments = [$arguments];
      \Drupal::moduleHandler()->invokeAll('grid_'.$name, $arguments);
    } else {
      \Drupal::moduleHandler()->invokeAll('grid_'.$name);
    }
	}

	public function alter( $name, $value, $arguments = null ) {
	  if($arguments != null){
      \Drupal::moduleHandler()->alter('grid_'.$name, $value, $arguments);
    } else {
      \Drupal::moduleHandler()->alter('grid_'.$name, $value );
    }
	  return $value;
	}
}
