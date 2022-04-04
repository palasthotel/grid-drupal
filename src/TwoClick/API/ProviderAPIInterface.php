<?php

namespace Drupal\grid\TwoClick\API;

interface ProviderAPIInterface {
  public function getThumbnail($url);
  public function getData( $url );


}
