<?php

namespace Drupal\grid\TwoClick\API;

use Drupal\grid\TwoClick\Constants\Constants;
use Drupal\grid\TwoClick\Constants\EmbedProperties;

class PodigeeAPI extends ProviderAPIBase implements ProviderAPIInterface
{

  public function getThumbnail(string $url): string
  {

    $explodedUrl = explode('/', $url);
    $imageID = end($explodedUrl);

    if (!$this->alreadyLoaded($imageID)) {
      $ch            = curl_init( $url );
      $fp           = fopen( $this->folderPath . '/' . $imageID, 'wb' );
      curl_setopt( $ch, CURLOPT_FILE, $fp );
      curl_setopt( $ch, CURLOPT_HEADER, false );
      curl_exec( $ch );
      curl_close( $ch );
      fclose( $fp );
    }

    return \Drupal::service('file_url_generator')->generateString(Constants::THUMBNAIL_FOLDER_PATH . $imageID);

  }

  public function getEmbedProperties(string $url): EmbedProperties
  {
    $podigeeOembedUrl = "https://embed.podigee.com/oembed?url=$url";

    $request     = curl_init( $podigeeOembedUrl );

    curl_setopt( $request, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $request, CURLOPT_HEADER, false );
    $result = curl_exec( $request );
    curl_close( $request );
    $result           = json_decode( $result );

    $embedProperties = new EmbedProperties();
    if(is_null($result)) return $embedProperties;


    $this->embedCode = $result->html;

    $properties = [
      'title'          => t($result->title ?? ""),
      'author'         => $result->author_name ?? "",
      'url'            => $url,
      'urlDescription' => t("Listen on @provider", ['@provider' => 'Podigee']),
      'embed'          => $this->embedCode,
      'thumbnail'      => $this->getThumbnail( $result->thumbnail_url ),
      'provider'       => 'Podigee'
    ];

    foreach ($properties as $propertyName => $propertyValue){
      $embedProperties->set($propertyName, $propertyValue);
    }

    return $embedProperties;
  }


  public function generateHTML(EmbedProperties $embedProperties)
  {
    $embed     = $embedProperties->embed;
    $title     = $embedProperties->title;
    // $author    = $embedProperties->author !== "" ? " - " . $embedProperties->author : "";
    // $title .= $author;
    $thumbnail      = $embedProperties->thumbnail;

    $provider = strtolower($embedProperties->provider);

    $config = \Drupal::config(Constants::TWO_CLICK_SETTINGS);
    $disclaimer = t($config->get(Constants::TWO_CLICK_SETTINGS_DISCLAIMER_TEXT));
    $disclaimerLink = $config->get(Constants::TWO_CLICK_SETTINGS_PRIVACY_LINK);


    $useDefaultThumbnail = str_contains($thumbnail, "default.jpg") ? "hide" : "";
    $showDefault = str_contains($thumbnail, "default.jpg") ? "default" : "";

    $buttonText = t("Play Episode");


    $autoplayScript = <<<JS

document.addEventListener('two-click-iframe-embedded', () => {
    if (typeof playerjs == 'undefined') return;

    setTimeout(() => {
        const twoClickContainer = document.querySelector('.two-click.podigee');
        const iframe = twoClickContainer.querySelector('iframe')
        if (!iframe) return;

        const player = new playerjs.Player(iframe);
        player.on(playerjs.EVENTS.PLAY, () => console.log('play'));
        player.on('ready', () => player.play());
    }, 500)
});
JS;


    $html = <<<HTML
            <div class="two-click $provider" data-original-embed='$embed' data-embed='$embed'>
                <div class="two-click__container">
                    <div class="two-click__image_container">
                        <img class="two-click__thumbnail $useDefaultThumbnail" src="$thumbnail" alt="video-thumbnail">
                    </div>
                    <div class="two-click__description_container">
                        <h3 class="two-click__title $showDefault">$title</h3>
                        <div class="two-click__button-container">

                          <svg class="two-click__play-button" viewBox="0 0 18 20" width="18px" height="20px" tabindex="0" aria-label="Play">
                              <g stroke="none" stroke-width="1" fill-rule="evenodd">
                                  <polygon transform="translate(9.000000, 10.000000) rotate(-270.000000) translate(-9.000000, -10.000000) " points="9 1 19 19 -1 19"></polygon>
                              </g>
                          </svg>
                          <span class="two-click__button-text">$buttonText</span>
                        </div>
                    </div>
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
            <script type="text/javascript" src="/libraries/playerjs/player-0.1.0.min.js" ></script>

            <script>
            $autoplayScript
            </script>

HTML;

    return $html;
  }

}
