<?php

namespace Drupal\grid\TwoClick\API;

use Drupal\grid\TwoClick\Constants\EmbedProperties;

interface ProviderAPIInterface {
  public function getThumbnail(string $url): string;
  public function getEmbedProperties( string $url ) : EmbedProperties;
  public function setEmbedCode( string $embedCode ) : void;


}
