<?php

namespace Drupal\grid\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\grid\TwoClick\Constants\Constants;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class GridCommands extends DrushCommands {


  /**
   * @param $grid
   * @command grid:export
   */
  public function export($grid) {
    $grid_storage = grid_get_storage();
    $loaded_grid = $grid_storage->loadGrid($grid);
    $containers = $loaded_grid->container;
    foreach($containers as $container) {
      $container->grid = null;
      $container->storage=null;
      foreach($container->slots as $slot) {
        $slot->grid=null;
        $slot->storage=null;
        foreach($slot->boxes as $box) {
          $box->grid=null;
          $box->storage=null;
        }
      }
    }
    echo serialize($containers);
  }

  /**
   * @command grid:import
   */
  public function import() {
    $grid_storage = grid_get_storage();
    $content = file_get_contents("php://stdin");
    /** @var \Palasthotel\Grid\Model\Container[] $loaded */
    $loaded = unserialize($content);
    $grid_id = $grid_storage->createGrid();
    $grid = $grid_storage->loadGrid($grid_id);
    //we can't use the storage persisting options as those do something wrong...
    foreach($loaded as $container_to_import) {
      $type = $container_to_import->type;
      $imported_container = $grid_storage->createContainer($grid,$type);
      $grid->container[] = $imported_container;
      for($i=0;$i<count($imported_container->slots);$i++) {
        $imported_slot = $imported_container->slots[$i];
        $slot_to_import = $container_to_import->slots[$i];
        foreach($slot_to_import->boxes as $box) {
          $box->boxid=null;
          $box->grid=$grid;
          $box->storage=$grid_storage;
          $grid_storage->persistBox($box);
          $imported_slot->boxes[] = $box;
        }
        $grid_storage->storeSlotOrder($imported_slot);
      }
    }
    $grid_storage->storeContainerOrder($grid);
    echo $grid->gridid."\n";
  }

  /**
   * Command description here.
   *
   * @param $arg1
   *   Argument description.
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   * @option option-name
   *   Description
   * @usage grid-commandName foo
   *   Usage description
   *
   * @command grid:commandName
   * @aliases foo
   */
  public function commandName($arg1, $options = ['option-name' => 'default']) {
    $this->logger()->success(dt('Achievement unlocked.'));
  }

  /**
   * An example of the table output format.
   *
   * @param array $options An associative array of options whose values come from cli, aliases, config, etc.
   *
   * @field-labels
   *   group: Group
   *   token: Token
   *   name: Name
   * @default-fields group,token,name
   *
   * @command grid:token
   * @aliases token
   *
   * @filter-default-field name
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   */
  public function token($options = ['format' => 'table']) {
    $all = \Drupal::token()->getInfo();
    foreach ($all['tokens'] as $group => $tokens) {
      foreach ($tokens as $key => $token) {
        $rows[] = [
          'group' => $group,
          'token' => $key,
          'name' => $token['name'],
        ];
      }
    }
    return new RowsOfFields($rows);
  }

  /**
   * Clears all two-click-data
   *
   * @command grid:clearTwoClick
   */
  public function clearTwoClick()
  {
    $this->clearTwoClickThumbnails();
    $this->clearTwoClickDatabase();
  }

	/**
	 * @command grid:clearTwoClickThumbnails
	 */
	public function clearTwoClickThumbnails()
	{
		grid_delete_video_thumbnails();
    $this->logger()->success("Deleted thumbnails in ". Constants::THUMBNAIL_FOLDER_PATH);

  }

  /**
   * @command grid:clearTwoClickDB
   */
  public function clearTwoClickDatabase()
  {
    $database = \Drupal::database();
    $result = $database->delete(Constants::TWO_CLICK_DB_TABLE)->execute();
    $this->logger()->success("Database-table '" . Constants::TWO_CLICK_DB_TABLE . "' deleted!");
  }


}
