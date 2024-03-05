<?php

namespace Drupal\grid\TwoClick;

use Drupal\Core\File\FileSystemInterface;
use Drupal\grid\TwoClick\API\PodigeeAPI;
use Drupal\grid\TwoClick\API\VimeoAPI;
use Drupal\grid\TwoClick\API\YouTubeAPI;
use Drupal\grid\TwoClick\API\DefaultProvider;
use Drupal\grid\TwoClick\Constants\Constants;
use Drupal\grid\TwoClick\Constants\EmbedProperties;

class TwoClickEmbedder {
  private $folderPath;
  private $api;
  private string $customEmbedCode = '';

  public function __construct($folderPath){
    \Drupal::service( 'file_system' )->prepareDirectory( $folderPath, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS );
    $this->folderPath = $folderPath;
    $this->api = new DefaultProvider($folderPath);
  }

  public function getProvider( $url ) {

    $this->api = new DefaultProvider($this->folderPath);
    if ($url === '') return Constants::PROVIDER_DEFAULT;

    $result = parse_url( $url );

    if ( preg_match( "/\w*?\.youtube\./um", $result['host'] ) || preg_match( "/youtu\.be/um", $result['host'] ) ) {
      $this->api = new YouTubeAPI( $this->folderPath );
      return Constants::PROVIDER_YOUTUBE;
    }

    if ( preg_match( "/(\w*?\.)?vimeo\./um", $result['host'] ) ) {
      $this->api = new VimeoAPI( $this->folderPath );
      return Constants::PROVIDER_VIMEO;
    }

    // if ( preg_match( "/(\w*?\.)?podigee\./um", $result['host'] ) ) {
    //  $this->api = new PodigeeAPI( $this->folderPath );
    //  return Constants::PROVIDER_PODIGEE;
    // }

    return Constants::PROVIDER_DEFAULT;
  }

  public function setEmbedCode(string $embedCode): void {
    $this->customEmbedCode = $embedCode;
    $this->api->setEmbedCode($embedCode);
  }


  public function switchIFrame( $url, $originalData = [] ) {

    $url = trim($url);

    $provider = $this->getProvider($url);

    if ($this->customEmbedCode !== '') $this->api->setEmbedCode($this->customEmbedCode);

    $embedProperties = $this->api->getEmbedProperties( $url );

    $originalData['embedProperties'] = $embedProperties;
    $originalData['code'] = $this->api->generateHTML( $embedProperties );

    return $originalData;

  }


}
