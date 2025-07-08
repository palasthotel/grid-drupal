<?php

namespace Drupal\grid\TwoClick\API;

use Drupal\grid\TwoClick\Constants\Constants;
use Drupal\grid\TwoClick\Constants\EmbedProperties;

class SpotifyAPI extends ProviderAPIBase implements ProviderAPIInterface {
  public function getThumbnail(string $url): string
  {
    $parsedUrl = parse_url($url);
    $showID = explode('show/' ,$parsedUrl['path']);
    $showID = array_pop($showID);

    $oembedUrl = urlencode("https://open.spotify.com/show/$showID");
    $oembedEndpoint = "https://open.spotify.com/oembed?url=$oembedUrl";

    $request = curl_init($oembedEndpoint);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($request, CURLOPT_HEADER, false);
    $result = curl_exec($request);
    if ($result === false) {
      var_dump(curl_error($request));
      die();
    }
    curl_close($request);
    $result = json_decode($result);

    return $result->thumbnail_url;
  }

  public function getEmbedProperties(string $url): EmbedProperties
  {
    $parsedUrl = parse_url($url);
    $showID = explode('show/' ,$parsedUrl['path']);
    $showID = array_pop($showID);

    $oembedUrl = urlencode("https://open.spotify.com/show/$showID");
    $oembedEndpoint = "https://open.spotify.com/oembed?url=$oembedUrl";


    $request = curl_init($oembedEndpoint);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($request, CURLOPT_HEADER, false);
    $result = curl_exec($request);
    if ($result === false) {
      var_dump(curl_error($request));
      die();
    }
    curl_close($request);
    $result = json_decode($result);


    $properties = [
      'title'          => $result->title,
      'author'         => '',
      'url'            => "https://open.spotify.com/show/$showID",
      'urlDescription' => t('Open on Spotify'),
      'embed'          => $result->html,
      'thumbnail'      => $result->thumbnail_url,
      'provider'       => 'Spotify',
    ];

    $embedProperties = new EmbedProperties($properties);

    return $embedProperties;
  }
}
