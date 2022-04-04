<?php

namespace Drupal\grid\TwoClick\API;

class ProviderAPIBase {

  protected $folderPath;

  public function __construct( $folderPath ) {
    $this->folderPath = $folderPath;
	}

  protected function alreadyLoaded( $url, $videoID ) {
    $files         = array_diff( scandir( $this->folderPath ), array( '.', '..' ) );
    foreach ($files as $file){
      if (strstr($file, $videoID) !== false) return true;
    }

    return false;
  }
}
