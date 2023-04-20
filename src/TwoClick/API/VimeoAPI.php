<?php

namespace Drupal\grid\TwoClick\API;

use Drupal\grid\TwoClick\Constants\Constants;
use Drupal\grid\TwoClick\Constants\EmbedProperties;

class VimeoAPI extends ProviderAPIBase implements ProviderAPIInterface {


	public function getThumbnail( string $url ) : string {

		$explodedUrl = explode( '/', $url );
		$videoId     = end( $explodedUrl );

		if ( ! $this->alreadyLoaded( $videoId ) ) {

			$config      = \Drupal::config( Constants::TWO_CLICK_SETTINGS );
			$vimeoAPIKey = $config->get( Constants::TWO_CLICK_SETTINGS_VIMEO_KEY );


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


      if ($thumbnailresult) {
        $thumbnailresult = json_decode( $thumbnailresult );


        if ( ! isset( $thumbnailresult->error ) ) {


          $thumbnailurl = $thumbnailresult->data[0]->sizes[0]->link;
          $ch           = curl_init( $thumbnailurl );
          $fp           = fopen( $this->folderPath . '/' . $videoId . '.jpg', 'wb' );
          curl_setopt( $ch, CURLOPT_FILE, $fp );
          curl_setopt( $ch, CURLOPT_HEADER, false );
          curl_exec( $ch );
          curl_close( $ch );
          fclose( $fp );
        }
      }
		}

    return \Drupal::service('file_url_generator')->generateString(Constants::THUMBNAIL_FOLDER_PATH . $videoId . '.jpg');

  }

	public function getEmbedProperties( string $url ) : EmbedProperties {

		if ( strpos( $url, "?" ) !== false ) {
			$url = explode( "?", $url );
			$url = $url[0];
		}


		$vimeoAPIurl = "https://vimeo.com/api/oembed.json?url=" . urlencode( $url ) . "&autoplay=1&controls=1&dnt=1";
		$request     = curl_init( $vimeoAPIurl );

		curl_setopt( $request, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $request, CURLOPT_HEADER, false );
		$result = curl_exec( $request );
		curl_close( $request );
		$result           = json_decode( $result );

    if (is_null($result)) return $this->defaultInfos($url);

    $this->embedCode = $result->html;

    $properties = [
      'title'          => t($result->title),
      'author'         => $result->author_name,
      'url'            => $url,
      'urlDescription' => t("Watch on @provider", ['@provider' => 'Vimeo']),
      'embed'          => $this->embedCode,
      'thumbnail'      => $this->getThumbnail( $url ),
      'provider'       => 'Vimeo'
    ];

    $embedProperties = new EmbedProperties();

    foreach ($properties as $propertyName => $propertyValue){
      $embedProperties->set($propertyName, $propertyValue);
    }

    return $embedProperties;

	}

  private function defaultInfos($url) {


    $videoProperties = [
      'title'          => t('Vimeo Video'),
      'author'         => '',
      'url'            => $url,
      'urlDescription' => t("Watch on @provider", ['@provider' => 'Vimeo']),
      'embed'          => '',
      'thumbnail' => 'default.jpg',
      'provider'       => 'Vimeo'
    ];

    $embedProperties = new EmbedProperties();

    foreach ($videoProperties as $propertyName => $propertyValue){
      $embedProperties->set($propertyName, $propertyValue);
    }

    return $embedProperties;

  }
}
