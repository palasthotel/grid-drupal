<?php

namespace Drupal\grid\TwoClick\API;

class ProviderAPIBase {

  protected $folderPath;
  protected $embedCode = '<div></div>';

  public function __construct( $folderPath ) {
    $this->folderPath = $folderPath;
	}

  protected function alreadyLoaded( $videoID ) {
    $files         = array_diff( scandir( $this->folderPath ), array( '.', '..' ) );
    foreach ($files as $file){
      if (strstr($file, $videoID) !== false) return true;
    }

    return false;
  }

  public function setEmbedCode(string $embedCode): void
  {
    $this->embedCode = $embedCode;
  }
}
