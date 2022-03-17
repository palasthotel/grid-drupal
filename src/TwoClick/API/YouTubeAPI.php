<?php

namespace Drupal\grid\TwoClick\API;


use Drupal\grid\TwoClick\Constants\Constants;

class YouTubeAPI extends ProviderAPIBase implements ProviderAPIInterface {


  public function getThumbnail( $url ) {

    $url        = $this->getCleanYoutubeUrl( $url );
    $parts      = explode( "v=", $url );
    $videoId    = end( $parts );
    $imageyturl = 'https://i.ytimg.com/vi/' . $videoId . '/sddefault.jpg';


    if ( ! $this->alreadyLoaded( $url, $videoId ) ) {
      $ch = curl_init( $imageyturl );
      $fp = fopen( $this->folderPath . '/' . $videoId . '.jpg', 'wb' );
      curl_setopt( $ch, CURLOPT_FILE, $fp );
      curl_setopt( $ch, CURLOPT_HEADER, 0 );
      curl_exec( $ch );
      curl_close( $ch );
      fclose( $fp );
    }

    return file_create_url( Constants::THUMBNAIL_FOLDER_PATH . $videoId . '.jpg' );
  }


  private function getCleanYoutubeUrl( $url ) {


    $parsedUrl = parse_url($url);

    if($parsedUrl['host'] === "youtu.be"){
      $url = "${parsedUrl['scheme']}://${parsedUrl['host']}${parsedUrl['path']}";
    }

    // let's get us a consistent and predictable youtube-url
    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_HEADER, true );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_exec( $ch );
    $youTubeUrl = curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL );
    $youTubeUrl = explode( '&', $youTubeUrl );
    $cleanUrl   = $youTubeUrl[0];
    curl_close( $ch );


    return $cleanUrl;
  }

  public function getData( $url ) {

    $parsedUrl = parse_url($url);

    $queryArgs = [];
    parse_str($parsedUrl['query'], $queryArgs);

    $timestamp = "0";
    if (isset($queryArgs['t'])){
      $timestamp = $queryArgs['t'];
    }

    $ytAPIurl     = "https://www.youtube-nocookie.com/oembed?url=" . urlencode( $url ) . "&format=json&start=$timestamp";
    $request = curl_init( $ytAPIurl );
    curl_setopt( $request, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $request, CURLOPT_HEADER, false );
    $result = curl_exec( $request );
    if ( $result === false ) {
      var_dump( curl_error( $request ) );
      die();
    }
    curl_close( $request );
    $result = json_decode( $result );
    $html   = $result->html;

    $html   = str_replace( 'src="http://', 'src="https://', $html );
    // Prevents flash bug in Firefox (no playback on click)
    $html = str_replace( 'feature=oembed', 'feature=oembed&wmode=transparent&html5=1&autoplay=1', $html );
    $result->html = str_replace( "youtube.com", "youtube-nocookie.com", $html );

    return $result;
  }
}
