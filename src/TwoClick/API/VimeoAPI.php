<?php

namespace Drupal\grid\TwoClick\API;

use Drupal\grid\TwoClick\Constants\Constants;

class VimeoAPI extends ProviderAPIBase implements ProviderAPIInterface {

  public function getThumbnail( $url ) {

    $explodedUrl = explode( '/', $url );
    $videoId     = end( $explodedUrl );

    if (! $this->alreadyLoaded($url, $videoId) ){
      $vimeoAPIKey = Constants::VIMEO_API_KEY;

      $url           = "https://api.vimeo.com/videos/$videoId/pictures?sizes=960";
      $ch            = curl_init( $url );
      $customHeaders = array(
        "Authorization: bearer $vimeoAPIKey",
      );
      curl_setopt( $ch, CURLOPT_HTTPHEADER, $customHeaders );
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
      curl_setopt( $ch, CURLOPT_HEADER, false );
      $thumbnailresult = curl_exec( $ch );
      curl_close( $ch );
      $thumbnailresult = json_decode( $thumbnailresult );

      $thumbnailurl = $thumbnailresult->data[0]->sizes[0]->link;
      $ch           = curl_init( $thumbnailurl );
      $fp           = fopen( $this->folderPath . '/' . $videoId . '.jpg', 'wb' );
      curl_setopt( $ch, CURLOPT_FILE, $fp );
      curl_setopt( $ch, CURLOPT_HEADER, false );
      curl_exec( $ch );
      curl_close( $ch );
      fclose( $fp );
    }

    return file_create_url( Constants::THUMBNAIL_FOLDER_PATH . $videoId . '.jpg' );
  }

  public function getData( $url ) {

    if ( strpos( $url, "?" ) !== false ) {
      $url = explode( "?", $url );
      $url = $url[0];
    }


    $vimeoAPIurl     = "https://vimeo.com/api/oembed.json?url=" . urlencode( $url ) . "&autoplay=1&controls=1&dnt=1";
    $request = curl_init( $vimeoAPIurl );

    curl_setopt( $request, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $request, CURLOPT_HEADER, false );
    $result = curl_exec( $request );
    curl_close( $request );
    $result = json_decode( $result );
    $result->provider = $result->provider_name;

    return $result;

  }
}
