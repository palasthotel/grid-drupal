<?php

namespace Drupal\grid\TwoClick\API;

use Drupal\grid\TwoClick\Constants\Constants;
use Drupal\grid\TwoClick\Constants\EmbedProperties;

class DefaultProvider extends ProviderAPIBase implements ProviderAPIInterface
{

  protected $embedCode = '<div></div>';
  public function getThumbnail(string $url = "") : string
  {
    return "default.jpg";
  }

  public function setEmbedCode(string $embedCode) : void {
    $this->embedCode = $embedCode;
  }

  public function getEmbedProperties(string $url): EmbedProperties
  {

    $config = \Drupal::config(Constants::TWO_CLICK_SETTINGS);
    $disclaimer = t($config->get(Constants::TWO_CLICK_SETTINGS_DISCLAIMER_TEXT));
    $urlDescription = $url === '' ? '' : "Open external content on original site";

    $properties = [
      'title'          => $disclaimer,
      'author'         => '',
      'url'            => $url,
      'urlDescription' => t($urlDescription),
      'embed'          => $this->embedCode,
      'thumbnail'      => $this->getThumbnail( $url ),
      'provider'       => 'default'
    ];

    $embedProperties = new EmbedProperties();

    foreach ($properties as $propertyName => $propertyValue){
      $embedProperties->set($propertyName, $propertyValue);
    }


    return $embedProperties;
  }
}
