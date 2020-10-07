<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 20.08.15
 * Time: 10:51
 */

namespace Drupal\grid\Controller;

use Grid\Constants\GridCSSVariant;
use Symfony\Component\HttpFoundation\Response;
use const Grid\Constants\GRID_CSS_VARIANT_TABLE;

class GridFrontendController
{
    public function css()
    {
      $defaultVariant = GridCSSVariant::getVariant(GRID_CSS_VARIANT_TABLE);
      $variant = (isset($_GET["variant"]))? $_GET["variant"]: $defaultVariant->slug();

      $containerTypes = db_query("SELECT * FROM {grid_container_type}")->fetchAll();
      $css = grid_get_library()->editor->getContainerSlotCSS($containerTypes, GridCSSVariant::getVariant($variant));
      return new Response( $css,200, array("Content-Type"=>"text/css"));
    }
}
