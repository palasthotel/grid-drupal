<?php

namespace Drupal\grid\TwoClick\API;


use Drupal\grid\TwoClick\Constants\Constants;
use Drupal\grid\TwoClick\Constants\EmbedProperties;

class YouTubeAPI extends ProviderAPIBase implements ProviderAPIInterface
{
  private $extension = "webp";
  private $defaultYouTubeThumbnailDimensions = ['width' => 120, 'height' => 90];
  private $folderPathToImage = "";


  public function getThumbnail( string $url ) : string {
    $videoId = $this->getVideoID($url);
    $imageyturl = 'https://i.ytimg.com/vi_webp/' . $videoId . '/maxresdefault.webp';

    $this->folderPathToImage = $this->folderPath . '/' . $videoId . ".$this->extension";

    if (!$this->alreadyLoaded($videoId)) {
      $this->loadAndSaveImage($imageyturl);

      $imageSize = getimagesize(Constants::THUMBNAIL_FOLDER_PATH . $videoId . ".$this->extension");
      $imageWidth = $imageSize[0];
      $imageHeight = $imageSize[1];

      if ($imageWidth === $this->defaultYouTubeThumbnailDimensions['width']
        && $imageHeight === $this->defaultYouTubeThumbnailDimensions['height']) {
        //we have the default image, let's try to get a lower resolution thumbnail
        $imageyturl = 'https://i.ytimg.com/vi_webp/' . $videoId . '/0.webp';
        $this->loadAndSaveImage($imageyturl);

        $imageSize = getimagesize(Constants::THUMBNAIL_FOLDER_PATH . $videoId . ".$this->extension");
        $imageWidth = $imageSize[0];
        $imageHeight = $imageSize[1];

        if ($imageWidth === $this->defaultYouTubeThumbnailDimensions['width']
          && $imageHeight === $this->defaultYouTubeThumbnailDimensions['height']) {
          //there is no webp-version of a thumbnail, let's try jpg
          $this->getJpgThumbnail($videoId);
        }
      }
    }
    return \Drupal::service('file_url_generator')->generateString(Constants::THUMBNAIL_FOLDER_PATH . $videoId . ".$this->extension");
  }


  private function getInfos($url)
  {
    $originalUrl = $url;
    $dbTable = Constants::TWO_CLICK_DB_TABLE;

    $database = \Drupal::database();
    $cachedInfos = $database->select($dbTable)
      ->fields($dbTable)
      ->condition("$dbTable.original_url", $url)
      ->execute()
      ->fetch();

    if ($cachedInfos) {
      return json_decode(json_encode($cachedInfos), true); //return std class as array
    }

    $cleanUrl = $this->getCleanYoutubeUrl($url);
    if (!$cleanUrl) {
      return $url;
    }

    $parts = explode("v=", $cleanUrl);
    $videoId = end($parts);

    if (strlen($videoId) > 50) {
      //something went wrong, let's not explode our table
      $videoId = "";
    }

    $cachedInfos = [
      'original_url' => $originalUrl,
      'clean_url' => $cleanUrl,
      'video_id' => $videoId,
    ];
    $database->insert($dbTable)
      ->fields($cachedInfos)
      ->execute();
    return $cachedInfos;
  }

