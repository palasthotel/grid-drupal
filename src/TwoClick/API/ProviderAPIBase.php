<?php

namespace Drupal\grid\TwoClick\API;

use Drupal\grid\TwoClick\Constants\Constants;
use Drupal\grid\TwoClick\Constants\EmbedProperties;

class ProviderAPIBase {

  protected $folderPath;
  protected $embedCode = '<div></div>';

  public function __construct( $folderPath ) {
    $this->folderPath = $folderPath;
	}

  protected function alreadyLoaded( $videoID ) {
    $files         = array_diff( scandir( $this->folderPath ), array( '.', '..' ) );
    foreach ($files as $file){
      if (str_contains($file, $videoID)) return true;
    }

    return false;
  }

  public function setEmbedCode(string $embedCode): void
  {
    $this->embedCode = $embedCode;
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


    $useDefaultThumbnail = str_contains($thumbnail, "default.jpg") ? "hide" : "";
    $showDefault = str_contains($thumbnail, "default.jpg") ? "default" : "";


    $html = <<<HTML
            <div class="two-click $provider" data-original-embed='$videoembed' data-embed='$videoembed'>
                <div class="two-click__container">
                  <h3 class="two-click__title $showDefault">$title</h3>
                  <div class="two-click__button-container">
                    <svg class="two-click__play-button" viewBox="0 0 18 20" width="18px" height="20px" tabindex="0" aria-label="Play">
                        <g stroke="none" stroke-width="1" fill-rule="evenodd">
                            <polygon transform="translate(9.000000, 10.000000) rotate(-270.000000) translate(-9.000000, -10.000000) " points="9 1 19 19 -1 19"></polygon>
                        </g>
                    </svg>
                  </div>
                  <img class="two-click__thumbnail $useDefaultThumbnail" src="$thumbnail" alt="video-thumbnail">
                  <a class="two-click__provider-link" href="$videoUrl" target="_blank" tabindex="0" aria-label="Watch on $provider">$urlDescription</a>
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
                        <a class='two-click__disclaimer-link' href='$disclaimerLink' target='_blank' tabindex="0" aria-label="More Info"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.2.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M256 512c141.4 0 256-114.6 256-256S397.4 0 256 0S0 114.6 0 256S114.6 512 256 512zM216 336h24V272H216 192V224h24 48 24v24 88h8 24v48H296 216 192V336h24zm72-144H224V128h64v64z"/></svg></a>
                    </label>
                </div>
            </div>

HTML;

    return $html;

  }
}
