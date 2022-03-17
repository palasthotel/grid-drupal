<?php

namespace Drupal\grid\TwoClick;

use Drupal\grid\TwoClick\API\VimeoAPI;
use Drupal\grid\TwoClick\API\YouTubeAPI;
use Drupal\grid\TwoClick\Constants\Constants;

class TwoClickEmbedder {
  private $folderPath;
  private $api;
  private $provider;

  public function __construct($folderPath){
    $this->folderPath = $folderPath;
  }

  public function getProvider( $url ) {
    $result = parse_url( $url );

    if ( preg_match( "/\w*?\.youtube\./um", $result['host'] ) || preg_match( "/youtu\.be/um", $result['host'] ) ) {
      $this->api = new YouTubeAPI( $this->folderPath );
      return Constants::PROVIDER_YOUTUBE;
    }

    if ( preg_match( "/(\w*?\.)?vimeo\./um", $result['host'] ) ) {
      $this->api = new VimeoAPI( $this->folderPath );
      return Constants::PROVIDER_VIMEO;
    }

    return false;
  }


  public function switchIFrame( $url, $originalData = [] ) {

    $provider = $this->getProvider($url);

    if ( ! $provider ) {
      return false;
    }

    $data = $this->api->getData( $url );

    //$data['code'] = $this->api->providerifyIt($data['code']);

    $videoProperties = [
      'title'          => $data->title,
      'author'         => $data->author_name,
      'url'            => $url,
      'urlDescription' => "Watch on $provider",
      'embed'          => $data->html,
      'thumbnail'      => $this->api->getThumbnail( $url )
    ];

    $originalData['videoProperties'] = $videoProperties;
    $originalData['code'] = $this->generateHTML( $videoProperties );

    return $originalData;

  }

  public function generateHTML( $videoProperties ) {

    $videoembed     = $videoProperties['embed'];
    $videoTitle     = $videoProperties['title'];
    $videoAuthor    = $videoProperties['author'];
    $videoUrl       = $videoProperties['url'];
    $urlDescription = $videoProperties['urlDescription'];
    $thumbnail      = $videoProperties['thumbnail'];

    $config = \Drupal::config('grid.settings');

    $disclaimer = t($config->get('two_click_disclaimer_text'));
    $disclaimerLink = $config->get('two_click_disclaimer_link');


    $disclaimerTag = "<p class='two-click__disclaimer'><a class='two-click__disclaimer-link' href='$disclaimerLink' target='_blank'>$disclaimer</a></p>";

    if ($disclaimer == ""){
      $disclaimerTag = "";
    }



    $html = <<<HTML
            <div class="two-click two-click__container" data-videoembed='$videoembed'>
                <h3 class="two-click__title">$videoTitle - $videoAuthor</h3>
                <div class="two-click__button-container">
                  <svg class="two-click__play-button" viewBox="0 0 18 20" width="18px" height="20px">
                      <g stroke="none" stroke-width="1" fill-rule="evenodd">
                          <polygon transform="translate(9.000000, 10.000000) rotate(-270.000000) translate(-9.000000, -10.000000) " points="9 1 19 19 -1 19"></polygon>
                      </g>
                  </svg>
                </div>
                $disclaimerTag
                <img class="two-click__thumbnail" src="$thumbnail" alt="video-thumbnail">
                <a class="two-click__provider-link" href="$videoUrl" target="_blank">$urlDescription</a>
            </div>

HTML;

    return $html;

  }


}