  public function getEmbedProperties(string $url) : EmbedProperties
  {

    $isEmebd = is_int(strpos($url, '/embed/'));

    if ($isEmebd) {
      $embeddedUrl = explode('/embed/', $url);
      $url = 'https://www.youtube.com/watch?v=' . array_pop($embeddedUrl);
    }

    $parsedUrl = parse_url($url);

    $queryArgs = [];
    if (isset($parsedUrl['query'])) {
      parse_str($parsedUrl['query'], $queryArgs);
    }

    $timestamp = "0";
    if (isset($queryArgs['t'])) {
      $timestamp = $queryArgs['t'];
    }

    $ytAPIurl = "https://www.youtube-nocookie.com/oembed?url=" . urlencode($url) . "&format=json&start=$timestamp";
    $request = curl_init($ytAPIurl);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($request, CURLOPT_HEADER, false);
    $result = curl_exec($request);
    if ($result === false) {
      var_dump(curl_error($request));
      die();
    }
    curl_close($request);
    $result = json_decode($result);

    if (is_null($result)) return $this->defaultInfos($url);
    $html = $result->html;

    $html = str_replace('src="http://', 'src="https://', $html);
    // Prevents flash bug in Firefox (no playback on click)
    $html = str_replace('feature=oembed', 'feature=oembed&wmode=transparent&html5=1&autoplay=1', $html);
    $result->html = str_replace("youtube.com", "youtube-nocookie.com", $html);
    $this->embedCode = $result->html;

    $properties = [
      'title'          => t($result->title),
      'author'         => $result->author_name,
      'url'            => $url,
      'urlDescription' => t("Watch on @provider", ['@provider' => 'YouTube']),
      'embed'          => $this->embedCode,
      'thumbnail'      => $this->getThumbnail( $url ),
      'provider'       => 'YouTube'
    ];

    $embedProperties = new EmbedProperties();

    foreach ($properties as $propertyName => $propertyValue){
      $embedProperties->set($propertyName, $propertyValue);
    }

    return $embedProperties;
  }

  private function getCleanYoutubeUrl(string $url)
  {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $youTubeUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);;
    curl_close($ch);

    $parts = explode("v=", $youTubeUrl);
    $parts = explode("&", $parts[1]);
    $videoId = reset($parts);

    $cleanUrl = "https://www.youtube.com/watch?v=$videoId";

    //if we get temporarily blocked by google, we failed
    if (is_int(strpos($cleanUrl, "https://www.google.com/sorry"))) {
      return false;
    }

    if ($videoId === "") {
      $videoId = "default";
      $cleanUrl = "+++NoCleanUrlFound+++?v=$videoId"; //mark this in database, but also return videoId so we can at least work with that
    }

    return $cleanUrl;
  }

  private function getVideoID($url)
  {
    $infos = $this->getInfos($url);
    return $infos['video_id'];
  }

  private function defaultInfos($url)
  {
    $videoProperties = [
      'title' => t('YouTube Video'),
      'author' => '',
      'url' => $url,
      'urlDescription' => t("Watch on @provider", ['@provider' => 'YouTube']),
      'embed' => $this->embedCode,
      'thumbnail' => 'default.jpg',
      'provider'       => 'YouTube'
    ];

    $embedProperties = new EmbedProperties();

    foreach ($videoProperties as $propertyName => $propertyValue){
      $embedProperties->set($propertyName, $propertyValue);
    }

    return $embedProperties;
  }

  private function loadAndSaveImage($imageyturl)
  {
    $ch = curl_init($imageyturl);

    $fp = fopen($this->folderPathToImage, 'wb');
    if (!$fp) {
      $this->folderPathToImage = $this->folderPath . "/default.$this->extension";
      $videoId = "default";
      $fp = fopen($this->folderPathToImage, 'wb');
    }
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);

    fclose($fp);
  }

  private function getJpgThumbnail($videoId)
  {
    // cleanup: delete default webp-image, change extension and folderpath
    \Drupal::service('file_system')->delete(Constants::THUMBNAIL_FOLDER_PATH . $videoId . ".$this->extension");
    $this->extension = "jpg";
    $this->folderPathToImage = $this->folderPath . '/' . $videoId . ".$this->extension";

    $imageyturl = 'https://i.ytimg.com/vi/' . $videoId . '/maxresdefault.jpg';
    $this->loadAndSaveImage($imageyturl);

    $imageSize = getimagesize(Constants::THUMBNAIL_FOLDER_PATH . $videoId . ".$this->extension");
    $imageWidth = $imageSize[0];
    $imageHeight = $imageSize[1];

    if ($imageWidth === $this->defaultYouTubeThumbnailDimensions['width']
      && $imageHeight === $this->defaultYouTubeThumbnailDimensions['height']) {
      //we have the default image, let's try to get a lower resolution thumbnail
      $imageyturl = 'https://i.ytimg.com/vi/' . $videoId . '/0.jpg';
      $this->loadAndSaveImage($imageyturl);
    }
  }
}
