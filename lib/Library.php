<?php


namespace Palasthotel\Grid\Drupal;

use Drupal\grid\Components\GridAjaxEndpoint;
use Palasthotel\Grid\API;
use Palasthotel\Grid\Core;
use Palasthotel\Grid\Editor;
use Palasthotel\Grid\Template;

/**
 * @property Core core
 * @property Query query
 * @property Hook hook
 * @property API api
 * @property GridAjaxEndpoint endpoint
 * @property Template template
 * @property Editor editor
 */
class Library {

  const FIRE_LOAD_CLASSES = "load_classes";
  const ALTER_TEMPLATES_PATHS = "templates_paths";

  public function __construct() {
    $this->query = new Query();
    $this->hook = new Hook();
    $this->core = new Core(
      $this->query,
      $this->hook,
      \Drupal::currentUser()->getAccount()->getAccountName()
    );

    $this->endpoint = new GridAjaxEndpoint();
    $this->template = new Template();
    $this->api = new API($this->core, $this->endpoint, $this->template);

    $this->editor = new Editor(
      $this->core->storage,
      "/".drupal_get_path('module','grid')."/lib/grid/"
    );

    $this->hook->fire(self::FIRE_LOAD_CLASSES);

    // collect template paths
    $templates = array();
    $theme_path = DRUPAL_ROOT . '/'. \Drupal::theme()->getActiveTheme()->getPath();
    $templates[] =  $theme_path.'/grid';
    $templates = $this->hook->alter(self::ALTER_TEMPLATES_PATHS, $templates);
    $templates[] = dirname(__FILE__)."/../core/templates/drupal";

    foreach ($templates as $path){
      $this->template->addTemplatesPath($path);
    }

  }

}
