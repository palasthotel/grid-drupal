<?php
/**
 * Created by PhpStorm.
 * User: edward
 * Date: 10.05.17
 * Time: 10:21
 */

namespace Drupal\grid\Element;


use Drupal\Core\Render\Element\RenderElement;

/**
 * Class GridPreviewElement
 * @package Drupal\grid\Element
 * @\Drupal\Core\Render\Annotation\RenderElement("grid_preview")
 */
class GridPreviewElement extends RenderElement {

	public function getInfo()
	{
		return array(
			'#theme'=>'grid_preview',
			'#preview'=>''
		);
	}

}