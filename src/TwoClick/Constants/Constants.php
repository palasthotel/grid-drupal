<?php

namespace Drupal\grid\TwoClick\Constants;


class Constants {
  const PROVIDER_YOUTUBE = "YouTube";
  const PROVIDER_VIMEO = "Vimeo";
  const PROVIDER_PODIGEE = "Podigee";
  const PROVIDER_DEFAULT = "default";
  const THUMBNAIL_FOLDER_PATH = "public://TwoclickThumbnails/";
  const TWO_CLICK_SETTINGS = "grid.two_click.settings";
  const TWO_CLICK_SETTINGS_ENABLE = "two_click_enable";
  const TWO_CLICK_SETTINGS_VIMEO_KEY = "two_click_vimeo_api_key";
  const TWO_CLICK_SETTINGS_DISCLAIMER_TEXT = "two_click_disclaimer_text";
  const TWO_CLICK_SETTINGS_PRIVACY_LINK = "two_click_disclaimer_link";
  const TWO_CLICK_DB_TABLE = "grid_twoclick_urls";
  const TWO_CLICK_DB_TABLE_SCHEMA = [
    'fields'      => [
      'id'           => [
        'type'     => 'serial',
        'not null' => true,
      ],
      'original_url' => [
        'type'     => 'text',
        'not null' => true,
      ],
      'clean_url'    => [
        'type'     => 'text',
        'not null' => true,
      ],
      'video_id'     => [
        'type'     => 'varchar',
        'not null' => true,
        'length'   => 50,
      ],
    ],
    'primary key' => [ 'id' ],
  ];
}
