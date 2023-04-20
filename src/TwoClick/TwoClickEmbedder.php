<?php

namespace Drupal\grid\TwoClick;

use Drupal\Core\File\FileSystemInterface;
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
    $this->api = new DefaultProvider();
  }

  public function getProvider( $url ) {

    $this->api = new DefaultProvider();
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

    return Constants::PROVIDER_DEFAULT;
  }

  public function setEmbedCode(string $embedCode): void {
    $this->customEmbedCode = $embedCode;
    $this->api->setEmbedCode($embedCode);
  }


  public function switchIFrame( $url, $originalData = [] ) {

    $provider = $this->getProvider($url);

    if ($this->customEmbedCode !== '') $this->api->setEmbedCode($this->customEmbedCode);
    if ( ! $provider ) {
      return false;
    }

    $embedProperties = $this->api->getEmbedProperties( $url );

    $originalData['embedProperties'] = $embedProperties;
    $originalData['code'] = $this->generateHTML( $embedProperties );

    return $originalData;

  }

  public function generateHTML( EmbedProperties $embedProperties ) {

    $videoembed     = $embedProperties->embed;
    $videoTitle     = $embedProperties->title;
    $videoAuthor    = $embedProperties->author !== "" ? " - " . $embedProperties->author : "";
    $title = $videoTitle . $videoAuthor;
    $videoUrl       = $embedProperties->url;
    $urlDescription = $embedProperties->urlDescription;
    $thumbnail      = $embedProperties->thumbnail;

    $provider = strtolower($embedProperties->provider);

    $config = \Drupal::config(Constants::TWO_CLICK_SETTINGS);
    $disclaimer = t($config->get(Constants::TWO_CLICK_SETTINGS_DISCLAIMER_TEXT));
    $disclaimerLink = $config->get(Constants::TWO_CLICK_SETTINGS_PRIVACY_LINK);


    $useDefaultThumbnail = is_numeric(strpos($thumbnail, "default.jpg")) ? "hide" : "";


    $html = <<<HTML
            <div class="two-click $provider" data-embed='$videoembed'>
                <div class="two-click__container">
                  <h3 class="two-click__title">$title</h3>
                  <div class="two-click__button-container">
                    <svg class="two-click__play-button" viewBox="0 0 18 20" width="18px" height="20px">
                        <g stroke="none" stroke-width="1" fill-rule="evenodd">
                            <polygon transform="translate(9.000000, 10.000000) rotate(-270.000000) translate(-9.000000, -10.000000) " points="9 1 19 19 -1 19"></polygon>
                        </g>
                    </svg>
                  </div>
                  <img class="two-click__thumbnail $useDefaultThumbnail" src="$thumbnail" alt="video-thumbnail">
                  <a class="two-click__provider-link" href="$videoUrl" target="_blank">$urlDescription</a>
                </div>
                <div class='two-click__disclaimer'>
                    <label class="toggler-wrapper">
                        <input type="checkbox" >
                        <div class="toggler">
                            <div class="toggler-slider">
                                <div class="toggler-knob"></div>
                            </div>
                        </div>
                        <span class="toggle-label">$disclaimer</span>
                        <a class='two-click__disclaimer-link' href='$disclaimerLink' target='_blank'><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M256 512c141.4 0 256-114.6 256-256S397.4 0 256 0S0 114.6 0 256S114.6 512 256 512zM216 336h24V272H216 192V224h24 48 24v24 88h8 24v48H296 216 192V336h24zm72-144H224V128h64v64z"/></svg></a>
                    </label>
                </div>
            </div>

HTML;

    return $html;

  }


}
