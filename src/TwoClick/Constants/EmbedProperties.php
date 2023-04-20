<?php

namespace Drupal\grid\TwoClick\Constants;

use Drupal\Core\StringTranslation\TranslatableMarkup;

class EmbedProperties
{

  public TranslatableMarkup $title;
  public string $author;
  public string $url;
  public TranslatableMarkup $urlDescription;
  public string $embed;
  public string $thumbnail;
  public string $provider;

  public function set(string $propertyName, $value){
    if (property_exists($this, $propertyName)) $this->$propertyName = $value;
  }

}
